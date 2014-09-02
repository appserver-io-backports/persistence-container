<?php

/**
 * TechDivision\PersistenceContainer\TimerWorker
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

use TechDivision\Storage\StackableStorage;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\EnterpriseBeans\EnterpriseBeansException;
use TechDivision\EnterpriseBeans\NoSuchObjectLocalException;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\Application\Interfaces\ApplicationInterface;
use TechDivision\PersistenceContainerProtocol\RemoteMethodCall;

/**
 * The garbage collector for the stateful session beans.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerWorker extends \Thread
{

    /**
     * The application instance the worker is working for.
     *
     * @var \TechDivision\ApplicationServer\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * The timer we have to handle.
     *
     * @var \TechDivision\EnterpriseBeans\TimerInterface
     */
    protected $timer;

    /**
     * Initializes the queue worker with the application and the storage it should work on.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ApplicationInterface $application The application instance with the queue manager/locator
     * @param \TechDivision\EnterpriseBeans\TimerInterface                    $timer       The timer we have to handle
     */
    public function __construct($application, $timer)
    {

        // bind the gc to the application
        $this->application = $application;
        $this->timer = $timer;

        // start the worker
        $this->start();
    }

    /**
     * We process the timer here.
     *
     * @return void
     */
    public function run()
    {

        // create a local instance of appication
        $application = $this->application;

        // register the class loader again, because each thread has its own context
        $application->registerClassLoaders();

        // we need the timer and the bean manager
        $timer = $this->timer;
        $beanManager = $application->getManager(BeanContext::IDENTIFIER);

        // initialize the flag that keeps the worker running
        $handleTimer = true;

        do {

            try {

                // initialize the timestamp with the actual time
                $actualTime = new \DateTime();

                // check if we've to invoke the timer instance
                if ($actualTime > $timer->getNextTimeout()) {

                    // load the remote method that we want to invoke
                    $localMethod = $timer->getInfo();

                    // lock the container and lookup the bean instance
                    $instance = $beanManager->locate($localMethod, array($application));

                    // prepare method name and parameters and invoke method
                    $methodName = $localMethod->getMethodName();
                    $parameters = array($timer);

                    // invoke the remote method call on the local instance
                    call_user_func_array(array($instance, $methodName), $parameters);

                    // re-attach the bean instance to the container
                    $beanManager->attach($instance);
                }

                // wait for the next timer invokation
                $this->wait($timer->getTimeRemaining());

            } catch (NoSuchObjectLocalException $nsole) { // timer has been finished

                // TODO: Invoke method annotated with @Timeout here

                $handleTimer = false;
                $application->getInitialContext()->getSystemLogger()->info(sprintf('Timer successfully finished'));

            } catch (\Exception $e) { // an error occured
                $handleTimer = false;
                $application->getInitialContext()->getSystemLogger()->error($e->__toString());
            }

        } while ($handleTimer); // check if the timer has been finished
    }
}
