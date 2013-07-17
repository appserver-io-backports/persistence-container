<?php

/**
 * TechDivision\PersistenceContainer\Deployment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\Configuration;
use TechDivision\ApplicationServer\InitialContext;

/**
 * @package     TechDivision\PersistenceContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Deployment {

    /**
     * Path to the container's host configuration.
     * @var string
     */
    const CONTAINER_HOST = '/container/host';

    /**
     * XPath expression for the application configurations.
     * @var string
     */
    const XPATH_APPLICATIONS = '/datasources/datasource';

    protected $containerThread;

    protected $applications;

    public function __construct($containerThread) {
        $this->containerThread = $containerThread;
    }

    public function getContainerThread() {
        return $this->containerThread;
    }

    /**
     * Returns the deployed applications.
     *
     * @return array The deployed applications
     */
    public function getApplications() {
        return $this->applications;
    }

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\Server The server instance
     * @todo Implement real deployment here
     */
    public function deploy() {

        // the container configuration
        $containerThread = $this->getContainerThread();
        $configuration = $containerThread->getConfiguration();

        // load the host configuration for the path to the webapps folder
        $host = $configuration->getChild(self::CONTAINER_HOST);

        // gather all the deployed web applications
        foreach (new \FilesystemIterator($host->getAppBase()) as $folder) {

            // check if file or subdirectory has been found
            if (is_dir($folder . DS . 'META-INF')) {

                // add the servlet-specific include path
                set_include_path($folder . PS . get_include_path());

                // set the additional servlet include paths
                set_include_path($folder . DS . 'META-INF' . DS . 'classes' . PS . get_include_path());
                set_include_path($folder . DS . 'META-INF' . DS . 'lib' . PS . get_include_path());

                // initialize the application name
                $name = basename($folder);

                // it's no valid application without at least the appserver-ds.xml file
                if (!file_exists($ds = $folder . DS . 'META-INF' . DS . 'appserver-ds.xml')) {
                    throw new InvalidApplicationArchiveException(sprintf('Folder %s contains no valid webapp.', $folder));
                }

                $databaseConfiguration = Configuration::loadFromFile($ds);

                foreach ($databaseConfiguration->getChilds('/datasources/datasource') as $datasource) {

                    // initialize the application instance
                    $application = $this->newInstance($datasource->getType(), array($name));
                    $application->setConfiguration($configuration);
                    $application->setDatabaseConfiguration($datasource);

                    error_log($datasource->getChilds('/name'));

                    $this->applications[$datasource->getDataSourceName()] = $application->connect();

                }
            }
        }

        // return the server instance
        return $this;
    }

    /**
     * Creates a new instance of the passed class name and passes the
     * args to the instance constructor.
     *
     * @param string $className The class name to create the instance of
     * @param array $args The parameters to pass to the constructor
     * @return object The created instance
     */
    public function newInstance($className, array $args = array()) {
        return InitialContext::get()->newInstance($className, $args);
    }
}