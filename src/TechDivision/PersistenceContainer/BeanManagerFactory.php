<?php

/**
 * TechDivision\PersistenceContainer\BeanManagerFactory
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
 * @author    Bernhard Wick <b.wick@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\Storage\StackableStorage;
use TechDivision\ApplicationServer\AbstractManagerFactory;

/**
 * The bean manager handles the message and session beans registered for the application.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @author    Bernhard Wick <b.wick@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
class BeanManagerFactory extends AbstractManagerFactory
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
                $managerConfiguration = $self->managerConfiguration;

                // register the default class loader
                $initialContext->getClassLoader()->register(true, true);

                // initialize the bean locator
                $beanLocator = new BeanLocator();

                // initialize the stackable for the data, the stateful + singleton session beans and the naming directory
                $data = new StackableStorage();
                $namingDirectory = new StackableStorage();
                $statefulSessionBeans = new StackableStorage();
                $singletonSessionBeans = new StackableStorage();

                // initialize the default settings for the stateful session beans
                $statefulSessionBeanSettings = new DefaultStatefulSessionBeanSettings();
                $statefulSessionBeanSettings->mergeWithParams($managerConfiguration->getParamsAsArray());

                // initialize the bean manager
                $beanManager = new BeanManager();
                $beanManager->injectData($data);
                $beanManager->injectResourceLocator($beanLocator);
                $beanManager->injectNamingDirectory($namingDirectory);
                $beanManager->injectWebappPath($application->getWebappPath());
                $beanManager->injectSingletonSessionBeans($singletonSessionBeans);
                $beanManager->injectStatefulSessionBeans($statefulSessionBeans);
                $beanManager->injectStatefulSessionBeanSettings($statefulSessionBeanSettings);

                // attach the instance
                $instances[] = $beanManager;

                // wait for the next instance to be created
                $self->wait();

            }, $this);
        }
    }
}
