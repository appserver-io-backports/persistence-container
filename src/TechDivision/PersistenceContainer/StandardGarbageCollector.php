<?php

/**
 * TechDivision\PersistenceContainer\StandardGarbageCollector
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
class StandardGarbageCollector extends \Thread
{

    /**
     * The application instance the worker is working for.
     *
     * @var \TechDivision\ApplicationServer\Interfaces\ApplicationInterface
     */
    protected $application;

    /**
     * Initializes the queue worker with the application and the storage it should work on.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ApplicationInterface $application The application instance with the queue manager/locator
     */
    public function __construct(ApplicationInterface $application)
    {

        // bind the gc to the application
        $this->application = $application;

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

        // create a local instance of appication and storage
        $application = $this->application;

        // register the class loader again, because each thread has its own context
        $application->registerClassLoaders();

        while (true) {

            $this->synchronized(function () { // wait one second
                $this->wait(1000000);
            });

            // we need the bean manager that handles all the beans
            $beanManager = $application->getManager(BeanContext::IDENTIFIER);

            // iterate over the applications sessions with stateful session beans
            foreach ($beanManager->getStatefulSessionBeans() as $sessionId => $sessions) {

                if ($sessions instanceof StatefulSessionBeanMap) { // if we've a map with stateful session beans

                    // initialize the timestamp with the actual time
                    $actualTime = time();

                    // check the lifetime of the stateful session beans
                    foreach ($sessions->getLifetime() as $className => $lifetime) {

                        if ($lifetime < $actualTime) { // if the stateful session bean has timed out, remove it
                            $beanManager->removeStatefulSessionBean($sessionId, $className);
                        }
                    }
                }
            }
        }
    }
}
