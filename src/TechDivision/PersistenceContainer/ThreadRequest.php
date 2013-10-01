<?php

/**
 * TechDivision\PersistenceContainer\ThreadRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\AbstractContextThread;
use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\PersistenceContainerClient\Interfaces\RemoteMethod;
use TechDivision\Socket\Client;

/**
 * The thread implementation that handles the request.
 *
 * @package TechDivision\PersistenceContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class ThreadRequest extends AbstractContextThread
{

    /**
     * Holds the container instance
     *
     * @var ContainerInterface
     */
    public $container;

    /**
     * Holds the main socket resource
     *
     * @var resource
     */
    public $resource;

    /**
     * Initializes the request with the client socket.
     *
     * @param ContainerInterface $container
     *            The ServletContainer
     * @param resource $resource
     *            The client socket instance
     * @return void
     */
    public function init(ContainerInterface $container, $resource)
    {
        $this->container = $container;
        $this->resource = $resource;
    }

    /**
     *
     * @see AbstractThread::main()
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

                // load the referenced application from the server
                $application = $this->findApplication($className);

                // create initial context and lookup session bean
                $instance = $application->lookup($className, $sessionId);

                // prepare method name and parameters and invoke method
                $methodName = $remoteMethod->getMethodName();
                $parameters = $remoteMethod->getParameters();

                // invoke the remote method call on the local instance
                $response = call_user_func_array(array(
                    $instance,
                    $methodName
                ), $parameters);

            } catch (\Exception $e) {
                $response = new \Exception($e);
            }

            try {

                // send the data back to the client
                $client->sendLine(serialize($response));
            } catch (\Exception $e) {

                // log the stack trace
                error_log($e->__toString());
            }
        } else {
            error_log('Invalid remote method call');
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
     * @return \TechDivision\ServletContainer\Container The container instance
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
     * Tries to find and return the application for the passed class name.
     *
     * @param string $className
     *            The name of the class to find and return the application instance
     * @return \TechDivision\PersistenceContainer\Application The application instance
     * @throws \Exception Is thrown if no application can be found for the passed class name
     */
    public function findApplication($className)
    {

        // iterate over all classes and check if the application name contains the class name
        foreach ($this->getApplications() as $name => $application) {

            if (strpos(strtolower($className), $name) !== false) {
                // if yes, return the application instance
                return $application;
            }
        }

        // if not throw an exception
        throw new \Exception("Can\'t find application for '$className'");
    }
}