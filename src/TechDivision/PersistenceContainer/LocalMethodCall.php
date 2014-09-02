<?php

/**
 * TechDivision\PersistenceContainer\LocalMethodCall
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
 * @package   TechDivision_PersistenceContainerProtocol
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\PersistenceContainerProtocol\RemoteMethod;

/**
 * Abstract base class of the Maps.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainerProtocol
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class LocalMethodCall implements RemoteMethod, \Serializable
{

    /**
     * The name of the webapp this call originates from
     *
     * @var string
     */
    protected $appName;

    /**
     * The class name to invoke the method on.
     *
     * @var string
     */
    protected $className = null;

    /**
     * The method name to invoke on the class.
     *
     * @var string
     */
    protected $methodName = null;

    /**
     * Parameters for the method.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * The session ID to use for the method call.
     *
     * @var string
     */
    protected $sessionId = null;

    /**
     * The client's socket server IP address to send the response to.
     *
     * @var string
     */
    protected $address = '127.0.0.1';

    /**
     * The client's socket server port to send the response to.
     *
     * @var integer
     */
    protected $port = 0;

    /**
     * Initialize the instance with the necessary params.
     *
     * @param string $className  The class name to invoke the method on
     * @param string $methodName The method name to invoke
     * @param string $sessionId  The session ID to use for the method call
     *
     * @return void
     */
    public function __construct($className, $methodName, $sessionId = null)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->sessionId = $sessionId;
    }

    /**
     * Adds passed parameter to the array with the parameters.
     *
     * @param integer $key   The parameter name
     * @param mixed   $value The parameter value
     *
     * @return void
     */
    public function addParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Returns the parameter with the passed key.
     *
     * @param string $key The name of the parameter to return
     *
     * @return mixed The parameter's value
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getParameter()
     */
    public function getParameter($key)
    {
        return $this->parameters[$key];
    }

    /**
     * Returns the parameters for the method.
     *
     * @return array The method's parameters
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getParameters()
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the class name to invoke the method on.
     *
     * @return string The class name
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getClassName()
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns the method name to invoke on the class.
     *
     * @return string The method name
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getMethodName()
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the session ID to use for the method call.
     *
     * @return string The session ID
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getSessionId()
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Sets the client's socket server IP address.
     *
     * @param string $address The client's socket server IP address
     *
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Returns the client's server socket IP address.
     *
     * @return string The client's server socket IP address
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getAddress()
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the webapp name the call comes from
     *
     * @param string $appName Name of the webapp using this client connection
     *
     * @return void
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * Returns the name of the webapp this call comes from
     *
     * @return string The webapp name
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * Sets the client's socket server port.
     *
     * @param integer $port The client's socket server port
     *
     * @return void
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Returns the client's server socket port.
     *
     * @return string The client's server socket port
     * @see \TechDivision\PersistenceContainerProtocol\RemoteMethod::getPort()
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the session ID to use for the method call.
     *
     * @return string The session ID
     *
     * @return void
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Sets the parameters for the method.
     *
     * @param array The method parameters
     *
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * (non-PHPdoc)
     * @see Serializable::serialize()
     */
    public function serialize()
    {

        // create a serialized version of the instance
        $serialized = serialize(
            array(
                'methodName' => $this->methodName,
                'className' => $this->className
            )
        );

        // return the serialized instance
        return $data;
    }

    /**
     * (non-PHPdoc)
     * @see Serializable::unserialize()
     */
    public function unserialize($data)
    {

        // unserialize the passed data
        $unserialized = unserialize($data);

        // set the values
        $this->className = $unserialized['className'];
        $this->methodName = $unserialized['methodName'];
    }
}
