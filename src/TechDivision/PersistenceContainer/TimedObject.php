<?php
/**
 * TechDivision\PersistenceContainer\TimedObject
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

use TechDivision\Lang\Reflection\ClassInterface;
use TechDivision\PersistenceContainer\Utils\BeanUtils;

/**
 * A wrapper instance for a reflection class.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimedObject implements ClassInterface
{

    /**
     * The class name to invoke the method on.
     *
     * @var string
     */
    protected $className = '';

    /**
     * The method annotations.
     *
     * @var array
     */
    protected $annotations = array();

    /**
     * Initializes the timed object with the passed data.
     *
     * @param string $className   The class name to invoke the method on
     * @param array  $annotations The method annotations
     */
    public function __construct($className, array $annotations = array())
    {
        $this->className = $className;
        $this->annotations = $annotations;
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
     * Returns the method annotations.
     *
     * @return array The method annotations
     */
    public function getAnnotations()
    {
        return $this->annotations;
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

    /**
     * Queries whether the timeout method has an annotation with the passed name or not.
     *
     * @param string $annotationName The annotation we want to query
     *
     * @return boolean TRUE if the timeout method has the annotation, else FALSE
     */
    public function hasAnnotation($annotationName)
    {
        return isset($this->annotations[$annotationName]);
    }

    /**
     * Returns the annotation instance with the passed name.
     *
     * @param string $annotationName The name of the requested annotation instance
     *
     * @return \TechDivision\PersistenceContainer\Annotations\AnnotationInterface|null The requested annotation instance
     * @see \TechDivision\PersistenceContainer\TimeoutMethod::hasAnnotation()
     */
    public function getAnnotation($annotationName)
    {
        if ($this->hasAnnotation($annotationName)) {
            return $this->annotations[$annotationName];
        }
    }

    /**
     * Returns a reflection class representation of this instance.
     *
     * @return \ReflectionClass The reflection class instance
     */
    public function toReflectionClass()
    {
        return new \ReflectionClass($this->getClassName());
    }

    /**
     * Creates a new timed object instance from the passed reflection class.
     *
     * @param \ReflectionClass $reflectionClass The reflection class to load the data from
     *
     * @return \TechDivision\PersistenceContainer\TimedObject The instance
     */
    public static function fromReflectionClass(\ReflectionClass $reflectionClass)
    {

        // we need the bean utils
        $beanUtils = new BeanUtils();

        // load the bean annotations
        $annotations = $beanUtils->getBeanAnnotations($reflectionClass);

        // load class name from the reflection class
        $className = $reflectionClass->getName();

        // initialize and return the timed object instance
        return new TimedObject($className, $annotations);
    }
}
