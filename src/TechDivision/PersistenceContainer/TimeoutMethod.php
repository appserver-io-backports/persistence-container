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
use TechDivision\PersistenceContainer\Utils\BeanUtils;

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
     * The method annotations.
     *
     * @var array
     */
    protected $annotations = array();

    /**
     * Initializes the timeout method with the passed data.
     *
     * @param string $className   The class name to invoke the method on
     * @param string $methodName  The method name to invoke on the class
     * @param array  $annotations The method annotations
     */
    public function __construct($className, $methodName, array $annotations = array())
    {
        $this->className = $className;
        $this->methodName = $methodName;
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
     * @return array The method parameters
     * @see \TechDivision\EnterpriseBeans\MethodInterface::getParameters()
     */
    public function getParameters()
    {
        return array();
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
     * Returns a reflection method representation of this instance.
     *
     * @return \ReflectionMethod The reflection method instance
     */
    public function toReflectionMethod()
    {
        return new \ReflectionMethod($this->getClassName(), $this->getMethodName());
    }

    /**
     * Creates a new timeout method instance from the passed reflection method.
     *
     * @param \ReflectionMethod $reflectionMethod The reflection method to load the data from
     *
     * @return \TechDivision\PersistenceContainer\TimeoutMethod The instance
     */
    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod)
    {

        // we need the bean utils
        $beanUtils = new BeanUtils();

        // load the method annotations
        $annotations = $beanUtils->getMethodAnnotations($reflectionMethod);

        // load class and method name from the reflection class
        $className = $reflectionMethod->getDeclaringClass()->getName();
        $methodName = $reflectionMethod->getName();

        // initialize and return the timeout method instance
        return new TimeoutMethod($className, $methodName, $annotations);
    }
}
