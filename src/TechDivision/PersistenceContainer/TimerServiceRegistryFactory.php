<?php

/**
 * TechDivision\PersistenceContainer\TimerServiceRegistryFactory
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
use TechDivision\ApplicationServer\AbstractManagerFactory;

/**
 * A factory for the timer service registry instances.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class TimerServiceRegistryFactory extends AbstractManagerFactory
{

    /**
     * The main method that creates new instances in a separate context.
     *
     * @return void
     */
    public function run()
    {

        while (true) { // we never stop

            $this->synchronized(function ($self) {

                // make instances local available
                $instances = $self->instances;
                $application = $self->application;
                $initialContext = $self->initialContext;

                // register the default class loader
                $initialContext->getClassLoader()->register(true, true);

                // initialize the service locator
                $serviceLocator = new ServiceLocator();

                // initialize the stackable for the data and the services
                $data = new StackableStorage();
                $services = new StackableStorage();

                // initialize the service registry
                $serviceRegistry = new TimerServiceRegistry();
                $serviceRegistry->injectData($data);
                $serviceRegistry->injectServices($services);
                $serviceRegistry->injectServiceLocator($serviceLocator);
                $serviceRegistry->injectWebappPath($application->getWebappPath());

                // attach the instance
                $instances[] = $serviceRegistry;

                // wait for the next instance to be created
                $self->wait();

            }, $this);
        }
    }
}
