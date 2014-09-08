<?php
/**
 * TechDivision\PersistenceContainer\TimedObjectInvoker
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
 * @package   TechDivision_PersistenceContainerProtocol
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\Storage\StorageInterface;
use TechDivision\Storage\GenericStackable;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\EnterpriseBeans\MethodInterface;
use TechDivision\EnterpriseBeans\TimedObjectInterface;
use TechDivision\EnterpriseBeans\TimedObjectInvokerInterface;
use TechDivision\PersistenceContainer\Annotations\Timeout;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\Application\Interfaces\ApplicationInterface;

/**
 * Timed object invoker for an enterprise bean.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimedObjectInvoker extends GenericStackable implements TimedObjectInvokerInterface
{

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
     * Injects the timed object instance.
     *
     * @param object $timedObject The timed object instance
     *
     * @return void
     */
    public function injectTimedObject($timedObject)
    {
        $this->timedObject = $timedObject;
    }

    /**
     * Injects the storage for the timeout methods.
     *
     * @param \TechDivision\Storage\StorageInterface $timeoutMethods The storage for the timeout methods
     *
     * @return void
     */
    public function injectTimeoutMethods(StorageInterface $timeoutMethods)
    {
        $this->timeoutMethods = $timeoutMethods;
    }

    /**
     * Injects the application instance.
     *
     * @param \TechDivision\Application\ApplicationInterface $application The application instance
     *
     * @return void
     */
    public function injectApplication(ApplicationInterface $application)
    {
        $this->application = $application;
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
     * Return the timed object instance.
     *
     * @return \TechDivision\EnterpriseBeans\TimedObjectInterface The timed object instance
     */
    public function getTimedObject()
    {
        return $this->timedObject;
    }

    /**
     * Returns the timeout methods.
     *
     * @return \TechDivision\Storage\StorageInterface A collection of timeout methods
     **/
    public function getTimeoutMethods()
    {
        return $this->timeoutMethods;
    }

    /**
     * Returns the application instance.
     *
     * @return \TechDivision\Application\Interfaces\ApplicationInterface The application instance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * The globally unique identifier for this timed object invoker.
     *
     * @return string
     */
    public function getTimedObjectId()
    {
        return get_class($this->getTimedObject());
    }

    /**
     * Responsible for invoking the timeout method on the target object.
     *
     * The timerservice implementation invokes this method as a callback when a timeout occurs for the
     * passed timer. The timerservice implementation will be responsible for passing the correct
     * timeout method corresponding to the <code>timer</code> on which the timeout has occurred.
     *
     * @param \TechDivision\EnterpriseBean\TimerInterface  $timer         The timer that is passed to timeout
     * @param \TechDivision\EnterpriseBean\MethodInterface $timeoutMethod The timeout method
     *
     * @return void
     */
    public function callTimeout(TimerInterface $timer, MethodInterface $timeoutMethod = null)
    {

        // check if the timeout method is valid
        if ($timeoutMethod != null) {

            // invoke the timeout method
            $reflectionMethod = $timeoutMethod->toReflectionMethod();
            $reflectionMethod->invoke($this->getTimedObject(), $timer);
            return;
        }

        // register the class loader for the pre-loaded timeout methods
        $this->getApplication()->registerClassLoaders();

        // check if we've a default timeout method
        if ($this->defaultTimeoutMethod != null) {

            // invoke the default timeout method
            $reflectionMethod = $this->defaultTimeoutMethod->toReflectionMethod();
            $reflectionMethod->invoke($this->getTimedObject(), $timer);
            return;
        }
    }

    /**
     * Initializes the timed object invoker with the methods annotated
     * with the @Timeout or @Schedule annotation.
     *
     * @return void
     */
    public function start()
    {

        // create a reflection object instance of the timed object
        $reflectionObject = new \ReflectionObject($this->getTimedObject());

        // first check if the bean implements the timed object interface => so we've a default timeout method
        if ($reflectionObject->implementsInterface('TechDivision\EnterpriseBeans\TimedObjectInterface')) {
            $reflectionMethod = $reflectionObject->getMethod(TimedObjectInterface::DEFAULT_TIMEOUT_METHOD);
            $this->defaultTimeoutMethod = TimeoutMethod::fromReflectionMethod($reflectionMethod);
        }

        // check the methods of the bean for a @Timeout annotation => overwrite the default
        // timeout method defined by the interface
        foreach ($reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {

            // initialize the timeout method instance
            $timeoutMethod = TimeoutMethod::fromReflectionMethod($reflectionMethod);

            // check if the timed object instance has @Timeout annotation => default timeout method
            if ($timeoutMethod->hasAnnotation(Timeout::ANNOTATION)) {
                $this->defaultTimeoutMethod = $timeoutMethod;
            }

            // check if the timed object instance has @Schedule annotation
            if ($timeoutMethod->hasAnnotation(Schedule::ANNOTATION)) {
                $this->timeoutMethods[$timeoutMethod->getMethodName()] = $timeoutMethod;
            }
        }
    }
}
