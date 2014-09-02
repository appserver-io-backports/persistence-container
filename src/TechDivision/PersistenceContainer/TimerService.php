<?php

/**
 * TechDivision\PersistenceContainer\TimerService
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
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use Rhumsaa\Uuid\Uuid;
use TechDivision\Storage\StorageInterface;
use TechDivision\Storage\StackableStorage;
use TechDivision\Storage\GenericStackable;
use TechDivision\EnterpriseBeans\TimerConfig;
use TechDivision\EnterpriseBeans\TimerServiceInterface;
use TechDivision\EnterpriseBeans\ScheduleExpression;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\PersistenceContainerProtocol\RemoteMethodCall;
use TechDivision\Application\Interfaces\ApplicationInterface;
use TechDivision\Application\Interfaces\ManagerConfigurationInterface;

/**
 * The timer service implementation providing functionality to handle timers.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerService extends GenericStackable implements TimerServiceContext
{

    /**
     * Injects the storage for the timers.
     *
     * @param \TechDivision\Storage\StorageInterface $timers The storage for the timers
     *
     * @return void
     */
    public function injectTimers(StorageInterface $timers)
    {
        $this->timers = $timers;
    }

    /**
     * Injects the bean utilities we use.
     *
     * @param \TechDivision\PersistenceContainer\Utils\BeanUtils $beanUtils The bean utilities we use
     *
     * @return void
     */
    public function injectBeanUtils(BeanUtils $beanUtils)
    {
        $this->beanUtils = $beanUtils;
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
     * Create a calendar-based timer based on the input schedule expression.
     *
     * @param \TechDivision\EnterpriseBeans\ScheduleExpression $schedule    A schedule expression describing the timeouts for this timer.
     * @param \TechDivision\EnterpriseBeans\TimerConfig        $timerConfig Timer configuration.
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface The newly created Timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function createCalendarTimer(ScheduleExpression $schedule, TimerConfig $timerConfig = null)
    {

        // create a new timer
        $timer = new Timer();

        // set the necessary information
        $timer->setSchedule($schedule);
        $timer->setPersistent($timerConfig->isPersistent());
        $timer->setInfo($timerConfig->getInfo());

        // return the timer instance
        return $timer;
    }

    /**
     * Create an interval timer whose first expiration occurs at a given point in time and
     * whose subsequent expirations occur after a specified interval.
     *
     * @param int                                       $initialExpiration The number of milliseconds that must elapse before the firsttimer expiration notification.
     * @param int                                       $intervalDuration  The number of milliseconds that must elapse between timer
     *      expiration notifications. Expiration notifications are scheduled relative to the time of the first expiration. If
     *      expiration is delayed(e.g. due to the interleaving of other method calls on the bean) two or more expiration notifications
     *      may occur in close succession to "catch up".
     * @param \TechDivision\EnterpriseBeans\TimerConfig $timerConfig       Timer configuration.
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface The newly created Timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function createIntervalTimer($initialExpiration, $intervalDuration, TimerConfig $timerConfig)
    {
        // TODO: Implement createIntervalTimer() method.
    }

    /**
     * Create a single-action timer that expires after a specified duration.
     *
     * @param int                                       $duration    The number of milliseconds that must elapse before the timer expires.
     * @param \TechDivision\EnterpriseBeans\TimerConfig $timerConfig Timer configuration.
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface The newly created Timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function createSingleActionTimer($duration, TimerConfig $timerConfig)
    {
        // TODO: Implement createSingleActionTimer() method.
    }

    /**
     * Get all the active timers associated with this bean.
     *
     * @return \TechDivision\Storage\StorageInterface A collection of Timer objects.
     *
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getTimers()
    {
        return $this->timers;
    }

    /**
     * Returns all active timers associated with the beans in the same module in which the caller
     * bean is packaged. These include both the programmatically-created timers and
     * the automatically-created timers.
     *
     * @return array<TimerInterface> A collection of javax.ejb.Timer objects.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getAllTimers()
    {
        return $this->timers;
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
        $this->registerTimers($application);
    }

    /**
     * Registers the timers, declared by the bean annotations, at startup.
     *
     * @param \TechDivision\Application\Interfaces\ApplicationInterface $application The application instance
     *
     * @return void
     */
    protected function registerTimers(ApplicationInterface $application)
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
                $reflectionClass = new \ReflectionClass($className);

                // first check if the bean implements the interface necessary for a timed object
                if ($reflectionClass->implementsInterface('TechDivision\EnterpriseBeans\TimedObjectInterface') === false) {
                    continue;
                }

                // only beans with a @Stateless or @Singleton annotation are allowed to initialize schedule timers
                if ($this->getBeanUtils()->hasBeanAnnotation($reflectionClass, BeanUtils::STATELESS) ||
                    $this->getBeanUtils()->hasBeanAnnotation($reflectionClass, BeanUtils::SINGLETON)) {

                    // check the methods of the bean for a @Schedule annotation
                    foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {

                        // then check if we found a bean with @Schedule annotation
                        if ($this->getBeanUtils()->hasMethodAnnotation($reflectionMethod, BeanUtils::SCHEDULE)) { // register the timers

                            // load the schedule annotation instance
                            $annotation = $this->getBeanUtils()->getMethodAnnotationInstance($reflectionMethod, BeanUtils::SCHEDULE);

                            // create the schedule and the timer config
                            $schedule = $this->getBeanUtils()->createScheduleAnnotationFromScheduleExpression($annotation);
                            $timerConfig = $this->getBeanUtils()->createTimerConfigFromReflectionMethod($reflectionMethod);

                            // create and add a new calendar timer
                            $this->timers[Uuid::uuid4()->__toString()] = $this->createCalendarTimer($schedule, $timerConfig);

                            // log a message that we've successfully add a timer
                            $application->getInitialContext()->getSystemLogger()->debug(
                                sprintf(
                                    'Successfully added scheduled timer for method %s::%s to timer service',
                                    $reflectionMethod->getDeclaringClass()->getName(),
                                    $reflectionMethod->getName()
                                )
                            );
                        }
                    }
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
     * Returns the absolute path to the web application.
     *
     * @return string The absolute path
     */
    public function getWebappPath()
    {
        return $this->webappPath;
    }

    /**
     * Returns the bean utilties.
     *
     * @return \TechDivision\PersistenceContainer\Utils\BeanUtils The bean utilities.
     */
    public function getBeanUtils()
    {
        return $this->beanUtils;
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

        // create an instance of the bean utilities
        $beanUtils = new BeanUtils();

        // initialize the stackable for the timers
        $timers = new StackableStorage();

        // initialize the timer service
        $timerService = new TimerService();
        $timerService->injectTimers($timers);
        $timerService->injectBeanUtils($beanUtils);
        $timerService->injectWebappPath($application->getWebappPath());

        // add the initialized manager instance to the application
        $application->addManager($timerService);
    }
}
