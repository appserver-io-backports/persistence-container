<?php

/**
 * TechDivision\PersistenceContainer\Deployment
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\AbstractDeployment;

/**
 * Class Deployment
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
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
        foreach (new \FilesystemIterator($this->getWebappPath()) as $folder) {
    
            // check if file or subdirectory has been found
            if ($folder->isDir() === true) {

                // initialize the application name
                $name = basename($folder);

                // Lets get the datasources found during deployment
                $datasources = $this->collectDatasources($this->getContainerNode()->getName(), $folder);

                // initialize the application instance
                $application = $this->newInstance(
                    '\TechDivision\PersistenceContainer\Application',
                    array(
                        $this->getInitialContext(),
                        $this->getContainerNode(),
                        $name,
                        $datasources
                    )
                );

                // add the application and deploy the datasource if available
                $this->addApplication($application);
                $this->deployDatasources($datasources);
            }
        }
    
        // return initialized applications
        return $this;
    }

    /**
     * Deploys the passed datasources.
     *
     * @param array $datasources The datasources to deploy
     *
     * @return void
     */
    public function deployDatasources(array $datasources)
    {
        // we need a datasource service to attach the sources
        $datasourceService = $this->newService('\TechDivision\ApplicationServer\Api\DatasourceService');

        // now attach them to our system configuration
        foreach ($datasources as $datasourceNode) {
            $datasourceService->attachDatasource($datasourceNode);
        }
    }

    /**
     * Collects all the datasources found within the specified folder and links them to the container.
     *
     * @param string       $containerName The name of the container the datasource is used in
     * @param \SplFileInfo $folder        Folder to check for datasources
     *
     * @return array
     */
    public function collectDatasources($containerName, $folder)
    {
        // if we wont find anything the return an empty array
        $datasources = array();

        if (is_dir($folder . DIRECTORY_SEPARATOR . 'META-INF')) {
            if (file_exists($ds = $folder . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'appserver-ds.xml')) {
                
                // lets instantiate all the datasources we can find and collect them
                $datasourceService = $this->newService('TechDivision\ApplicationServer\Api\DatasourceService');

                foreach ($datasourceService->initFromFile($ds, $containerName) as $datasourceNode) {
                    $datasources[$datasourceNode->getUuid()] = $datasourceNode;
                }
            }
        }

        return $datasources;
    }
    
    /**
     * Returns the authentication manager.
     *
     * @return \TechDivision\ServletEngine\Authentication\AuthenticationManager
     */
    protected function getAuthenticationManager()
    {
        return new StandardAuthenticationManager();
    }
    
    /**
     * (non-PHPdoc)
     *
     * @return string The path to the webapps folder
     * @see ApplicationService::getWebappPath()
     */
    public function getWebappPath()
    {
        return $this->getBaseDirectory($this->getAppBase());
    }
}
