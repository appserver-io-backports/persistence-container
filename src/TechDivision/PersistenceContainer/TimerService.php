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

use TechDivision\EnterpriseBeans\ScheduleExpression;
use TechDivision\EnterpriseBeans\TimerConfig;
use TechDivision\EnterpriseBeans\TimerServiceInterface;

/**
 * Class TimerService
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerService implements TimerServiceInterface
{
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
        // TODO: Implement createCalendarTimer() method.
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
     * @return array<TimerInterface> A collection of Timer objects.
     *
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getTimers()
    {
        // TODO: Implement getTimers() method.
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
        // TODO: Implement getAllTimers() method.
    }
}
