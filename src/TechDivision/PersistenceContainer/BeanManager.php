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

use TechDivision\Collections\ArrayList;
use TechDivision\Collections\HashMap;
use TechDivision\Storage\StorageInterface;
use TechDivision\Storage\GenericStackable;
use TechDivision\Storage\StackableStorage;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\PersistenceContainerProtocol\RemoteMethod;
use TechDivision\Application\Interfaces\ApplicationInterface;
use TechDivision\Application\Interfaces\ManagerConfigurationInterface;
use TechDivision\PersistenceContainer\Annotations\MessageDriven;
use TechDivision\PersistenceContainer\Annotations\PreDestroy;
use TechDivision\PersistenceContainer\Annotations\PostConstruct;
use TechDivision\PersistenceContainer\Annotations\Singleton;
use TechDivision\PersistenceContainer\Annotations\Startup;
use TechDivision\PersistenceContainer\Annotations\Stateful;
use TechDivision\PersistenceContainer\Annotations\Stateless;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\PersistenceContainer\Annotations\Timeout;
use TechDivision\Lang\Reflection\ClassInterface;
use TechDivision\Lang\Reflection\ReflectionClass;
use TechDivision\Lang\Reflection\ReflectionObject;
use TechDivision\Lang\Reflection\AnnotationInterface;

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
     */
    public function __construct()
    {
        $this->data = new StackableStorage();
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
     * Injects the storage for the naming directory.
     *
     * @param \TechDivision\Storage\StorageInterface $namingDirectory The storage for the naming directory
     *
     * @return void
     */
    public function injectNamingDirectory(StorageInterface $namingDirectory)
    {
        $this->namingDirectory = $namingDirectory;
    }

    /**
     * Injects the storage for the stateful session beans.
     *
     * @param \TechDivision\Storage\StorageInterface $statefulSessionBeans The storage for the stateful session beans
     *
     * @return void
     */
    public function injectStatefulSessionBeans(StorageInterface $statefulSessionBeans)
    {
        $this->statefulSessionBeans = $statefulSessionBeans;
    }

    /**
     * Injects the storage for the singleton session beans.
     *
     * @param \TechDivision\Storage\StorageInterface $singletonSessionBeans The storage for the singleton session beans
     *
     * @return void
     */
    public function injectSingletonSessionBeans(StorageInterface $singletonSessionBeans)
    {
        $this->singletonSessionBeans = $singletonSessionBeans;
    }

    /**
     * Injects the stateful session bean settings.
     *
     * @param \TechDivision\PersistenceContainer\StatefulSessionBeanSettings $statefulSessionBeanSettings Settings for the stateful session beans
     *
     * @return void
     */
    public function injectStatefulSessionBeanSettings(StatefulSessionBeanSettings $statefulSessionBeanSettings)
    {
        $this->statefulSessionBeanSettings = $statefulSessionBeanSettings;
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
        $this->registerBeans($application);
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
        if (is_dir($metaInfDir) === false) {
            return;
        }

        // check meta-inf classes or any other sub folder to pre init beans
        $recursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($metaInfDir));
        $phpFiles = new \RegexIterator($recursiveIterator, '/^(.+)\.php$/i');

        // iterate all php files
        foreach ($phpFiles as $phpFile) {

            try {

                // cut off the META-INF directory and replace OS specific directory separators
                $relativePathToPhpFile = str_replace(DIRECTORY_SEPARATOR, '\\', str_replace($metaInfDir, '', $phpFile));

                // now cut off the first directory, that'll be '/classes' by default
                $pregResult = preg_replace('%^(\\\\*)[^\\\\]+%', '', $relativePathToPhpFile);
                $className = substr($pregResult, 0, -4);

                // we need a reflection class to read the annotations
                $reflectionClass = $this->newReflectionClass($className);

                // register the bean instance
                $this->registerBean($reflectionClass);

                // if we found a bean with @Singleton + @Startup annotation
                if ($reflectionClass->hasAnnotation(Singleton::ANNOTATION) &&
                    $reflectionClass->hasAnnotation(Startup::ANNOTATION)) { // instanciate the bean
                    $this->getResourceLocator()->lookup($this, $reflectionClass->getShortName(), null, array($application));
                }

            } catch (\Exception $e) { // if class can not be reflected continue with next class

                // log an error message
                $application->getInitialContext()->getSystemLogger()->error($e->__toString());

                // proceed with the nexet bean
                continue;
            }
        }
    }

    /**
     * Register the bean, defined by the passed reflection class instance.
     *
     * @param \TechDivision\Lang\Reflection\ClassInterface $reflectionClass The reflection class instance of the bean we want to register
     *
     * @return void
     */
    public function registerBean(ClassInterface $reflectionClass)
    {

        // declare the local variable for the reflection annotation instance
        $reflectionAnnotation = null;

        // if we found an enterprise bean with either a @Singleton annotation
        if ($reflectionClass->hasAnnotation(Singleton::ANNOTATION)) {
            $reflectionAnnotation = $reflectionClass->getAnnotation(Singleton::ANNOTATION);
        }

        // if we found an enterprise bean with either a @Stateless annotation
        if ($reflectionClass->hasAnnotation(Stateless::ANNOTATION)) {
            $reflectionAnnotation = $reflectionClass->getAnnotation(Stateless::ANNOTATION);
        }

        // if we found an enterprise bean with either a @Stateful annotation
        if ($reflectionClass->hasAnnotation(Stateful::ANNOTATION)) {
            $reflectionAnnotation = $reflectionClass->getAnnotation(Stateful::ANNOTATION);
        }

        // if we found an enterprise bean with either a @MessageDriven annotation
        if ($reflectionClass->hasAnnotation(MessageDriven::ANNOTATION)) {
            $reflectionAnnotation = $reflectionClass->getAnnotation(MessageDriven::ANNOTATION);
        }

        // can't register the bean, because of a missing enterprise bean annotation
        if ($reflectionAnnotation == null) {
            return;
        }

        // create the annotation and real PHP reflection class instance
        $annotationInstance = $this->newAnnotationInstance($reflectionAnnotation);

        // load class name and short class name
        $className = $reflectionClass->getName();
        $shortClassName = $reflectionClass->getShortName();

        // register the bean with the real class name (without namespace)
        $this->getNamingDirectory()->set($shortClassName, $className);

        // register the bean with the name defined as @Annotation(name=****)
        if ($name = $annotationInstance->getName()) {
            $this->getNamingDirectory()->set($name, $className);
        }

        // register the bean with the name defined as @Annotation(mappedName=****)
        if ($mappedName = $annotationInstance->getMappedName()) {
            $this->getNamingDirectory()->set($mappedName, $className);
        }
    }

    /**
     * Creates a new new instance of the annotation type, defined in the passed reflection annotation.
     *
     * @param \TechDivision\Lang\Reflection\AnnotationInterface $annotation The reflection annotation we want to create the instance for
     *
     * @return \TechDivision\Lang\Reflection\AnnotationInterface The real annotation instance
     */
    protected function newAnnotationInstance(AnnotationInterface $annotation)
    {
        return $annotation->newInstance($annotation->getAnnotationName(), $annotation->getValues());
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
     * Return the storage with the naming directory.
     *
     * @return \TechDivision\Storage\StorageInterface The storage with the naming directory
     */
    public function getNamingDirectory()
    {
        return $this->namingDirectory;
    }

    /**
     * Return the storage with the singleton session beans.
     *
     * @return \TechDivision\Storage\StorageInterface The storage with the singleton session beans
     */
    public function getSingletonSessionBeans()
    {
        return $this->singletonSessionBeans;
    }

    /**
     * Return the storage with the stateful session beans.
     *
     * @return \TechDivision\Storage\StorageInterface The storage with the stateful session beans
     */
    public function getStatefulSessionBeans()
    {
        return $this->statefulSessionBeans;
    }

    /**
     * Returns the stateful session bean settings.
     *
     * @return \TechDivision\PersistenceContainer\BeanSettings The stateful session bean settings
     */
    public function getStatefulSessionBeanSettings()
    {
        return $this->statefulSessionBeanSettings;
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
     * Retrieves the requested stateful session bean.
     *
     * @param string $sessionId The session-ID of the stateful session bean to retrieve
     * @param string $className The class name of the session bean to retrieve
     *
     * @return object|null The stateful session bean if available
     */
    public function lookupStatefulSessionBean($sessionId, $className)
    {

        // check if the session has already been initialized
        if ($this->getStatefulSessionBeans()->has($sessionId) === false) {
            return;
        }

        // check if the stateful session bean has already been initialized
        if ($this->getStatefulSessionBeans()->get($sessionId)->exists($className) === true) {
            return $this->getStatefulSessionBeans()->get($sessionId)->get($className);
        }
    }

    /**
     * Removes the stateful session bean with the passed session-ID and class name
     * from the bean manager.
     *
     * @param string $sessionId The session-ID of the stateful session bean to retrieve
     * @param string $className The class name of the session bean to retrieve
     *
     * @return void
     */
    public function removeStatefulSessionBean($sessionId, $className)
    {

        // check if the session has already been initialized
        if ($this->getStatefulSessionBeans()->has($sessionId) === false) {
            return;
        }

        // check if the stateful session bean has already been initialized
        if ($this->getStatefulSessionBeans()->get($sessionId)->exists($className) === true) {

            // remove the stateful session bean from the sessions
            $sessions = $this->getStatefulSessionBeans()->get($sessionId);

            // remove the instance from the sessions
            $sessions->remove($className, array($this, 'destroyBeanInstance'));

            // re-attach the map with the stateful session beans to the container
            $this->getStatefulSessionBeans()->set($sessionId, $sessions);
        }
    }

    /**
     * Retrieves the requested singleton session bean.
     *
     * @param string $className The class name of the session bean to retrieve
     *
     * @return object|null The singleton session bean if available
     */
    public function lookupSingletonSessionBean($className)
    {
        if ($this->getSingletonSessionBeans()->has($className) === true) {
            return $this->getSingletonSessionBeans()->get($className);
        }
    }

    /**
     * Invokes the bean method with the @PreDestroy annotation.
     *
     * @param object $instance The instance to invoke the method
     *
     * @return void
     */
    public function destroyBeanInstance($instance)
    {

        // we need a reflection object
        $reflectionObject = new ReflectionObject($instance);

        // we've to check for a @PreDestroy annotation
        foreach ($reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {

            // if we found a @PreDestroy annotation, invoke the method
            if ($reflectionMethod->hasAnnotation(PreDestroy::ANNOTATION)) {
                $reflectionMethod->invoke($instance); // method MUST have no parameters
            }
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

        // we need a reflection object to read the annotations
        $reflectionObject = new ReflectionObject($instance);

        // @Singleton
        if ($reflectionObject->hasAnnotation(Singleton::ANNOTATION)) {

            // we don't have to attach singleton session beans, because they extends \Stackable
            return;
        }

        // @Stateful
        if ($reflectionObject->hasAnnotation(Stateful::ANNOTATION)) {

            // check if we've a session-ID available
            if ($sessionId == null) {
                throw new \Exception('Can\'t find a session-ID to attach stateful session bean');
            }

            // initialize the map for the stateful session beans
            if ($this->getStatefulSessionBeans()->has($sessionId) === false) {
                $this->getStatefulSessionBeans()->set($sessionId, new StatefulSessionBeanMap());
            }

            // load the lifetime from the session bean settings
            $lifetime = $this->getStatefulSessionBeanSettings()->getLifetime();

            // add the stateful session bean to the map
            $sessions = $this->getStatefulSessionBeans()->get($sessionId);
            $sessions->add($reflectionObject->getName(), $instance, $lifetime);

            // re-attach the map with the stateful session beans to the container
            $this->getStatefulSessionBeans()->set($sessionId, $sessions);

            return;
        }

        // @Stateless or @MessageDriven
        if ($reflectionObject->hasAnnotation(Stateless::ANNOTATION) ||
            $reflectionObject->hasAnnotation(MessageDriven::ANNOTATION)) {

            // simply destroy the instance
            $this->destroyBeanInstance($instance);

            return;
        }

        // we've an unknown bean type => throw an exception
        throw new InvalidBeanTypeException('Try to attach bean with mission enterprise annotation');
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
        $this->data->set($key, $value);
    }

    /**
     * Returns the attribute with the passed key from the container.
     *
     * @param string $key The key the requested value is registered with
     *
     * @return mixed|null The requested value if available
     */
    public function getAttribute($key)
    {
        if ($this->data->has($key)) {
            return $this->data->get($key);
        }
    }

    /**
     * Returns a reflection class intance for the passed class name.
     *
     * @param string $className The class name to return the reflection instance for
     *
     * @return \TechDivision\Lang\Reflection\ReflectionClass The reflection instance
     */
    public function newReflectionClass($className)
    {

        // initialize the array with the annotations we want to ignore
        $annotationsToIgnore = array(
            'author',
            'package',
            'license',
            'copyright',
            'param',
            'return',
            'throws',
            'see',
            'link'
        );

        // initialize the array with the aliases for the enterprise bean annotations
        $annotationAliases = array(
            MessageDriven::ANNOTATION => MessageDriven::__getClass(),
            PostConstruct::ANNOTATION => PostConstruct::__getClass(),
            PreDestroy::ANNOTATION    => PreDestroy::__getClass(),
            Schedule::ANNOTATION      => Schedule::__getClass(),
            Singleton::ANNOTATION     => Singleton::__getClass(),
            Startup::ANNOTATION       => Startup::__getClass(),
            Stateful::ANNOTATION      => Stateful::__getClass(),
            Stateless::ANNOTATION     => Stateless::__getClass(),
            Timeout::ANNOTATION       => Timeout::__getClass()
        );

        // return the reflection class instance
        return new ReflectionClass($className, $annotationsToIgnore, $annotationAliases);
    }

    /**
     * Returns a new instance of the passed class name.
     *
     * @param string $className The fully qualified class name to return the instance for
     * @param array  $args      Arguments to pass to the constructor of the instance
     *
     * @return object The instance itself
     */
    public function newInstance($className, array $args = array())
    {

        // create and return a new instance
        $reflectionClass = $this->newReflectionClass($className);
        $instance = $reflectionClass->newInstanceArgs($args);

        // we've to check for a @PostConstruct annotations
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {

            // if we found a @PostConstruct annotation, invoke the method
            if ($reflectionMethod->hasAnnotation(PostConstruct::ANNOTATION)) {
                $reflectionMethod->invoke($instance); // method MUST has no parameters
            }
        }

        // return the instance here
        return $instance;
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
     * @param \TechDivision\Application\Interfaces\ApplicationInterface               $application          The application instance
     * @param \TechDivision\Application\Interfaces\ManagerConfigurationInterface|null $managerConfiguration The manager configuration
     *
     * @return void
     * @see \TechDivision\Application\Interfaces\ManagerInterface::get()
     */
    public static function visit(ApplicationInterface $application, ManagerConfigurationInterface $managerConfiguration = null)
    {

        // initialize the bean locator
        $beanLocator = new BeanLocator();

        // initialize the stackable for the stateful + singleton session beans and the naming directory
        $namingDirectory = new StackableStorage();
        $statefulSessionBeans = new StackableStorage();
        $singletonSessionBeans = new StackableStorage();

        // initialize the default settings for the stateful session beans
        $statefulSessionBeanSettings = new DefaultStatefulSessionBeanSettings();
        $statefulSessionBeanSettings->mergeWithParams($managerConfiguration->getParamsAsArray());

        // initialize the bean manager
        $beanManager = new BeanManager();
        $beanManager->injectResourceLocator($beanLocator);
        $beanManager->injectNamingDirectory($namingDirectory);
        $beanManager->injectWebappPath($application->getWebappPath());
        $beanManager->injectSingletonSessionBeans($singletonSessionBeans);
        $beanManager->injectStatefulSessionBeans($statefulSessionBeans);
        $beanManager->injectStatefulSessionBeanSettings($statefulSessionBeanSettings);

        // add the initialized manager instance to the application
        $application->addManager($beanManager);
    }
}
