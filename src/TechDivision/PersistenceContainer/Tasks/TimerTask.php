<?php

/**
 * TechDivision\PersistenceContainer\Tasks\TimerTask
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Library
 * @package    TechDivision_PersistenceContainer
 * @subpackage Tasks
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link       http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer\Tasks;

use TechDivision\Storage\StackableStorage;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\PersistenceContainer\Utils\TimerState;
use TechDivision\Application\Interfaces\ApplicationInterface;

/**
 * The timer task.
 *
 * @category   Library
 * @package    TechDivision_PersistenceContainer
 * @subpackage Tasks
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link       http://www.appserver.io
 */
class TimerTask extends \Thread
{

    /**
     * The timer we have to handle.
     *
     * @var \TechDivision\EnterpriseBeans\TimerInterface
     */
    protected $timer;

    /**
     * The application instance.
     *
     * @var \TechDivision\Application\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * Wheter the timer task has been finished or not.
     *
     * @var boolean
     */
    protected $finished;

    /**
     * Initializes the queue worker with the application and the storage it should work on.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface              $timer       The timer we have to handle
     * @param \TechDivision\Application\Interfaces\ApplicationInterface $application The application instance
     */
    public function __construct(TimerInterface $timer, ApplicationInterface $application)
    {

        // we want to start working
        $this->finished = false;

        // initialize timer and application instance
        $this->timer = $timer;
        $this->application = $application;

        // start the timer task
        $this->start();
    }

    /**
     * Queries whether the timer task has been finished or not.
     *
     * @return boolean TRUE if the timer task has been finished, else FALSE
     */
    protected function isFinished()
    {
        return $this->finished;
    }

    /**
     * We process the timer here.
     *
     * @return void
     */
    public function run()
    {

        // register shutdown handler
        register_shutdown_function(array(&$this, "shutdown"));

        // load application and timer instance
        $application = $this->application;
        $timer = $this->timer;

        // we need to register the class loaders again
        $application->registerClassLoaders();

        // we lock the timer for this check, because if a cancel is in progress then we do not want to
        // do the isActive check, but wait for the cancelling transaction to finish one way or another
        $timer->lock();

        try {

            // check if the timer is active
            if ($timer->isActive() === false) {

                // log an info that the timer is NOT active
                $application->getInitialContext()->getSystemLogger()->info(
                    sprintf(
                        'Timer is not active, skipping this scheduled execution at: %s for %s',
                        date('Y-m-d'),
                        $timer->getId()
                    )
                );

                return; // return without do anything
            }

            // set the current date as the "previous run" of the timer
            $timer->setPreviousRun(new \DateTime());

            // set the next timeout
            $timer->setNextTimeout($this->calculateNextTimeout($timer));

            // change the state to mark it as in timeout method
            $timer->setTimerState(TimerState::IN_TIMEOUT);

            // persist changes
            $timer->getTimerService()->persistTimer($timer, false);

        } catch (\Exception $e) {
            $application->getInitialContext()->getSystemLogger()->error($e->__toString());
        }

        // unlock after timeout recalculation
        $timer->unlock();

        // call timeout method
        $this->callTimeout($timer);
    }

    /**
     * Does shutdown logic for worker if something breaks in process.
     *
     * This shutdown function will be called from specific connection handler if an error occurs, so the connection
     * handler can send an response in the correct protocol specifications and a new worker can be started
     *
     * @return void
     */
    public function shutdown()
    {
        $this->finished = true;
    }

    /**
     * Invokes the timeout on the passed timer.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer The timer we want to invoke the timeout for
     *
     * @return void
     */
    protected function callTimeout(TimerInterface $timer)
    {

        // if we have any more schedules remaining, then schedule a new task
        if ($timer->getNextExpiration() != null && !$timer->isInRetry()) {
            $timer->scheduleTimeout(false);
        }

        // invoke the timeout on the timed object
        $timer->getTimerService()->getTimedObjectInvoker()->callTimeout($timer);
    }

    /**
     * Calculates and returns the next timeout for the passed timer.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer The timer we want to calculate the next timeout for
     *
     * @return \DateTime|null The next expiration timeout
     */
    protected function calculateNextTimeout(TimerInterface $timer)
    {

        // try to load the interval
        $intervalDuration = $timer->getIntervalDuration();

        // check if we've a interval
        if ($intervalDuration > 0) {

            // load the next expiration date
            $nextExpiration = $timer->getNextExpiration();

            // compute and return the next expiration date
            return $nextExpiration->add(new \DateInterval(sprintf('PT%sS', $intervalDuration / 1000000)));
        }

        // return nothing
        return null;
    }
}
