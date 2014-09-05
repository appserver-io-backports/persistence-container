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
     * Injects the application instance.
     *
     * @param \TechDivision\Application\Interfaces\ApplicationInterface $application The application instance
     *
     * @return void
     */
    public function injectApplication(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Injects the timed object instance.
     *
     * @param \TechDivision\EnterpriseBeans\TimedObjectInstance $timedObject The timed object instance
     *
     * @return void
     */
    public function injectTimedObject(TimedObjectInterface $timedObject)
    {
        $this->timedObject = $timedObject;
    }

    /**
     * Injects the storage for the timeout interceptors.
     *
     * @param \TechDivision\Storage\StorageInterface $timeoutInterceptors The storage for the timeout interceptors
     *
     * @return void
     */
    public function injectTimeoutInterceptors(StorageInterface $timeoutInterceptors)
    {
        $this->timeoutInterceptors = $timeoutInterceptors;
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
     * The application instance providing the database connection.
     *
     * @return \TechDivision\Application\Interfaces\ApplicationInterface The application instance
     */
    public function getApplication()
    {
        return $this->application;
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
     * Returns the timeout interceptors.
     *
     * @return \TechDivision\Storage\StorageInterface A collection of timeout interceptors
     **/
    public function getTimeoutInterceptors()
    {
        return $this->timeoutInterceptors;
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

        // create a reflection object instance of the timed object
        $reflectionObject = new \ReflectionObject($this->getTimedObject());

        // if we don't have a timeout method passed, try to load the default one
        if ($timeoutMethod == null && $this->timeoutInterceptors->count() > 0) {
            foreach ($this->timeoutInterceptors as $timeoutMethod) {
                break;
            }
        }

        // check if the timeout method is valid
        if ($timeoutMethod != null && $reflectionObject->hasMethod($timeoutMethod->getMethodName())) {

            // invoke the timeout method
            $reflectionMethod = $reflectionObject->getMethod($timeoutMethod->getMethodName());
            $reflectionMethod->invoke($this->getTimedObject(), $timer);
        }
    }

    /**
     * Initializes the timed object invoker with the methods annotated
     * with the @Timeout annotation.
     *
     * @return void
     */
    public function start()
    {

        // create a reflection object instance of the timed object
        $reflectionObject = new \ReflectionObject($this->getTimedObject());

        // check the methods of the bean for a @Timeout annotation
        foreach ($reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {

            // then check if the timed object instance has @Timeout annotation
            if ($this->getBeanUtils()->hasMethodAnnotation($reflectionMethod, BeanUtils::TIMEOUT)) {
                $this->timeoutInterceptors[] = new TimeoutMethod($reflectionMethod->getDeclaringClass(), $reflectionMethod->getName());
            }
        }
    }
}
