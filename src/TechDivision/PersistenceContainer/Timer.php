<?php
/**
 * TechDivision\PersistenceContainer\Timer
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
 * @author    Johann Zelger<jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\EnterpriseBeans\TimerInterface;

/**
 * Class Timer
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Johann Zelger<jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class Timer implements TimerInterface
{
    /**
     * Cause the timer and all its associated expiration notifications to be canceled.
     *
     * @return void
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function cancel()
    {
        // TODO: Implement cancel() method.
    }

    /**
     * Get the number of milliseconds that will elapse before the next scheduled timer expiration.
     *
     * @return int Number of milliseconds that will elapse before the next scheduled timer expiration.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getTimeRemaining()
    {
        // TODO: Implement getTimeRemaining() method.
    }

    /**
     * Get the point in time at which the next timer expiration is scheduled to occur.
     *
     * @return \DateTime Get the point in time at which the next timer expiration is scheduled to occur.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getNextTimeout()
    {
        // TODO: Implement getNextTimeout() method.
    }

    /**
     * Get the information associated with the timer at the time of creation.
     *
     * @return \Serializable The Serializable object that was passed in at timer creation, or null if the
     *         info argument passed in at timer creation was null.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getInfo()
    {
        // TODO: Implement getInfo() method.
    }

    /**
     * Get a serializable handle to the timer. This handle can be used at a later time to
     * re-obtain the timer reference.
     *
     * @return \TechDivision\EnterpriseBeans\TimerHandleInterface Handle of the Timer
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getHandle()
    {
        // TODO: Implement getHandle() method.
    }

    /**
     * Get the schedule expression corresponding to this timer.
     *
     * @return \TechDivision\EnterpriseBeans\ScheduleExpression
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function getSchedule()
    {
        // TODO: Implement getSchedule() method.
    }

    /**
     * Query whether this timer is a calendar-based timer.
     *
     * @return boolean True if this timer is a calendar-based timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function isCalendarTimer()
    {
        // TODO: Implement isCalendarTimer() method.
    }

    /**
     * Query whether this timer has persistent semantics.
     *
     * @return boolean True if this timer has persistent guarantees.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function isPersistent()
    {
        // TODO: Implement isPersistent() method.
    }
}
