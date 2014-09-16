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

use TechDivision\Naming\InitialContext;
use TechDivision\Storage\StorageInterface;
use TechDivision\Storage\GenericStackable;
use TechDivision\Lang\Reflection\ClassInterface;
use TechDivision\Lang\Reflection\MethodInterface;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\EnterpriseBeans\TimedObjectInterface;
use TechDivision\EnterpriseBeans\TimedObjectInvokerInterface;
use TechDivision\PersistenceContainer\Annotations\Timeout;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\PersistenceContainerProtocol\BeanContext;
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
     * Injects the timed object instance.
     *
     * @param \TechDivision\Lang\Reflection\ClassInterface $timedObject The timed object instance
     *
     * @return void
     */
    public function injectTimedObject(ClassInterface $timedObject)
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
     * Return the timed object instance.
     *
     * @return \TechDivision\Lang\Reflection\ClassInterface The timed object instance
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
        return $this->getTimedObject()->getShortName();
    }

    /**
     * Responsible for invoking the timeout method on the target object.
     *
     * The timerservice implementation invokes this method as a callback when a timeout occurs for the
     * passed timer. The timerservice implementation will be responsible for passing the correct
     * timeout method corresponding to the <code>timer</code> on which the timeout has occurred.
     *
     * @param \TechDivision\EnterpriseBean\TimerInterface   $timer         The timer that is passed to timeout
     * @param \TechDivision\Lang\Reflection\MethodInterface $timeoutMethod The timeout method
     *
     * @return void
     */
    public function callTimeout(TimerInterface $timer, MethodInterface $timeoutMethod = null)
    {

        // initialize the application instance
        $application = $this->getApplication();

        // register the class loader for the pre-loaded timeout methods
        $application->registerClassLoaders();

        // initialize the initial context instance
        $initialContext = new InitialContext();
        $initialContext->injectApplication($application);

        // lookup the enterprise bean using the initial context
        $instance = $initialContext->lookup($this->getTimedObjectId());

        // check if the timeout method has been passed
        if ($timeoutMethod != null) { // if yes, invoke it on the proxy
            $callback = array($instance, $timeoutMethod->getMethodName());
            call_user_func_array($callback, array($timer));
            return;
        }

        // check if we've a default timeout method
        if ($this->defaultTimeoutMethod != null) { // if yes, invoke it on the proxy
            $callback = array($instance, $this->defaultTimeoutMethod->getMethodName());
            call_user_func_array($callback, array($timer));
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
        $reflectionClass = $this->getTimedObject();

        // first check if the bean implements the timed object interface => so we've a default timeout method
        if ($reflectionClass->implementsInterface('TechDivision\EnterpriseBeans\TimedObjectInterface')) {
            $this->defaultTimeoutMethod = $reflectionClass->getMethod(TimedObjectInterface::DEFAULT_TIMEOUT_METHOD);
        }

        // check the methods of the bean for a @Timeout annotation => overwrite the default
        // timeout method defined by the interface
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $timeoutMethod) {

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
