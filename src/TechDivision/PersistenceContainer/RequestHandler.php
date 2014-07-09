<?php

/**
 * TechDivision\ServletEngine\RequestHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainerProtocol
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\Context\Context;
use TechDivision\Http\HttpResponseStates;
use TechDivision\PersistenceContainerProtocol\RemoteMethod;
use TechDivision\PersistenceContainerProtocol\RemoteMethodProtocol;
use TechDivision\ApplicationServer\Interfaces\ApplicationInterface;

/**
 * This is a request handler that is necessary to process each request of an
 * application in a separate context.
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainerProtocol
 * @link      http://www.appserver.io
 */
class RequestHandler extends \Thread implements Context
{

    /**
     * The application instance we're processing requests for.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * Initializes the request handler with the application.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\ApplicationInterface The application instance
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    public function injectRequest($servletRequest)
    {
        $this->servletRequest = $servletRequest;
    }

    public function injectResponse($servletResponse)
    {
        $this->servletResponse = $servletResponse;
    }

    /**
     * Returns the value with the passed name from the context.
     *
     * @param string $key The key of the value to return from the context.
     *
     * @return mixed The requested attribute
     */
    public function getAttribute($key)
    {
        // do nothing here, it's only to implement the Context interface
    }

    /**
     * Returns the application instance.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\ApplicationInterface The application instance
     */
    protected function getApplication()
    {
        return $this->application;
    }

    /**
     * The main method that handles the thread in a separate context.
     *
     * @return void
     */
    public function run()
    {

        try {

            // create a local instance of appication
            $application = $this->application;

            // register the class loader again, because each thread has its own context
            $application->registerClassLoaders();

            // synchronize the servlet request/response
            $servletRequest = $this->servletRequest;
            $servletResponse = $this->servletResponse;

            // register the class loader again, because each thread has its own context
            $application->registerClassLoaders();

            // unpack the remote method call
            $remoteMethod = RemoteMethodProtocol::unpack($servletRequest->getBodyContent());

            // lock the container and lookup the bean instance
            $instance = $application->getBeanManager()->locate($remoteMethod, array($application));

            // prepare method name and parameters and invoke method
            $methodName = $remoteMethod->getMethodName();
            $parameters = $remoteMethod->getParameters();

            // invoke the remote method call on the local instance
            $response = call_user_func_array(array($instance, $methodName), $parameters);

            // serialize the remote method and write it to the socket
            $servletResponse->appendBodyStream($packed = RemoteMethodProtocol::pack($response));

            // reattach the bean instance in the container and unlock it
            $application->getBeanManager()->attach($instance, $sessionId);

        } catch (\Exception $e) {

            $servletResponse->appendBodyStream($e->__toString());
            $servletResponse->setStatusCode(500);
        }
    }
}
