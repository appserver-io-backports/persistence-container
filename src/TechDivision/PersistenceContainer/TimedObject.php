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

use TechDivision\Lang\Reflection\ReflectionClass;
use TechDivision\PersistenceContainer\Annotations\MessageDriven;
use TechDivision\PersistenceContainer\Annotations\PostConstruct;
use TechDivision\PersistenceContainer\Annotations\PreDestroy;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\PersistenceContainer\Annotations\Singleton;
use TechDivision\PersistenceContainer\Annotations\Startup;
use TechDivision\PersistenceContainer\Annotations\Stateful;
use TechDivision\PersistenceContainer\Annotations\Stateless;
use TechDivision\PersistenceContainer\Annotations\Timeout;

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
class TimedObject extends ReflectionClass
{

    /**
     * Creates a new reflection class instance from the passed PHP reflection class.
     *
     * @param \ReflectionClass $reflectionClass     The PHP reflection class to load the data from
     * @param array            $annotationsToIgnore An array with annotations names we want to ignore when loaded
     * @param array            $annotationAliases   An array with annotation aliases used when create annotation instances
     *
     * @return \TechDivision\Lang\Reflection\ReflectionClass The instance
     */
    public static function fromPhpReflectionClass(\ReflectionClass $reflectionClass, array $annotationsToIgnore = array(), array $annotationAliases = array())
    {

        // initialize the array with the annotations we want to ignore
        $annotationsToIgnore = array_merge(
            $annotationsToIgnore,
            array(
                'author',
                'package',
                'license',
                'copyright',
                'param',
                'return',
                'throws',
                'see',
                'link'
            )
        );

        // initialize the array with the aliases for the enterprise bean annotations
        $annotationAliases = array_merge(
            array(
                MessageDriven::ANNOTATION => MessageDriven::__getClass(),
                PostConstruct::ANNOTATION => PostConstruct::__getClass(),
                PreDestroy::ANNOTATION    => PreDestroy::__getClass(),
                Schedule::ANNOTATION      => Schedule::__getClass(),
                Singleton::ANNOTATION     => Singleton::__getClass(),
                Startup::ANNOTATION       => Startup::__getClass(),
                Stateful::ANNOTATION      => Stateful::__getClass(),
                Stateless::ANNOTATION     => Stateless::__getClass(),
                Timeout::ANNOTATION       => Timeout::__getClass()
            )
        );

        // create a new timed object instance
        return new TimedObject($reflectionClass->getName(), $annotationsToIgnore, $annotationAliases);
    }
}
