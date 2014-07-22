<?php

/**
 * TechDivision\PersistenceContainer\PersistenceContainerValve
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use \TechDivision\ServletEngine\Valve;
use \TechDivision\Servlet\Http\HttpServletRequest;
use \TechDivision\Servlet\Http\HttpServletResponse;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\PersistenceContainerProtocol\RemoteMethodProtocol;

/**
 * Valve implementation that will be executed by the servlet engine to handle
 * an incoming HTTP servlet request.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class PersistenceContainerValve implements Valve
{

    /**
     * Processes the request by invoking the request handler that executes the servlet
     * in a protected context.
     *
     * @param \TechDivision\Servlet\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\Servlet\ServletResponse $servletResponse The response instance
     *
     * @return void
     */
    public function invoke(HttpServletRequest $servletRequest, HttpServletResponse $servletResponse)
    {

        try {

            // unpack the remote method call
            $remoteMethod = RemoteMethodProtocol::unpack($servletRequest->getBodyContent());

            // load the application context
            $application = $servletRequest->getContext();
            $beanManager = $application->getManager(BeanContext::IDENTIFIER);

            // lock the container and lookup the bean instance
            $instance = $beanManager->locate($remoteMethod, array($application));

            // prepare method name and parameters and invoke method
            $methodName = $remoteMethod->getMethodName();
            $parameters = $remoteMethod->getParameters();
            $sessionId = $remoteMethod->getSessionId();

            // invoke the remote method call on the local instance
            $response = call_user_func_array(array($instance, $methodName), $parameters);

            // serialize the remote method and write it to the socket
            $servletResponse->appendBodyStream(RemoteMethodProtocol::pack($response));

            // reattach the bean instance in the container and unlock it
            $beanManager->attach($instance, $sessionId);

        } catch (\Exception $e) {
            // catch the exception and append it to the body stream
            error_log($e->__toString());
            $servletResponse->setStatusCode(500);
            $servletResponse->appendBodyStream(RemoteMethodProtocol::pack($e));

        } finally {

            // dispatch this request, because we have finished processing it
            $servletRequest->setDispatched(true);
        }
    }
}
