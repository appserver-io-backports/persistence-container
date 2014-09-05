<?php
/**
 * TechDivision\PersistenceContainer\TimerBuilder
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
class TimerBuilder
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $timedObjectId;

    /**
     * @var \DateTime
     */
    protected $initialDate;

    /**
     * @var integer
     */
    protected $repeatInterval;

    /**
     * @var \DateTime
     */
    protected $nextDate;

    /**
     * @var \DateTime
     */
    protected $previousRun;

    /**
     * @var \Serializable
     */
    protected $info;

    /**
     * @var integer
     */
    protected $timerState;

    /**
     * @var boolean
     */
    protected $persistent;

    /**
     * @var boolean
     */
    protected $newTimer;

    /**
     *
     * @param string $id
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @param string $timedObjectId
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setTimedObjectId($timedObjectId)
    {
        $this->timedObjectId = $timedObjectId;
        return $this;
    }

    /**
     *
     * @param \DateTime $initialDate
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setInitialDate(\DateTime $initialDate)
    {
        $this->initialDate = $initialDate;
        return $this;
    }

    /**
     *
     * @param integer $repeatInterval
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setRepeatInterval($repeatInterval)
    {
        $this->repeatInterval = $repeatInterval;
        return $this;
    }

    /**
     *
     * @param \DateTime $nextDate
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setNextDate(\DateTime $nextDate)
    {
        $this->nextDate = $nextDate;
        return $this;
    }

    /**
     *
     * @param \DateTime $previousRun
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setPreviousRun(\DateTime $previousRun)
    {
        $this->previousRun = $previousRun;
        return $this;
    }

    /**
     *
     * @param \Serializable $info
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setInfo(\Serializable $info = null)
    {
        $this->info = info;
        return $this;
    }

    /**
     *
     * @param integer $timerState
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setTimerState($timerState)
    {
        $this->timerState = $timerState;
        return $this;
    }

    /**
     *
     * @param boolean $persistent
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setPersistent($persistent)
    {
        $this->persistent = $persistent;
        return $this;
    }

    /**
     *
     * @param boolean $newTimer
     *
     * @return \TechDivision\PersistenceContainer\TimerBuilder
     */
    public function setNewTimer($newTimer) {
        $this->newTimer = $newTimer;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getTimedObjectId()
    {
        return $this->timedObjectId;
    }

    /**
     *
     * @return \DateTime
     */
    public function getInitialDate()
    {
        return $this->initialDate;
    }

    /**
     *
     * @return integer
     */
    public function getRepeatInterval()
    {
        return $this->repeatInterval;
    }

    /**
     *
     * @return \DateTime
     */
    public function getNextDate()
    {
        return $this->nextDate;
    }

    /**
     *
     * @return \DateTime
     */
    public function getPreviousRun()
    {
        return $this->previousRun;
    }

    /**
     *
     * @return \Serializable
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     *
     * @return integer
     */
    public function getTimerState()
    {
        return $this->timerState;
    }

    /**
     *
     * @return boolean
     */
    public function isPersistent()
    {
        return $this->persistent;
    }

    /**
     *
     * @return boolean
     */
    public function isNewTimer()
    {
        return $this->newTimer;
    }

    /**
     * Creates a new timer instance with the builders data.
     *
     * @param \TechDivision\EnterpriseBeans\TimerServiceInterface The timer service
     *
     * @return \TechDivision\PersistenceContainer\Timer The initialized timer instance
     */
    public function build(TimerServiceInterface $timerService)
    {
        return new Timer($this, $timerService);
    }
}
