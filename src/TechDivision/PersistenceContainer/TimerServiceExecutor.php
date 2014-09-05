<?php

/**
 * TechDivision\PersistenceContainer\TimerServiceExecutor
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

use TechDivision\Storage\GenericStackable;
use TechDivision\Storage\StackableStorage;
use TechDivision\EnterpriseBeans\TimerConfig;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\Application\Interfaces\ApplicationInterface;

/**
 * The executor thread for the timers.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerServiceExecutor extends \Thread
{

    /**
     * The application instance the worker is working for.
     *
     * @var \TechDivision\ApplicationServer\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * Contains the registered timer services.
     *
     * @var \TechDivision\Storage\GenericStackable
     */
    protected $timerServices;

    /**
     * Contains the scheduled timer tasks.
     *
     * @var \TechDivision\Storage\GenericStackable
     */
    protected $scheduledTimerTasks;

    /**
     * Initializes the queue worker with the application and the storage it should work on.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ApplicationInterface $application The application instance with the queue manager/locator
     */
    public function __construct(ApplicationInterface $application)
    {

        // bind the gc to the application
        $this->application = $application;

        // a collection with the
        $this->timerServices = new GenericStackable();
        $this->scheduledTimerTasks = new GenericStackable();

        // start the worker
        $this->start();
    }

    /**
     * Adds the passed timer task to the schedule.
     *
     * @param \Thread $timerTask The timer task to schedule
     *
     * @return void
     */
    public function schedule(\Thread $timerTask)
    {
        $this->scheduledTimerTasks[] = $timerTask;
    }

    /**
     * We process the messages here.
     *
     * @return void
     */
    public function run()
    {

        // create an instance of the bean utilities
        $beanUtils = new BeanUtils();

        // create a local instance of appication and the registered timers
        $application = $this->application;

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

        $beanManager = $application->getManager(BeanContext::IDENTIFIER);

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

                switch ($beanUtils->getBeanAnnotation($reflectionClass)) {

                    case BeanUtils::STATELESS:
                    case BeanUtils::SINGLETON:

                        $timedObject = $beanManager->newInstance($reflectionClass->getName(), array($application));
                        break;

                    default:

                        continue 2;
                        break;
                }

                // initialize the stackable for the timeout interceptors
                $timeoutInterceptors = new StackableStorage();

                // create the timed object invoker
                $timedObjectInvoker = new TimedObjectInvoker();
                $timedObjectInvoker->injectBeanUtils($beanUtils);
                $timedObjectInvoker->injectApplication($application);
                $timedObjectInvoker->injectTimedObject($timedObject);
                $timedObjectInvoker->injectTimeoutInterceptors($timeoutInterceptors);
                $timedObjectInvoker->start();

                // initialize the stackable for the timers
                $timers = new StackableStorage();

                // initialize the timer service
                $timerService = new TimerService();
                $timerService->injectTimers($timers);
                $timerService->injectBeanUtils($beanUtils);
                $timerService->injectTimerServiceExecutor($this);
                $timerService->injectTimedObjectInvoker($timedObjectInvoker);

                // check the methods of the bean for a @Schedule annotation
                foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {

                    // then check if we found a bean with @Schedule annotation
                    if ($beanUtils->hasMethodAnnotation($reflectionMethod, BeanUtils::SCHEDULE)) { // register the timers

                        // load the schedule annotation instance
                        $annotation = $beanUtils->getMethodAnnotationInstance($reflectionMethod, BeanUtils::SCHEDULE);

                        // create the schedule and the timer config
                        $schedule = $beanUtils->createScheduleExpressionFromScheduleAnnotation($annotation);

                        // create the timeout method that has to be invoked
                        $timeoutMethod = new TimeoutMethod($reflectionMethod->getDeclaringClass(), $reflectionMethod->getName());

                        // create and add a new calendar timer
                        $timerService->createCalendarTimer($schedule, null, true, $timeoutMethod);

                        $this->timersRegistered[] = $timerService;

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

        // still wait and do nothing
        while (true) {
            $this->wait(1000000); // wait one second
        }
    }
}
