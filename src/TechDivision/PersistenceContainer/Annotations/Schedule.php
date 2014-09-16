<?php

/**
 * TechDivision\PersistenceContainer\Annotations\Schedule
 *
 * PHP version 5
 *
 * @category   Library
 * @package    TechDivision_PersistenceContainer
 * @subpackage Annotations
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer\Annotations;

use TechDivision\EnterpriseBeans\ScheduleExpression;
use TechDivision\Lang\Reflection\ReflectionAnnotation;

/**
 * Annotation implementation representing a @Schedule annotation on a bean method.
 *
 * The annotation can define the following values:
 *
 * - second
 * - minute
 * - hour
 * - dayOfWeek
 * - dayOfMonth
 * - month
 * - year
 * - start
 * - end
 * - timezone
 *
 * @category   Library
 * @package    TechDivision_PersistenceContainer
 * @subpackage Annotations
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class Schedule extends ReflectionAnnotation
{

    /**
     * The annotation for method, a timer has to be registered for.
     *
     * @var string
     */
    const ANNOTATION = 'Schedule';

    /**
     * The aliases to be replaced with valid CRON values.
     *
     * @var array
     */
    protected $aliases = array('EVERY' => '*', 'ZERO' => '0');

    /**
     * The constructor the initializes the instance with the
     * data passed with the token.
     *
     * @param string $annotationName The annotation name
     * @param array  $values         The annotation values
     */
    public function __construct($annotationName, array $values = array())
    {

        // set the annotation name
        $this->annotationName = $annotationName;

        // set the values found in the annotation
        foreach ($values as $member => $value) {

            // check if we've to replace the value
            if (array_key_exists($value, $this->aliases)) {
                $value = $this->aliases[$value];
            }

            // set the value
            $this->values[$member] = $value;
        }
    }

    /**
     * This method returns the class name as
     * a string.
     *
     * @return string
     */
    public static function __getClass()
    {
        return __CLASS__;
    }

    /**
     * Returns day of month.
     *
     * @return string The day of month
     */
    public function getDayOfMonth()
    {
        return $this->values['dayOfMonth'];
    }

    /**
     * Returns day of week.
     *
     * @return string The day of week
     */
    public function getDayOfWeek()
    {
        return $this->values['dayOfWeek'];
    }

    /**
     * Returns end date time.
     *
     * @return string The last expiration date
     */
    public function getEnd()
    {
        return $this->values['end'];
    }

    /**
     * Returns the hour.
     *
     * @return string The hour
     */
    public function getHour()
    {
        return $this->values['hour'];
    }

    /**
     * Returns the minute.
     *
     * @return string The minute
     */
    public function getMinute()
    {
        return $this->values['minute'];
    }

    /**
     * Returns the month.
     *
     * @return string The month
     */
    public function getMonth()
    {
        return $this->values['month'];
    }

    /**
     * Returns the second.
     *
     * @return string The second
     */
    public function getSecond()
    {
        return $this->values['second'];
    }

    /**
     * Returns start date time.
     *
     * @return \DateTime The initial expiration date
     */
    public function getStart()
    {
        return $this->values['start'];
    }

    /**
     * Returns the timezone.
     *
     * @return null|string The timezone
     */
    public function getTimezone()
    {
        return $this->values['timezone'];
    }

    /**
     * Returns the year.
     *
     * @return string The year
     */
    public function getYear()
    {
        return $this->values['year'];
    }

    /**
     * Creates a new schedule expression instance from this annotations data.
     *
     * @return \TechDivision\EnterpriseBeans\ScheduleExpression The expression initialzed with the data from this annotation
     */
    public function toScheduleExpression()
    {

        // create a new expression instance
        $expression = new ScheduleExpression();

        // copy the data from the annotation
        $expression->hour($this->getHour());
        $expression->minute($this->getMinute());
        $expression->month($this->getMonth());
        $expression->second($this->getSecond());
        $expression->start(new \DateTime($this->getStart()));
        $expression->end(new \DateTime($this->getEnd()));
        $expression->timezone($this->getTimezone());
        $expression->year($this->getYear());
        $expression->dayOfMonth($this->getDayOfMonth());
        $expression->dayOfWeek($this->getDayOfWeek());

        // return the expression
        return $expression;
    }
}
