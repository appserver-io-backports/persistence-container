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

use TechDivision\ApplicationServer\AbstractDeployment;
use TechDivision\ApplicationServer\Configuration;

/**
 * @package     TechDivision\PersistenceContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Deployment extends AbstractDeployment {

    /**
     * XPath expression for the application configurations.
     * @var string
     */
    const XPATH_DATASOURCES_DATASOURCE = '/datasources/datasource';

    /**
     * XPath expression for the datasource name.
     * @var string
     */
    const XPATH_DATASOURCE_NAME = '/datasource/name';

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\Server The server instance
     */
    public function deploy() {

        // the container configuration
        $containerThread = $this->getContainerThread();
        $configuration = $containerThread->getConfiguration();

        // load the host configuration for the path to the web application folder
        $baseDirectory = $configuration->getChild(self::XPATH_CONTAINER_BASE_DIRECTORY)->getValue();
        $appBase = $configuration->getChild(self::XPATH_CONTAINER_HOST)->getAppBase();

        // gather all the deployed web applications
        foreach (new \FilesystemIterator($baseDirectory . $appBase) as $folder) {

            // check if file or subdirectory has been found
            if (is_dir($folder . DIRECTORY_SEPARATOR . 'META-INF')) {
                
                // initialize the application name
                $name = basename($folder);

                // it's no valid application without at least the appserver-ds.xml file
                if (!file_exists($ds = $folder . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'appserver-ds.xml')) {
                    throw new InvalidApplicationArchiveException(sprintf('Folder %s contains no valid webapp.', $folder));
                }

                // load and initialize the database configuration
                $databaseConfiguration = $this->newInstance('TechDivision\ApplicationServer\Configuration');
                $databaseConfiguration->initFromFile($ds);
                foreach ($databaseConfiguration->getChilds(self::XPATH_DATASOURCES_DATASOURCE) as $datasource) {

                    // initialize the application instance
                    $application = $this->newInstance($datasource->getType(), array($this->initialContext, $name));
                    $application->setConfiguration($configuration);
                    $application->setDatabaseConfiguration($datasource);

                    // set the datasource name
                    foreach ($datasource->getChilds(self::XPATH_DATASOURCE_NAME) as $name) {
                        $this->applications[$name->getValue()] = $application->connect();
                    }
                }
            }
        }

        // return the server instance
        return $this;
    }
}