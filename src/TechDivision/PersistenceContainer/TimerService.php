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

use Rhumsaa\Uuid\Uuid;
use TechDivision\Storage\StorageInterface;
use TechDivision\Storage\GenericStackable;
use TechDivision\EnterpriseBeans\TimerConfig;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\EnterpriseBeans\MethodInterface;
use TechDivision\EnterpriseBeans\ScheduleExpression;
use TechDivision\EnterpriseBeans\TimerServiceInterface;
use TechDivision\EnterpriseBeans\TimedObjectInvokerInterface;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\PersistenceContainerProtocol\RemoteMethodCall;
use TechDivision\PersistenceContainer\Utils\TimerState;

/**
 * The timer service implementation providing functionality to handle timers.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerService extends GenericStackable implements TimerServiceInterface, ServiceProvider
{

    /**
     * Initializes the timer service instance.
     */
    public function __construct()
    {
        $this->started = false;
    }

    /**
     * Injects the timed object invoker handling timer invokation on timed object instances.
     *
     * @param \TechDivision\EnterpriseBeans\TimedObjectInvokerInterface $timedObjectInvoker The timed object invoker instance
     *
     * @return void
     */
    public function injectTimedObjectInvoker(TimedObjectInvokerInterface $timedObjectInvoker)
    {
        $this->timedObjectInvoker = $timedObjectInvoker;
    }

    /**
     * Injects the timer service executor.
     *
     * @param \TechDivision\PersistencContainer\TimerServiceExecutor $timerServiceExecutor The timer service executor instance
     *
     * @return void
     */
    public function injectTimerServiceExecutor(TimerServiceExecutor $timerServiceExecutor)
    {
        $this->timerServiceExecutor = $timerServiceExecutor;
    }

    /**
     * Injects the storage for the timers.
     *
     * @param \TechDivision\Storage\StorageInterface $timers The storage for the timers
     *
     * @return void
     */
    public function injectTimers(StorageInterface $timers)
    {
        $this->timers = $timers;
    }

    /**
     * Injects the bean utilities we use.
     *
     * @param \TechDivision\PersistenceContainer\Utils\BeanUtils $beanUtils The bean utilities we use
     *
     * @return void
     */
    public function injectBeanUtils(BeanUtils $beanUtils)
    {
        $this->beanUtils = $beanUtils;
    }

    /**
     * Returns identifier for this timer service instance.
     *
     * @return string The primary key of the timer service instance
     * @see \TechDivision\PersistenceContainer\ServiceProvider::getPrimaryKey()
     */
    public function getPrimaryKey()
    {
        return $this->getTimedObjectInvoker()->getTimedObjectId();
    }

    /**
     * Returns the unique service name.
     *
     * @return string The service name
     * @see \TechDivision\PersistenceContainer\ServiceProvider::getServiceName()
     */
    public function getServiceName()
    {
        return 'EnterpriseBeans.TimerService';
    }

    /**
     * Create an interval timer whose first expiration occurs at a given point in time and
     * whose subsequent expirations occur after a specified interval.
     *
     * @param integer       $initialExpiration The number of milliseconds that must elapse before the firsttimer expiration notification
     * @param integer       $intervalDuration  The number of milliseconds that must elapse between timer
     *      expiration notifications. Expiration notifications are scheduled relative to the time of the first expiration. If
     *      expiration is delayed(e.g. due to the interleaving of other method calls on the bean) two or more expiration notifications
     *      may occur in close succession to "catch up".
     * @param \Serializable $info              Serializable info that will be made available through the newly created timers Timer::getInfo() method
     * @param boolean       $persistent        TRUE if the newly created timer has to be persistent
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface The newly created Timer
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure
     **/
    public function createIntervalTimer($initialExpiration, $intervalDuration, \Serializable $info = null, $persistent = true)
    {

        // create the actual date and add the initial expiration
        $now = new \DateTime();
        $now->add(new \DateInterval(sprintf('PT%dS', $initialExpiration)));

        // create a new timer
        return $this->createTimer($now, $intervalDuration, $info, $persistent);
    }

    /**
     * Create a single-action timer that expires after a specified duration.
     *
     * @param integer       $duration   The number of microseconds that must elapse before the timer expires
     * @param \Serializable $info       Serializable info that will be made available through the newly created timers Timer::getInfo() method
     * @param boolean       $persistent TRUE if the newly created timer has to be persistent
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface The newly created Timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function createSingleActionTimer($duration, \Serializable $info = null, $persistent = true)
    {

        // create the actual date and add the initial expiration
        $now = new \DateTime();
        $now->add(new \DateInterval(sprintf('PT%dS', $duration)));

        // we don't have an interval
        $intervalDuration = 0;

        // create and return the timer instance
        return $this->createTimer($now, $intervalDuration, $info, $persistent);
    }

    /**
     * Create a calendar-based timer based on the input schedule expression.
     *
     * @param \TechDivision\EnterpriseBeans\ScheduleExpression $schedule      A schedule expression describing the timeouts for this timer
     * @param \Serializable                                    $info          Serializable info that will be made available through the newly created timers Timer::getInfo() method
     * @param boolean                                          $persistent    TRUE if the newly created timer has to be persistent
     * @param \TechDivision\EnterpriseBeans\MethodInterface    $timeoutMethod The timeout method to be invoked
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface The newly created Timer.
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     */
    public function createCalendarTimer(ScheduleExpression $schedule, \Serializable $info = null, $persistent = true, MethodInterface $timeoutMethod = null)
    {

        // create the timer
        $timer = CalendarTimer::builder()
            ->setAutoTimer($timeoutMethod != null)
            ->setScheduleExprSecond($schedule->getSecond())
            ->setScheduleExprMinute($schedule->getMinute())
            ->setScheduleExprHour($schedule->getHour())
            ->setScheduleExprDayOfWeek($schedule->getDayOfWeek())
            ->setScheduleExprDayOfMonth($schedule->getDayOfMonth())
            ->setScheduleExprMonth($schedule->getMonth())
            ->setScheduleExprYear($schedule->getYear())
            ->setScheduleExprStartDate(\DateTime::createFromFormat(ScheduleExpression::DATE_FORMAT, $schedule->getStart()))
            ->setScheduleExprEndDate(\DateTime::createFromFormat(ScheduleExpression::DATE_FORMAT, $schedule->getEnd()))
            ->setScheduleExprTimezone($schedule->getTimezone())
            ->setTimeoutMethod($timeoutMethod)
            ->setTimerState(TimerState::CREATED)
            ->setId(Uuid::uuid4()->__toString())
            ->setPersistent($persistent)
            ->setTimedObjectId($this->getTimedObjectInvoker()->getTimedObjectId())
            ->setInfo($info)
            ->setNewTimer(true)
            ->build($this);

        // $this->persistTimer($timer, true);

        // now "start" the timer. This involves, moving the timer to an ACTIVE state and scheduling the timer task
        $this->startTimer($timer);

        // return the timer
        return $timer;
    }

    /**
     * Create a new timer instance with the passed data.
     *
     * @param \DateTime     $initialExpiration The date at which the first timeout should occur.
     *     If the date is in the past, then the timeout is triggered immediately
     *     when the timer moves to TimerState::ACTIVE
     * @param integer       $intervalDuration  The interval (in milli seconds) between consecutive timeouts for the newly created timer.
     *     Cannot be a negative value. A value of 0 indicates a single timeout action
     * @param \Serializable $info              Serializable info that will be made available through the newly created timers Timer::getInfo() method
     * @param boolean       $persistent        TRUE if the newly created timer has to be persistent
     *
     * @return \TechDivision\EnterpriseBeans\TimerInterface Returns the newly created timer
     * @throws IllegalArgumentException If initialExpiration is null or intervalDuration is negative
     * @throws IllegalStateException If this method was invoked during a lifecycle callback on the enterprise bean
     */
    protected function createTimer(\DateTime $initialExpiration, $intervalDuration = 0, \Serializable $info = null, $persistent = true)
    {

        // create the timer
        $timer = Timer::builder()
            ->setNewTimer(true)
            ->setId(Uuid::uuid4()->__toString())
            ->setInitialDate($initialExpiration)
            ->setRepeatInterval($intervalDuration)
            ->setInfo($info)
            ->setPersistent($persistent)
            ->setTimerState(TimerState::CREATED)
            ->setTimedObjectId($this->getTimedObjectInvoker()->getTimedObjectId())
            ->build($this);

        // $this->persistTimer($timer, true);

        // now "start" the timer. This involves, moving the timer to an ACTIVE state and scheduling the timer task
        $this->startTimer($timer);

        // return the newly created timer
        return $timer;
    }

    /**
     * Initially starts the passed timer.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer The timer we want to start
     *
     * @return void
     */
    protected function startTimer(TimerInterface $timer)
    {
        $this->registerTimer($timer);
        $timer->scheduleTimeout(true);
    }

    /**
     * Registers the passed timer in the timer service instance.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer The timer we want to register for this timer service
     *
     * @return void
     */
    protected function registerTimer(TimerInterface $timer)
    {
        $this->timers[$timer->getId()] = $timer;
    }

    /**
     * Creates and schedules a timer taks for the next timeout of the passed timer.
     *
     * @param \TechDivision\EnterpriseBeans\TimerInterface $timer    The timer we want to schedule a task for
     * @param boolean                                      $newTimer TRUE if this is a new timer being scheduled, and not a re-schedule due to a timeout
     *
     * @return void
     */
    public function scheduleTimeout(TimerInterface $timer, $newTimer)
    {

        // check if this timer has been cancelled by another thread
        if ($newTimer === false && $this->timers->has($timer->getId()) === false) {
            return; // if yes, we just return
        }

        // if next expiration is NULL, no more tasks will be scheduled for this timer
        if ($timer->getNextExpiration() == null) {
            return; // we just return
        }

        // create the timer task and schedule it
        $this->getTimerServiceExecutor()->schedule($timer->getTimerTask());
    }

    /**
     * Creates the auto timer instances.
     *
     * @return void
     */
    public function start()
    {

        // load the timeout methods annotated with @Schedule
        foreach ($this->getTimedObjectInvoker()->getTimeoutMethods() as $timeoutMethod) {

            if ($timeoutMethod instanceof TimeoutMethod) { // make sure we've a timeout method

                // create the schedule expression from the timeout methods @Schedule annotation
                $schedule = $timeoutMethod->getAnnotation(Schedule::ANNOTATION)->toScheduleExpression();

                // create and add a new calendar timer
                $this->createCalendarTimer($schedule, null, true, $timeoutMethod);
            }
        }

        // mark timer service as started
        $this->started = true;
    }

    /**
     * Get all the active timers associated with this bean.
     *
     * @return \TechDivision\Storage\StorageInterface A collection of Timer objects.
     *
     * @throws \TechDivision\EnterpriseBeans\EnterpriseBeansException If this method could not complete due to a system-level failure.
     **/
    public function getTimers()
    {
        return $this->timers;
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
        return $this->timers;
    }

    /**
     * Returns the bean utilties.
     *
     * @return \TechDivision\PersistenceContainer\Utils\BeanUtils The bean utilities.
     */
    public function getBeanUtils()
    {
        return $this->beanUtils;
    }

    /**
     * Returns the timed object invoker handling timer invokation on timed object instances.
     *
     * @return \TechDivision\EnterpriseBeans\TimedObjectInvokerInterface The timed object invoker instance
     */
    public function getTimedObjectInvoker()
    {
        return $this->timedObjectInvoker;
    }

    /**
     * Returns the timer object executor instances.
     *
     * @return \TechDivision\PersistenceContainer\TimerServiceExecutor The timer service executor
     */
    public function getTimerServiceExecutor()
    {
        return $this->timerServiceExecutor;
    }

    /**
     * Queries if the timer with the passed ID has already been scheduled.
     *
     * @param string $id The ID of the timer
     *
     * @return boolean TRUE if the timer is schedule, else FALSE
     */
    public function isScheduled($id)
    {
        return array_key_exists($id, $this->timers);
    }

    /**
     * Queries whether the service has been started or not.
     *
     * @return boolean TRUE if the service has been started, else FALSE
     */
    public function isStarted()
    {
        return $this->started;
    }
}
