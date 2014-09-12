<?php
/**
 * TechDivision\PersistenceContainer\CalendarBasedTimeout
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

use Cron\FieldFactory;
use Cron\CronExpression;
use TechDivision\EnterpriseBeans\ScheduleExpression;

/**
 * A wrapper for a cron expression implementation.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class CalendarBasedTimeout extends CronExpression
{

    /**
     * The schedule expression with the data to create the instance with.
     *
     * @var \TechDivision\EnterpriseBeans\ScheduleExpression
     */
    protected $scheduleExpression;

    /**
     * Parse a CRON expression
     *
     * @param string                                           $expression         CRON expression (e.g. '8 * * * *')
     * @param \TechDivision\EnterpriseBeans\ScheduleExpression $scheduleExpression The schedule expression with the data to create the instance with
     * @param \Cron\FieldFactory                               $fieldFactory       Factory to create cron fields
     */
    public function __construct($expression, ScheduleExpression $scheduleExpression, FieldFactory $fieldFactory = null)
    {
        // call parent constructor
        parent::__construct($expression, $fieldFactory);

        // set the schedule expression instance
        $this->scheduleExpression = $scheduleExpression;
    }

    /**
     * Additional factory method that creates a new instance from
     * the passed schedule expression.
     *
     * @param \TechDivision\EnterpriseBeans\ScheduleExpression $scheduleExpression The schedule expression with the data to create the instance with
     *
     * @return \TechDivision\PersistenceContainer\CalendarBasedTimeout The instance
     */
    public static function factoryFromScheduleExpression(ScheduleExpression $scheduleExpression)
    {

        // prepare the CRON expression
        $cronExpression = sprintf(
            '%s %s %s %s %s %s',
            $scheduleExpression->getMinute(),
            $scheduleExpression->getHour(),
            $scheduleExpression->getDayOfMonth(),
            $scheduleExpression->getMonth(),
            $scheduleExpression->getDayOfWeek(),
            $scheduleExpression->getYear()
        );

        // return the point in time at which the next timer expiration is scheduled to occur
        return new CalendarBasedTimeout($cronExpression, $scheduleExpression, new FieldFactory());
    }

    /**
     * Returns the schedule expression the CRON has been initialized with.
     *
     * @return \TechDivision\EnterpriseBeans\ScheduleExpression The schedule expression
     */
    public function getScheduleExpression()
    {
        return $this->scheduleExpression;
    }
}
