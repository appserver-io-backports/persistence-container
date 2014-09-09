<?php

/**
 * TechDivision\PersistenceContainer\Annotations\AbstractAnnotation
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

use TechDivision\Lang\Object;
use TechDivision\Lang\Reflection\AnnotationInterface;

/**
 * Abstract annotation implementation.
 *
 * @category   Library
 * @package    TechDivision_PersistenceContainer
 * @subpackage Annotations
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class AbstractAnnotation extends Object implements AnnotationInterface
{

    /**
     * The annotation name.
     *
     * @var string
     */
    protected $name;

    /**
     * The array with the annotation values.
     *
     * @var array
     */
    protected $values = array();

    /**
     * The constructor the initializes the instance with the
     * data passed with the token.
     *
     * @param \stdClass $token A simple token object
     */
    public function __construct(\stdClass $token)
    {
        // set the token name and values
        $this->name = $token->name;
        $this->values = $token->values;
    }

    /**
     * Returns the annation name.
     *
     * @return string The annotation name
     */
    public function getName()
    {
        return $this->name;
    }
}
