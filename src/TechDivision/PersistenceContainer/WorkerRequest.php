<?php

/**
 * TechDivision\PersistenceContainer\Request
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\Socket\Client;
use TechDivision\PersistenceContainerClient\Interfaces\RemoteMethod;

/**
 * The stackable implementation that handles the request.
 * 
 * @package     TechDivision\PersistenceContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class WorkerRequest extends \Stackable {
    
    /**
     * The client socket resource.
     * @var string
     */
    public $resource;
    
    /**
     * Initializes the request with the client socket.
     * 
     * @param resource $resource The client socket instance
     * @return void
     */
    public function __construct($resource) {
        $this->resource = $resource;
    }
    
    /**
     * @see \Stackable::run()
     */
    public function run() {

        // check if a worker is available
        if ($this->worker) {

            // initialize a new client socket
            $client = new Client();

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
                    $application = $this->worker->findApplication($className);

                    // create initial context and lookup session bean
                    $instance = $application->lookup($className, $sessionId);

                    // prepare method name and parameters and invoke method
                    $methodName = $remoteMethod->getMethodName();
                    $parameters = $remoteMethod->getParameters();

                    // invoke the remote method call on the local instance
                    $response = call_user_func_array(array($instance, $methodName), $parameters);

                } catch (\Exception $e) {
                    $response = new \Exception($e);
                }

                try {
                    
                    // send the data back to the client
                    $client->sendLine(serialize($response));

                    // close the socket immediately
                    $client->close();

                } catch (\Exception $e) {

                    // log the stack trace
                    error_log($e->__toString());

                    // close the socket immediately
                    $client->close();
                }

            } else {
                error_log('Invalid remote method call');
            }
        }
    }
}