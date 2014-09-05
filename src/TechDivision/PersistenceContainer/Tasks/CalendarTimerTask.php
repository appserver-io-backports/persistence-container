<?php

/**
 * TechDivision\PersistenceContainer\Tasks\CalendarTimerTask
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

use Cron\CronExpression;
use TechDivision\Storage\StackableStorage;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\PersistenceContainer\Timer;

/**
 * The timer task for a calendar timer.
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
class CalendarTimerTask extends TimerTask
{

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

        // finally invoke the timeout method through the invoker
        if ($timer->isAutoTimer()) {
            $timer->getTimerService()->getTimedObjectInvoker()->callTimeout($timer, $timer->getTimeoutMethod());
        } else {
            $timer->getTimerService()->getTimedObjectInvoker()->callTimeout($timer);
        }
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
        return $timer->getCalendarTimeout()->getNextRunDate();
    }
}
