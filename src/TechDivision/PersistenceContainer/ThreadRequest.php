<?php
/**
 * TechDivision\PersistenceContainer\ThreadRequest
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\AbstractContextThread;
use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\PersistenceContainerClient\Interfaces\RemoteMethod;
use TechDivision\ApplicationServer\Api\AppService;
use TechDivision\Socket\Client;

/**
 * The thread implementation that handles the request.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class ThreadRequest extends AbstractContextThread
{

    /**
     * Holds the container instance.
     *
     * @var \TechDivision\PersistenceContainer\Container
     */
    public $container;

    /**
     * Holds the main socket resource.
     *
     * @var resource
     */
    public $resource;

    /**
     * Initializes the request with the client socket.
     *
     * @param \TechDivision\PersistenceContainer\Container $container The ServletContainer
     * @param resource                                     $resource  The client socket instance
     *
     * @return void
     */
    public function init(ContainerInterface $container, $resource)
    {
        $this->container = $container;
        $this->resource = $resource;
    }

    /**
     * The thread implementation main method which will be called from run in abstractness
     *
     * @return void
     */
    public function main()
    {
        // initialize a new client socket
        $client = $this->newInstance('TechDivision\Socket\Client');

        // set the client socket resource
        $client->setResource($this->resource);

        // read a line from the client
        $line = $client->readLine();

        // unserialize the passed remote method
        $remoteMethod = unserialize($line);

        // check if a remote method has been passed
        if ($remoteMethod instanceof RemoteMethod) {

            try {

                // load class name and session ID from remote method
                $className = $remoteMethod->getClassName();
                $sessionId = $remoteMethod->getSessionId();

                // Find the application for the given name coming from remote
                $application = $this->findApplication($remoteMethod->getAppName());

                // create initial context and lookup session bean
                $instance = $application->lookup($className, $sessionId);

                // prepare method name and parameters and invoke method
                $methodName = $remoteMethod->getMethodName();
                $parameters = $remoteMethod->getParameters();

                // invoke the remote method call on the local instance
                $response = call_user_func_array(
                    array(
                        $instance,
                        $methodName
                    ),
                    $parameters
                );

            } catch (\Exception $e) {
                $response = $e;
            }

            try {

                // send the data back to the client
                $client->sendLine(serialize($response));

            } catch (\Exception $e) {
                $this->getInitialContext()
                    ->getSystemLogger()
                    ->error($e->__toString());
            }

        } else {
            $this->getInitalContext()
                ->getSystemLogger()
                ->critical('Invalid remote method call');
        }

        // try to shutdown client socket
        try {

            $client->shutdown();
            $client->close();
        } catch (\Exception $e) {

            $client->close();
        }

        unset($client);
    }

    /**
     * Returns the container instance.
     *
     * @return \TechDivision\PersistenceContainer\Container The container instance
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the array with the available applications.
     *
     * @return array The available applications
     */
    public function getApplications()
    {
        return $this->getContainer()->getApplications();
    }

    /**
     * Tries to find and return the application for the passed application name.
     *
     * @param string $appName The name of the application to find and return the application instance
     *
     * @return \TechDivision\PersistenceContainer\Application The application instance
     * @throws \Exception Is thrown if no application can be found for the passed class name
     */
    public function findApplication($appName)
    {
        // iterate over all applications and check if the application name contains the app name
        $foundApplication = null;
        foreach ($this->getApplications() as $name => $application) {

            // Do we have an application like this?
            if ($name === $appName) {

                return $application;
            }
        }

        // if not throw an exception
        throw new \Exception("Can\'t find application for '$appName'");
    }
}
