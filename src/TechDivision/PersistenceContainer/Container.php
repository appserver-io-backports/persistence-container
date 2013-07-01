<?php

/**
 * TechDivision\PersistenceContainer\Container
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\AbstractContainer;
use TechDivision\ApplicationServer\Configuration;
use TechDivision\PersistenceContainer\Exceptions\InvalidApplicationArchiveException;

/**
 * @package     TechDivision\PersistenceContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 * @author      Johann Zelger <jz@techdivision.com>
 */
class Container extends AbstractContainer {

    /**
     * XPath expression for the application configurations.
     * @var string
     */
    const XPATH_APPLICATIONS = '/datasources/datasource';

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\Server The server instance
     * @todo Implement real deployment here
     */
    public function deploy() {

        // gather all the deployed web applications
        foreach (new \FilesystemIterator(getcwd() . '/webapps') as $folder) {

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
                
                $configuration = Configuration::loadFromFile($ds);
                
                foreach ($configuration->getChilds('/datasources/datasource') as $datasource) {
                
					// initialize the application instance
					$application = $this->newInstance($datasource->getType(), array($name));
					$application->setWebappPath($folder->getPathname());
					$application->init($datasource);
					
                	$this->applications[$application->getDataSourceName()] = $application;
                
                }
            }
        }

        // return the server instance
        return $this;
    }
}