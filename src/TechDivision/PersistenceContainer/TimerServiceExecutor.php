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

use TechDivision\Storage\StorageInterface;
use TechDivision\Application\Interfaces\ApplicationInterface;
use TechDivision\EnterpriseBeans\TimerInterface;

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
     * The application instance.
     *
     * @var \TechDivision\Application\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * Contains the scheduled timer tasks.
     *
     * @var \TechDivision\Storage\GenericStackable
     */
    protected $scheduledTimerTasks;

    /**
     * Injects the application instance.
     *
     * @param \TechDivision\Application\ApplicationInterface $application The application instance
     *
     * @return void
     */
    public function injectApplication(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Injects the storage for the scheduled timer tasks.
     *
     * @param \TechDivision\Storage\StorageInterface $scheduledTimerTasks The storage for the scheduled timer tasks
     *
     * @return void
     */
    public function injectScheduledTimerTasks(StorageInterface $scheduledTimerTasks)
    {
        $this->scheduledTimerTasks = $scheduledTimerTasks;
    }

    /**
     * Returns the application instance.
     *
     * @return \TechDivision\Application\Interfaces\ApplicationInterface The application instance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns the scheduled timer tasks.
     *
     * @return \TechDivision\Storage\StorageInterface A collection of scheduled timer tasks
     **/
    public function getScheduledTimerTasks()
    {
        return $this->scheduledTimerTasks;
    }

    /**
     * Adds the passed timer task to the schedule.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer The timer we want to schedule
     *
     * @return void
     */
    protected function schedule(TimerInterface $timer)
    {

        // create a wrapper instance for the timer task that we want to schedule
        $timerTaskWrapper = new \stdClass();
        $timerTaskWrapper->executeAt = microtime(true) + ($timer->getTimeRemaining() / 1000000);
        $timerTaskWrapper->timer = $timer;

        // schedule the timer tasks as wrapper
        $this->scheduledTimerTasks[] = $timerTaskWrapper;

        // force handling the timer tasks now
        $this->notify();
    }

    /**
     * Only wait for executing timer tasks.
     *
     * @return void
     */
    public function run()
    {

        // array with the timer tasks that are actually running
        $timerTasksExecuting = array();

        // make the list with the scheduled timer task wrappers available
        $scheduledTimerTasks = $this->getScheduledTimerTasks();

        // make the application available and register the class loaders
        $application = $this->getApplication();
        $application->registerClassLoaders();

        while (true) { // handle the timer events

            $this->wait(1000000); // wait 1 second or till we've been notified

            // iterate over the scheduled timer tasks
            foreach ($scheduledTimerTasks as $key => $timerTaskWrapper) {

                if ($timerTaskWrapper instanceof \stdClass) { // make sure we've a wrapper found

                    // check if the task has to be executed now
                    if ($timerTaskWrapper->executeAt < microtime(true)) {
                        // if yes, create the timer task and execute it
                        $timerTasksExecuting[] = $timerTaskWrapper->timer->getTimerTask($application);
                        // remove the task wrapper from the list
                        unset ($scheduledTimerTasks[$key]);
                    }
                }
            }

            // remove the finished timer tasks
            foreach ($timerTasksExecuting as $key => $executingTimerTask) {
                if ($executingTimerTask->isFinished()) {
                    unset ($timerTasksExecuting[$key]);
                }
            }
        }
    }
}
