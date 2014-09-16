<?php

/**
 * TechDivision\PersistenceContainer\Annotations\AbstractBeanAnnotation
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

use TechDivision\Lang\Reflection\ReflectionAnnotation;

/**
 * Abstract bean annotation implementation.
 *
 * @category   Library
 * @package    TechDivision_PersistenceContainer
 * @subpackage Annotations
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class AbstractBeanAnnotation extends ReflectionAnnotation
{

    /**
     * Returns the value of the name attribute.
     *
     * @return string The annotations name attribute
     */
    public function getName()
    {
        return $this->values['name'];
    }

    /**
     * Returns the value of the mapped name attribute.
     *
     * @return string The annotations mapped name attribute
     */
    public function getMappedName()
    {
        return $this->values['mappedName'];
    }

    /**
     * Returns the value of the description attribute.
     *
     * @return string The annotations description attribute
     */
    public function getDescription()
    {
        return $this->values['description'];
    }
}
