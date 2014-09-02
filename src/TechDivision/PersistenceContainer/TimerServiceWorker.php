<?php

/**
 * TechDivision\PersistenceContainer\TimerServiceWorker
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

use TechDivision\Storage\GenericStackable;
use TechDivision\EnterpriseBeans\TimerInterface;
use TechDivision\PersistenceContainerProtocol\BeanContext;
use TechDivision\Application\Interfaces\ApplicationInterface;

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
class TimerServiceWorker extends \Thread
{

    /**
     * The application instance the worker is working for.
     *
     * @var \TechDivision\ApplicationServer\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * Contains the registered timers.
     *
     * @var \TechDivision\Storage\GenericStackable
     */
    protected $timersRegistered;

    /**
     * Initializes the queue worker with the application and the storage it should work on.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ApplicationInterface $application The application instance with the queue manager/locator
     */
    public function __construct(ApplicationInterface $application)
    {

        // bind the gc to the application
        $this->application = $application;

        // a collection with the
        $this->timersRegistered = new GenericStackable();

        // start the worker
        $this->start();
    }

    /**
     * We process the messages here.
     *
     * @return void
     */
    public function run()
    {

        // create a local instance of appication and the registered timers
        $application = $this->application;
        $timersRegistered = $this->timersRegistered;

        // register the class loader again, because each thread has its own context
        $application->registerClassLoaders();

        // we need the bean manager and the timer service to handle the timers
        if ($timerService = $application->getManager(TimerServiceContext::IDENTIFIER)) {

            while (true) { // check for new timers

                // iterate over the timers and start a worker for each of them
                foreach ($timerService->getAllTimers() as $uuid => $timer) {
                    if (array_key_exists($uuid, $timersRegistered) === false && $timer instanceof TimerInterface) {
                        $this->registeredTimers[$uuid] = new TimerWorker($application, $timer);
                    }
                }

                // wait one second
                $this->wait(1000000);
            }
        }
    }
}
