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
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\EnterpriseBeans\ScheduleExpression;

/**
 * Class Timer
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class Timer implements TimerInterface
{

    /**
     * The schedule expression corresponding to this timer.
     *
     * @var \TechDivision\EnterpriseBeans\ScheduleExpression
     */
    protected $schedule;

    /**
     * The Serializable object that was passed in at timer creation.
     *
     * @var \Serializable
     */
    protected $info;

    /**
     * TRUE if this timer has persistent guarantees.
     *
     * @var boolean
     */
    protected $persistent = true;

    /**
     * Sets the schedule expression corresponding to this timer.
     *
     * @return \TechDivision\EnterpriseBeans\ScheduleExpression
     *
     * @return void
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function setSchedule(ScheduleExpression $schedule)
    {
        // $this->schedule = $schedule;
    }

    /**
     * Marks the timer to has persistent guarantees.
     *
     * @param boolean $persistent TRUE if the timer has persistent guarantees
     *
     * @return void
     */
    public function setPersistent($persistent)
    {
        $this->persistent = $persistent;
    }

    /**
     * Sets the information associated with the timer at the time of creation.
     *
     * @param \Serializable $info The Serializable object that was passed in at timer creation.
     *
     * @return void
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function setInfo(\Serializable $info)
    {
        $this->info = $info;
    }

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
        return 10000000;
    }

    /**
     * Get the point in time at which the next timer expiration is scheduled to occur.
     *
     * @return \DateTime Get the point in time at which the next timer expiration is scheduled to occur.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getNextTimeout()
    {
        /*
        $nextTimeout = new \DateTime();
        $nextTimeout->sub(\DateInterval::createFromDateString('1 seconds'));
        return $nextTimeout;
        */
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
        return $this->info;
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
        return $this->schedule;
    }

    /**
     * Query whether this timer is a calendar-based timer.
     *
     * @return boolean True if this timer is a calendar-based timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function isCalendarTimer()
    {
        return isset($this->schedule);
    }

    /**
     * Query whether this timer has persistent semantics.
     *
     * @return boolean True if this timer has persistent guarantees.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function isPersistent()
    {
        return $this->persistent;
    }
}
