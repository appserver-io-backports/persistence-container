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
     * Initializes the queue worker with the application and the storage it should work on.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer The timer we have to handle
     */
    public function __construct(TimerInterface $timer)
    {

        // bind the timer to the task
        $this->timer = $timer;

        // start the worker
        $this->start();
    }

    /**
     * We process the timer here.
     *
     * @return void
     */
    public function run()
    {

        // load timer and service
        $timer = $this->timer;

        // wait for the next timer expiration
        $this->wait($timer->getTimeRemaining());

        // we lock the timer for this check, because if a cancel is in progress then
        // we do not want to do the isActive check, but wait for the cancelling transaction to finish
        // one way or another
        $timer->lock();

        try {

            if ($timer->isActive() === false) {

                error_log(
                    sprintf(
                        'Timer is not active, skipping this scheduled execution at: %s for %s',
                        date('Y-m-d'),
                        $timer->getId()
                    )
                );

                return;
            }

            // set the current date as the "previous run" of the timer.
            // $timer->setPreviousRun(new \DateTime());

            $timer->setNextTimeout($this->calculateNextTimeout($timer));

            // change the state to mark it as in timeout method
            $timer->setTimerState(TimerState::IN_TIMEOUT);

            // persist changes
            // $timerService->persistTimer($timer, false);

        } catch (\Exception $e) {
            error_log($e->__toString());
        }

        $timer->unlock();

        $this->callTimeout($timer);
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

            // compute the next timeout date
            return $nextExpiration->add(new \DateInterval(sprintf('PT%sS', $intervalDuration / 1000000)));
        }

        // return nothing
        return null;
    }
}
