<?php

/**
 * TechDivision\PersistenceContainer\PersistenceContainer
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
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use Herrera\Annotations\Tokens;
use Herrera\Annotations\Tokenizer;
use Herrera\Annotations\Convert\ToArray;
use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\ApplicationServer\ServerNodeConfiguration;

/**
 * Class Container
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class PersistenceContainer extends \Stackable implements ContainerInterface
{
    
    /**
     * Initializes the container with the initial context, the unique container ID
     * and the deployed applications.
     *
     * @param \TechDivision\ApplicationServer\InitialContext                         $initialContext The initial context
     * @param \TechDivision\ApplicationServer\Api\Node\ContainerNode                 $containerNode  The container's UUID
     * @param array<\TechDivision\ApplicationServer\Interfaces\ApplicationInterface> $applications   The application instance
     *
     * @return void
     */
    public function __construct($initialContext, $containerNode, $applications)
    {
        $this->initialContext = $initialContext;
        $this->containerNode = $containerNode;
        $this->applications = $applications;
    }

    /**
     * Returns the receiver instance ready to be started.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\ReceiverInterface The receiver instance
     */
    public function getReceiver()
    {
        // nothing
    }

    /**
     * Returns an array with the deployed applications.
     *
     * @return array The array with applications
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Return's the containers config node
     *
     * @return \TechDivision\ApplicationServer\Api\Node\ContainerNode
     */
    public function getContainerNode()
    {
        return $this->containerNode;
    }

    /**
     * Return's the initial context instance
     *
     * @return \TechDivision\ApplicationServer\InitialContext
     */
    public function getInitialContext()
    {
        return $this->initialContext;
    }

    /**
     * Run the containers logic
     *
     * @return void
     */
    public function run()
    {
        // define webservers base dir
        // todo: refactor this in webserver repository
        define(
            'WEBSERVER_BASEDIR',
            $this->getInitialContext()->getSystemConfiguration()->getBaseDirectory()->getNodeValue()->__toString()
            . DIRECTORY_SEPARATOR
        );
        define(
            'WEBSERVER_AUTOLOADER',
            $autoloader = WEBSERVER_BASEDIR .
            'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'
        );

        // setup configurations
        $serverConfigurations = array();
        foreach ($this->getContainerNode()->getServers() as $serverNode) {
            $serverConfigurations[] = new ServerNodeConfiguration($serverNode);
        }

        // init server array
        $servers = array();

        // start servers by given configurations
        foreach ($serverConfigurations as $serverConfig) {

            // get type definitions
            $serverType = $serverConfig->getType();
            $serverContextType = $serverConfig->getServerContextType();

            // create a new instance server context
            $serverContext = new $serverContextType();

            // inject container to be available in specific mods etc. and initialize the module
            $serverContext->injectContainer($this);
            $serverContext->init($serverConfig);

            $serverContext->injectLoggers($this->getInitialContext()->getLoggers());

            // init and start server
            $servers[] = new $serverType($serverContext);
        }

        // wait for servers
        foreach ($servers as $server) {
            $server->join();
        }
    }
    
    /**
     * Attaches the passed bean, depending on it's type to the container.
     * 
     * @param object $instance  The bean instance to attach
     * @param string $sessionId The session-ID when we have stateful session bean
     * 
     * @return void
     * @throws \Exception Is thrown if we have a stateful session bean, but no session-ID passed
     */
    public function attach($instance, $sessionId = null)
    {

        try {

            // we need a reflection object to read the annotations
            $reflectionObject = new \ReflectionObject($instance);
            
            // check what kind of bean we have
            switch ($this->getBeanAnnotation($reflectionObject)) {
            
                case 'stateful': // @Stateful
            
                    // lock the container
                    $this->lock();
            
                    // check if we've a session-ID available
                    if ($sessionId == null) {
                        throw new \Exception("Can't find a session-ID to attach stateful session bean");
                    }
            
                    // load the session's from the initial context
                    $session = $this->getAttribute($sessionId);
            
                    // if an instance exists, load and return it
                    if (is_array($session) === false) {
                        $session = array();
                    }
            
                    // store the bean back to the container
                    $session[$reflectionObject->getName()] = $instance;
                    $this->setAttribute($sessionId, $session);
            
                    // unlock the container
                    $this->unlock();
            
                    break;
            
                case 'singleton': // @Singleton
            
                    // lock the container
                    $this->lock();
            
                    // replace any existing bean in the container
                    $this->setAttribute($reflectionObject->getName(), $instance);
            
                    // unlock the container
                    $this->unlock();
            
                    break;
            
                default: // @Stateless
            
                    // we do nothing here, because we have not state
            }
                        
        } catch (\Exception $e) {
            $this->unlock();
            throw $e;
        }
    }

    /**
     * Run's a lookup for the session bean with the passed class name and
     * session ID.
     * If the passed class name is a session bean an instance
     * will be returned.
     *
     * @param string $className The name of the session bean's class
     * @param string $sessionId The session ID
     * @param array  $args      The arguments passed to the session beans constructor
     *
     * @return object The requested session bean
     * @throws \Exception Is thrown if passed class name is no session bean or is a entity bean (not implmented yet)
     */
    public function lookup($className, $sessionId, array $args = array())
    {
        
        // get the reflection class for the passed class name
        $reflectionClass = $this->newReflectionClass($className);

        switch ($this->getBeanAnnotation($reflectionClass)) {

            case 'stateful':

                // load the session's from the initial context
                $session = $this->getAttribute($sessionId);

                // if an instance exists, load and return it
                if (is_array($session)) {
                    if (array_key_exists($className, $session)) {
                        return $session[$className];
                    }
                }

                // if not, initialize a new instance, add it to the container and return it
                return $this->newInstance($className, $args);

            case 'singleton':

                // check if an instance is available
                if ($this->getAttribute($className)) {
                    return $this->getAttribute($className);
                }

                // if not create a new instance and return it
                return $this->newInstance($className, $args);

            default: // @Stateless

                return $this->newInstance($className, $args);
        }

        // if the class is no session bean, throw an exception
        throw new \Exception(sprintf("Can\'t find session bean with class name '%s'", $className));
    }

    /**
     * Returns the bean annotation for the passed reflection class, that can be
     * one of Entity, Stateful, Stateless, Singleton.
     *
     * @param \ReflectionClass $reflectionClass The class to return the annotation for
     *
     * @throws \Exception Is thrown if the class has NO bean annotation
     * @return string The found bean annotation
     */
    public function getBeanAnnotation(\ReflectionClass $reflectionClass)
    {

        // load the class name to get the annotation for
        $className = $reflectionClass->getName();

        // check if an array with the bean types has already been registered
        $beanTypes = $this->getAttribute('beanTypes');
        if (is_array($beanTypes)) {
            if (array_key_exists($className, $beanTypes)) {
                return $beanTypes[$className];
            }
        } else {
            $beanTypes = array();
        }

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('author', 'package', 'license', 'copyright'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionClass->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // defines the available bean annotations
        $beanAnnotations = array('singleton', 'stateful', 'stateless');
        
        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {
            $tokeName = strtolower($token->name);
            if (in_array($tokeName, $beanAnnotations)) {
                $beanTypes[$className] = $tokeName;
                $this->setAttribute('beanTypes', $beanTypes);
                return $tokeName;
            }
        }

        // throw an exception if the requested class
        throw new \Exception(sprintf("Missing enterprise bean annotation for %s", $reflectionClass->getName()));
    }
    
    /**
     * Returns a new instance of the passed class name.
     *
     * @param string $className The fully qualified class name to return the instance for
     * @param array  $args      Arguments to pass to the constructor of the instance
     *
     * @return object The instance itself
     * @todo Has to be refactored to avoid registering autoloader on every call
     */
    public function newInstance($className, array $args = array())
    {
        return $this->getInitialContext()->newInstance($className, $args);
    }    
    
    /**
     * Returns a reflection class intance for the passed class name.
     *
     * @param string $className The class name to return the reflection instance for
     *
     * @return \ReflectionClass The reflection instance
     */
    public function newReflectionClass($className)
    {
        return $this->getInitialContext()->newReflectionClass($className);
    }
    
    /**
     * Registers the value with the passed key in the container.
     * 
     * @param string $key   The key to register the value with
     * @param object $value The value to register
     * 
     * @return void
     */
    protected function setAttribute($key, $value)
    {
        $this[$key] = $value;
    }
    
    /**
     * Returns the attribute with the passed key from the container.
     * 
     * @param string $key The key the requested value is registered with
     * 
     * @return object The requested value
     */
    protected function getAttribute($key)
    {
        return $this[$key];
    }
}
