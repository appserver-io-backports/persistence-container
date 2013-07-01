<?php

/**
 * TechDivision\PersistenceContainer\RequestHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\SplClassLoader;

/**
 * @package     TechDivision\PersistenceContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class RequestHandler extends \Worker {

    /**
     * A reference to the container instance.
     * 
     * @var \TechDivision\PersistenceContainer
     */
    protected $container;
    
    /**
     * Array with the available applications.
     * @var array
     */
    protected $applications;

    /**
     * Passes a reference to the container instance.
     * 
     * @param \TechDivision\PersistenceContainer\Container $container The container instance
     * @return void
     */
    public function __construct($container) {
        $this->container = $container;
    }
    
    /**
     * Returns the container instance.
     * 
     * @return \TechDivision\PersistenceContainer\Container The container instance
     */
    public function getContainer() {
        return $this->container;
    }
    
    /**
     * Returns the array with the available applications.
     * 
     * @return array The available applications
     */
    public function getApplications() {
        return $this->applications;
    }
    
    /**
     * Tries to find and return the application for the passed class name.
     * 
     * @param string $className The name of the class to find and return the application instance
     * @return \TechDivision\PersistenceContainer\Application The application instance
     * @throws \Exception Is thrown if no application can be found for the passed class name
     */
    public function findApplication($className) {
        
        // iterate over all classes and check if the application name contains the class name
        foreach ($this->getApplications() as $name => $application) {
            if (strpos($className, $name) !== false) {
                // if yes, return the application instance
                return $application;
            }
        }
        
        // if not throw an exception
        throw new \Exception("Can\'t find application for '$className'");
    }
    
    /**
     * @see \Worker::run()
     */
    public function run() {
        
        // register class loader again, because we are in a thread
        $classLoader = new SplClassLoader();
        $classLoader->register();
        
        // initialize the array for the applications
        $applications = array();
        
        // load the available applications from the container
        foreach ($this->getContainer()->getApplications() as $name => $application) {
            // set the applications and connect the entity manager
            $applications[$name] = $application->connect();
        }
        
        // set the applications in the worker instance
        $this->applications = $applications;
    }
}