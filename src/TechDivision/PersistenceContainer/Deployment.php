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
 *
 * @package TechDivision\PersistenceContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class Deployment extends AbstractDeployment
{

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\DeploymentInterface The deployment instance
     */
    public function deploy()
    {

        // gather all the deployed web applications
        foreach (new \FilesystemIterator($this->getBaseDirectory($this->getAppBase())) as $folder) {

            // check if file or sub directory has been found
            if (is_dir($folder)) {

                // initialize the application name
                $name = basename($folder);

                // initialize the application instance
                $application = $this->newInstance('\TechDivision\PersistenceContainer\Application', array(
                    $this->getInitialContext(),
                    $this->getContainerNode(),
                    $name
                ));

                // add the application and deploy the datasource if available
                $this->addApplication($application);
                $this->deployDatasource($application->getAppNode(), $folder);
            }
        }

        // return the server instance
        return $this;
    }

    /**
     * Deploys the datasource found for the passed app node in the app's webapp folder.
     *
     * @param \TechDivision\ApplicationServer\Api\Node\AppNode $appNode
     * @param string $folder
     *            Folder to check for datasources
     * @return void
     */
    public function deployDatasource($appNode, $folder)
    {
        if (is_dir($folder . DIRECTORY_SEPARATOR . 'META-INF')) {
            if (file_exists($ds = $folder . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'appserver-ds.xml')) {
                $datasourceService = $this->newService('TechDivision\ApplicationServer\Api\DatasourceService');
                foreach ($datasourceService->initFromFile($ds) as $datasourceNode) {
                    $datasourceService->attachDatasource($datasourceNode);
                }
            }
        }
    }
}