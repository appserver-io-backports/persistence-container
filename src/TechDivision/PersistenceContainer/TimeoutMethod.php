<?php
/**
 * TechDivision\PersistenceContainer\TimeoutMethod
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
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\EnterpriseBeans\MethodInterface;

/**
 * A timeout method call implementation.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimeoutMethod implements MethodInterface
{

    /**
     * The class name to invoke the method on.
     *
     * @var string
     */
    protected $className = '';

    /**
     * The method name to invoke on the class.
     *
     * @var string
     */
    protected $methodName = '';

    /**
     * The method parameters.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Initializes the timeout method with the passed data.
     *
     * @param string $className  The class name to invoke the method on
     * @param string $methodName The method name to invoke on the class
     * @param array  $parameters The method parameters
     */
    public function __construct($className, $methodName, array $parameters = array())
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
    }

    /**
     * Returns the class name to invoke the method on.
     *
     * @return string The class name
     * @see \TechDivision\EnterpriseBeans\MethodInterface::getClassName()
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns the method name to invoke on the class.
     *
     * @return string The method name
     * @see \TechDivision\EnterpriseBeans\MethodInterface::getMethodName()
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the method parameters.
     *
     * @return mixed The parameter's value
     * @see \TechDivision\EnterpriseBeans\MethodInterface::getParameters()
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Serializes the timeout method and returns a string representation.
     *
     * @return string The serialized string representation of the instance
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * Restores the instance with the serialized data of the passed string.
     *
     * @param string $data The serialized method representation
     *
     * @return void
     * @see \Serializable::unserialize()
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }
}
