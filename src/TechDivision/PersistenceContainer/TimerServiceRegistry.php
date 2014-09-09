<?php

/**
 * TechDivision\PersistenceContainer\TimerServiceRegistry
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

use TechDivision\Storage\StackableStorage;
use TechDivision\Application\Interfaces\ApplicationInterface;
use TechDivision\Application\Interfaces\ManagerConfigurationInterface;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\PersistenceContainer\Annotations\Stateless;
use TechDivision\PersistenceContainer\Annotations\Singleton;
use TechDivision\PersistenceContainer\Annotations\MessageDriven;

/**
 * The timer service registry handles an applications timer services.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerServiceRegistry extends ServiceRegistry implements TimerServiceContext
{

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

        // create an instance of the bean utilities
        $beanUtils = new BeanUtils();

        // register the class loader again, because each thread has its own context
        $application->registerClassLoaders();

        // build up META-INF directory var
        $metaInfDir = $application->getWebappPath() . DIRECTORY_SEPARATOR .'META-INF';

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

                // create the reflection class instance
                $reflectionClass = new \ReflectionClass($className);

                // initialize the timed object instance with the data from the reflection class
                $timedObject = TimedObject::fromReflectionClass($reflectionClass);

                // check if we have a bean with a @Stateless, @Singleton or @MessageDriven annotation
                if ($timedObject->hasAnnotation(Stateless::ANNOTATION) === false &&
                    $timedObject->hasAnnotation(Singleton::ANNOTATION) === false &&
                    $timedObject->hasAnnotation(MessageDriven::ANNOTATION) === false
                ) {
                    continue; // if not, we don't care here!
                }

                // initialize the stackable for the timeout methods
                $timeoutMethods = new StackableStorage();

                // create the timed object invoker
                $timedObjectInvoker = new TimedObjectInvoker();
                $timedObjectInvoker->injectBeanUtils($beanUtils);
                $timedObjectInvoker->injectApplication($application);
                $timedObjectInvoker->injectTimedObject($timedObject);
                $timedObjectInvoker->injectTimeoutMethods($timeoutMethods);
                $timedObjectInvoker->start();

                // initialize the stackable for the timers
                $timers = new StackableStorage();

                // inject the timer service executor
                $timerServiceExecutor = new TimerServiceExecutor();

                // initialize the timer service
                $timerService = new TimerService();
                $timerService->injectTimers($timers);
                $timerService->injectBeanUtils($beanUtils);
                $timerService->injectTimedObjectInvoker($timedObjectInvoker);
                $timerService->injectTimerServiceExecutor($timerServiceExecutor);
                $timerService->start();

                // register the initialized timer service
                $this->register($timerService);

                // log a message that the timer service has been registered
                $application->getInitialContext()->getSystemLogger()->info(
                    sprintf(
                        'Successfully registered timer service for bean %s',
                        $reflectionClass->getName()
                    )
                );

            } catch (\Exception $e) { // if class can not be reflected continue with next class

                // log an error message
                $application->getInitialContext()->getSystemLogger()->error($e->__toString());

                // proceed with the nexet bean
                continue;
            }
        }
    }

    /**
     * Attaches the passed service, to the context.
     *
     * @param \TechDivision\PersistenceContainer\ServiceProvider $instance The service instance to attach
     *
     * @return void
     * @throws \TechDivision\PersistenceContainer\ServiceAlreadyRegisteredException Is thrown if the passed service has already been registered
     */
    public function register(ServiceProvider $instance)
    {

        // check if the service has already been registered
        if ($this->getServices()->has($pk = $instance->getPrimaryKey())) {
            throw new ServiceAlreadyRegisteredException(
                sprintf(
                    'It is not allowed to register service %s with primary key %s more than on times',
                    $instance->getServiceName(),
                    $pk
                )
            );
        }

        // register the service using the primary key
        $this->getServices()->set($pk, $instance);
    }

    /**
     * Initializes the manager instance.
     *
     * @return void
     * @see \TechDivision\Application\Interfaces\ManagerInterface::initialize()
     */
    public function getIdentifier()
    {
        return TimerServiceContext::IDENTIFIER;
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

        // initialize the service locator
        $serviceLocator = new ServiceLocator();

        // initialize the stackable for the services
        $services = new StackableStorage();

        // initialize the service registry
        $serviceRegistry = new TimerServiceRegistry();
        $serviceRegistry->injectServices($services);
        $serviceRegistry->injectServiceLocator($serviceLocator);
        $serviceRegistry->injectWebappPath($application->getWebappPath());

        // add the initialized manager instance to the application
        $application->addManager($serviceRegistry);
    }
}
