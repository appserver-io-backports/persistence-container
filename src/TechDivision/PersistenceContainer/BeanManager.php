<?php

/**
 * TechDivision\PersistenceContainer\BeanManager
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
 * @author    Bernhard Wick <b.wick@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use Herrera\Annotations\Tokens;
use Herrera\Annotations\Tokenizer;
use Herrera\Annotations\Convert\ToArray;
use TechDivision\ApplicationServer\Application;
use TechDivision\Storage\GenericStackable;
use TechDivision\Storage\StackableStorage;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\PersistenceContainerProtocol\RemoteMethod;
use TechDivision\Application\Interfaces\ApplicationInterface;

/**
 * The bean manager handles the message and session beans registered for the application.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @author    Bernhard Wick <b.wick@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class BeanManager extends GenericStackable implements BeanContext
{

    /**
     * Initializes the bean manager.
     *
     * @return void
     */
    public function __construct()
    {

        // initialize the member variables
        $this->webappPath = '';
        $this->resourceLocator = null;

        // initialize the stackable for the beans
        $this->beans = new StackableStorage();

    }

    /**
     * Injects the absolute path to the web application.
     *
     * @param string $webappPath The absolute path to this web application
     *
     * @return void
     */
    public function injectWebappPath($webappPath)
    {
        $this->webappPath = $webappPath;
    }

    /**
     * Injects the resource locator that locates the requested queue.
     *
     * @param \TechDivision\MessageQueue\ResourceLocator $resourceLocator The resource locator
     *
     * @return void
     */
    public function injectResourceLocator(ResourceLocator $resourceLocator)
    {
        $this->resourceLocator = $resourceLocator;
    }

    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     *
     * @param \TechDivision\Application\Interfaces\ApplicationInterface $application The application instance
     *
     * @return void
     * @see \TechDivision\Application\Interfaces\ManagerInterface::initialize()
     */
    public function initialize(ApplicationInterface $application)
    {
        // register beans in container
        $this->registerBeans($application);

        // register timers
        $this->registerTimers();
    }

    /**
     * Registers the message beans at startup.
     *
     * @param \TechDivision\Application\Interfaces\ApplicationInterface $application The application instance
     *
     * @return void
     */
    protected function registerBeans(ApplicationInterface $application)
    {
        // build up META-INF directory var
        $metaInfDir = $this->getWebappPath() . DIRECTORY_SEPARATOR .'META-INF';

        // check if we've found a valid directory
        if (!is_dir($metaInfDir)) {
            return;
        }

        // check meta-inf classes or any other sub folder to pre init beans
        $phpFiles = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($metaInfDir)
            ),
            '/^(.+)\.php$/i'
        );

        // iterate all php files
        foreach ($phpFiles as $phpFile) {
            // check if it's a valid bean
            $className = str_replace('/', '\\', substr(preg_replace('/' . str_replace('/', '\/', $metaInfDir) . '\/[^\/]+\//', '', $phpFile), 0, -4));
            // try to lookup bean by reflection class
            try {
                $this->getResourceLocator()->lookup($this, $className, null, array($application));
            } catch (\Exception $e) {
                // if class can not be reflected continue with next class
                continue;
            }
        }
    }

    /**
     * Registers the timers for message beans at startup
     *
     * @return void
     */
    protected function registerTimers()
    {

    }

    /**
     * Returns the absolute path to the web application.
     *
     * @return string The absolute path
     */
    public function getWebappPath()
    {
        return $this->webappPath;
    }

    /**
     * Return the resource locator instance.
     *
     * @return \TechDivision\PersistenceContainer\ResourceLocator The resource locator instance
     */
    public function getResourceLocator()
    {
        return $this->resourceLocator;
    }

    /**
     * Tries to locate the queue that handles the request and returns the instance
     * if one can be found.
     *
     * @param \TechDivision\PersistenceContainerProtocol\RemoteMethod $remoteMethod The remote method call
     * @param array                                                   $args         The arguments passed to the session beans constructor
     *
     * @return object The requested bean instance
     */
    public function locate(RemoteMethod $remoteMethod, array $args = array())
    {
        return $this->getResourceLocator()->locate($this, $remoteMethod, $args);
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
            switch ($beanType = $this->getBeanAnnotation($reflectionObject)) {

                case BeanUtils::STATEFUL: // @Stateful

                    // lock the container
                    $this->beans->lock();

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
                    $this->beans->unlock();

                    break;

                case BeanUtils::SINGLETON: // @Singleton
                case BeanUtils::STATELESS: // @Stateless
                case BeanUtils::MESSAGEDRIVEN: // @MessageDriven

                    // add bean to container
                    $this->setAttribute($reflectionObject->getName(), $instance);

                    break;

                default: // this should never happen

                    throw new InvalidBeanTypeException("Try to attach invalid bean type '$beanType'");
                    break;
            }

        } catch (\Exception $e) {
            $this->unlock();
            throw $e;
        }
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
        $beanAnnotations = array(
            BeanUtils::SINGLETON,
            BeanUtils::STATEFUL,
            BeanUtils::STATELESS,
            BeanUtils::MESSAGEDRIVEN
        );

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
     * Registers the value with the passed key in the container.
     *
     * @param string $key   The key to register the value with
     * @param object $value The value to register
     *
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $this->beans->set($key, $value);
    }

    /**
     * Returns the attribute with the passed key from the container.
     *
     * @param string $key The key the requested value is registered with
     *
     * @return object The requested value
     */
    public function getAttribute($key)
    {
        return $this->beans->get($key);
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
        return new \ReflectionClass($className);
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
        // create and return a new instance
        $reflectionClass = $this->newReflectionClass($className);
        return $reflectionClass->newInstanceArgs($args);
    }

    /**
     * Initializes the manager instance.
     *
     * @return void
     * @see \TechDivision\Application\Interfaces\ManagerInterface::initialize()
     */
    public function getIdentifier()
    {
        return BeanContext::IDENTIFIER;
    }

    /**
     * Factory method that adds a initialized manager instance to the passed application.
     *
     * @param \TechDivision\Application\Interfaces\ApplicationInterface $application The application instance
     *
     * @return void
     * @see \TechDivision\Application\Interfaces\ManagerInterface::get()
     */
    public static function get(ApplicationInterface $application)
    {

        // initialize the bean locator
        $beanLocator = new BeanLocator();

        // initialize the bean manager
        $beanManager = new BeanManager();
        $beanManager->injectWebappPath($application->getWebappPath());
        $beanManager->injectResourceLocator($beanLocator);

        // add the manager instance to the application
        $application->addManager($beanManager);
    }
}
