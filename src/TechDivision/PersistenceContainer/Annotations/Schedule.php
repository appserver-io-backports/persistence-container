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
class Schedule implements AnnotationInterface
{

    /**
     * The annotation for method, a timer has to be registered for.
     *
     * @var string
     */
    const ANNOTATION = 'schedule';

    /**
     * @var string
     */
    protected $dayOfMonth = "*";

    /**
     * @var string
     */
    protected $dayOfWeek = "*";

    /**
     * @var \DateTime
     */
    protected $end;

    /**
     * @var string
     */
    protected $hour = "0";

    /**
     * @var string
     */
    protected $minute = "0";

    /**
     * @var string
     */
    protected $month = "*";

    /**
     * @var string
     */
    protected $second = "0";

    /**
     * @var \DateTime
     */
    protected $start;

    /**
     * @var string
     */
    protected $timezone = "";

    /**
     * @var string
     */
    protected $year = "*";

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
     * @param \stdClass $token A simple token object
     */
    public function __construct(\stdClass $token)
    {

        // set the values found in the annotation
        foreach ($token->values as $member => $value) {

            // check if we've to replace the value
            if (array_key_exists($value, $this->aliases)) {
                $value = $this->aliases[$value];
            }

            // set the value
            $this->$member = $value;
        }
    }

    /**
     * Return's day of month
     *
     * @return string
     */
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }

    /**
     * Return's day of week
     *
     * @return string
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * Return's end datetime
     *
     * @return string
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Return's hour
     *
     * @return string
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Return's minute
     *
     * @return string
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * Return's month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Return's second
     *
     * @return string
     */
    public function getSecond()
    {
        return $this->second;
    }

    /**
     * Return's start date time
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the timezone
     *
     * @return null|string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Return's the year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
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
