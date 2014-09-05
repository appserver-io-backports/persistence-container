<?php
/**
 * TechDivision\PersistenceContainer\CalendarTimerBuilder
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
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\EnterpriseBeans\TimerServiceInterface;

/**
 * Class Timer
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class CalendarTimerBuilder extends TimerBuilder
{

    /**
     * @var string
     */
    protected $scheduleExprSecond;

    /**
     * @var string
     */
    protected $scheduleExprMinute;

    /**
     * @var string
     */
    protected $scheduleExprHour;

    /**
     * @var string
     */
    protected $scheduleExprDayOfWeek;

    /**
     * @var string
     */
    protected $scheduleExprDayOfMonth;

    /**
     * @var string
     */
    protected $scheduleExprMonth;

    /**
     * @var string
     */
    protected $scheduleExprYear;

    /**
     * @var \DateTime
     */
    protected $scheduleExprStartDate;

    /**
     * @var \DateTime
     */
    protected $cheduleExprEndDate;

    /**
     * @var string
     */
    protected $scheduleExprTimezone;

    /**
     * @var boolean
     */
    protected $autoTimer;

    /**
     * @var \TechDivision\PersistenceContainer\TimeoutMethod
     */
    protected $timeoutMethod;

    /**
     *
     * @param string $scheduleExprSecond
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprSecond($scheduleExprSecond)
    {
        $this->scheduleExprSecond = $scheduleExprSecond;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprMinute
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprMinute($scheduleExprMinute)
    {
        $this->scheduleExprMinute = $scheduleExprMinute;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprHour
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprHour($scheduleExprHour)
    {
        $this->scheduleExprHour = $scheduleExprHour;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprDayOfWeek
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprDayOfWeek($scheduleExprDayOfWeek)
    {
        $this->scheduleExprDayOfWeek = $scheduleExprDayOfWeek;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprDayOfMonth
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprDayOfMonth($scheduleExprDayOfMonth)
    {
        $this->scheduleExprDayOfMonth = $scheduleExprDayOfMonth;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprMonth
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprMonth($scheduleExprMonth)
    {
        $this->scheduleExprMonth = $scheduleExprMonth;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprYear
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprYear($scheduleExprYear)
    {
        $this->scheduleExprYear = $scheduleExprYear;
        return $this;
    }

    /**
     *
     * @param \DateTime $scheduleExprStartDate
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprStartDate(\DateTime $scheduleExprStartDate)
    {
        $this->scheduleExprStartDate = $scheduleExprStartDate;
        return $this;
    }

    /**
     *
     * @param \DateTime $scheduleExprEndDate
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprEndDate(\DateTime $scheduleExprEndDate)
    {
        $this->scheduleExprEndDate = $scheduleExprEndDate;
        return $this;
    }

    /**
     *
     * @param string $scheduleExprTimezone
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setScheduleExprTimezone($scheduleExprTimezone)
    {
        $this->scheduleExprTimezone = $scheduleExprTimezone;
        return $this;
    }

    /**
     *
     * @param boolean $autoTimer
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setAutoTimer($autoTimer) {
        $this->autoTimer = $autoTimer;
        return $this;
    }

    /**
     *
     * @param \TechDivision\PersistenceContainer\TimeoutMethod $timeoutMethod
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimerBuilder
     */
    public function setTimeoutMethod(TimeoutMethod $timeoutMethod)
    {
        $this->timeoutMethod = $timeoutMethod;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprSecond()
    {
        return $this->scheduleExprSecond;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprMinute()
    {
        return $this->scheduleExprMinute;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprHour()
    {
        return $this->scheduleExprHour;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprDayOfWeek()
    {
        return $this->scheduleExprDayOfWeek;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprDayOfMonth()
    {
        return $this->scheduleExprDayOfMonth;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprMonth()
    {
        return $this->scheduleExprMonth;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprYear()
    {
        return $this->scheduleExprYear;
    }

    /**
     *
     * @return \DateTime
     */
    public function getScheduleExprStartDate()
    {
        return $this->scheduleExprStartDate;
    }

    /**
     *
     * @return \DateTime
     */
    public function getScheduleExprEndDate()
    {
        return $this->scheduleExprEndDate;
    }

    /**
     *
     * @return string
     */
    public function getScheduleExprTimezone()
    {
        return $this->scheduleExprTimezone;
    }

    /**
     *
     * @return boolean
     */
    public function isAutoTimer()
    {
        return $this->autoTimer;
    }

    /**
     *
     * @return \TechDivision\PersistenceContainer\TimeoutMethod
     */
    public function getTimeoutMethod()
    {
        return $this->timeoutMethod;
    }

    /**
     * Creates a new timer instance with the builders data.
     *
     * @param \TechDivision\EnterpriseBeans\TimerServiceInterface The timer service
     *
     * @return \TechDivision\PersistenceContainer\CalendarTimer The initialized timer instance
     */
    public function build(TimerServiceInterface $timerService)
    {
        return new CalendarTimer($this, $timerService);
    }
}
