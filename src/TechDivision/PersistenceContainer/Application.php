<?php

/**
 * TechDivision\PersistenceContainer\Application
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
    
namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\AbstractApplication;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * The application instance holds all information about the deployed application
 * and provides a reference to the entity manager and the initial context.
 *
 * @package     TechDivision\PersistenceContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Application extends AbstractApplication
{

    /**
     * The data source name to use.
     * @var string
     */
    protected $dataSourceName;
    
    /**
     * The path to the doctrine entities.
     * @var string
     */
    protected $pathToEntities;
    
    /**
     * The doctrine entity manager.
     * @var \Doctrine\Common\Persistence\ObjectManager 
     */
    protected $entityManager;

    /**
     * The database configuration.
     * @var \TechDivision\ApplicationServer\Configuration
     */
    protected $databaseConfiguration;
    
    /**
     * Array with the connection parameters.
     * @var array
     */
    protected $connectionParameters;

    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     * 
     * @return \TechDivision\PersistenceContainer\Application The connected application
     */
    public function connect() {
        
        // initialize the class loader with the additional folders
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath());
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath() . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'classes');
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath() . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'lib');
        
        // load the database configuration
        $configuration = $this->getDatabaseConfiguration();

        // initialize the application instance
        $this->setDataSourceName($configuration->getChild('/datasource/name')->getValue());
        $this->setPathToEntities($this->getWebappPath() . DIRECTORY_SEPARATOR . $configuration->getChild('/datasource/pathToEntities')->getValue());

        // load the database connection information
        foreach ($configuration->getChilds('/datasource/database') as $database) {

            // initialize the connection parameters
            $connectionParameters = array(
                'driver' => $database->getChild('/database/driver')->getValue(),
                'user' => $database->getChild('/database/user')->getValue(),
                'password' => $database->getChild('/database/password')->getValue()
            );

            // initialize database driver specific connection parameters
            if (($databaseName = $database->getChild('/database/databaseName')) != null) {
                $connectionParameters['dbname'] = $databaseName->getValue();
            }
            if (($path = $database->getChild('/database/path')) != null) {
                $connectionParameters['path'] = $this->getWebappPath() . DIRECTORY_SEPARATOR . $path->getValue();
            }
            if (($memory = $database->getChild('/database/memory')) != null) {
                $connectionParameters['memory'] = $memory->getValue();
            }

            // set the connection parameters
            $this->setConnectionParameters($connectionParameters);
        }

        // return the instance itself
        return $this;
    }

    /**
     * Set's the database configuration.
     *
     * @param \TechDivision\ApplicationServer\Configuration $databaseConfiguration The database configuration
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setDatabaseConfiguration($databaseConfiguration) {
        $this->databaseConfiguration = $databaseConfiguration;
        return $this;
    }

    /**
     * Returns the database configuration.
     *
     * @return \TechDivision\ApplicationServer\Configuration The database configuration
     */
    public function getDatabaseConfiguration() {
        return $this->databaseConfiguration;
    }

    /**
     * Sets the data source name.
     *
     * @param string $dataSourceName The data source name
     * @return string
     */
    public function setDataSourceName($dataSourceName) {
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * Returns the data source name.
     *
     * @return string The data source name
     */
    public function getDataSourceName() {
        return $this->dataSourceName;
    }
    
    /**
     * Set's the path to the doctrine entities.
     * 
     * @param string $pathToEntities The path to the doctrine entities
     * @return \TechDivision\PersistenceContainer\Application The application instance
     */
    public function setPathToEntities($pathToEntities) {
        $this->pathToEntities = $pathToEntities;
        return $this;
    }
    
    /**
     * Return's the path to the doctrine entities.
     * 
     * @return string The path to the doctrine entities
     */
    public function getPathToEntities() {
        return $this->pathToEntities;
    }
    
    /**
     * Set's the database connection parameters.
     * 
     * @param array $connectionParameters The database connection parameters
     * @return \TechDivision\PersistenceContainer\Application The application instance
     */
    public function setConnectionParameters(array $connectionParameters) {
        $this->connectionParameters = $connectionParameters;
        return $this;
    }
    
    /**
     * Return's the database connection parameters.
     * 
     * @return array The database connection parameters
     */
    public function getConnectionParameters() {
        return $this->connectionParameters;
    }
    
    /**
     * Sets the applications entity manager instance.
     * 
     * @param \Doctrine\Common\Persistence\ObjectManager $entityManager The entity manager instance
     * @return \TechDivision\PersistenceContainer\Application The application instance
     */
    public function setEntityManager(ObjectManager $entityManager) {
        $this->entityManager = $entityManager;
        return $this;
    }
    
    /**
     * Return the entity manager instance.
     * 
     * @return \Doctrine\Common\Persistence\ObjectManager The entity manager instance
     */
    public function getEntityManager() {

        // initialize path to entities
        $pathToEntities = array($this->getPathToEntities());

        // load the doctrine metadata information
        $metadataConfiguration = Setup::createAnnotationMetadataConfiguration($pathToEntities, true);

        // load the connection parameters
        $connectionParameters = $this->getConnectionParameters();

        // initialize the entity manager
        return EntityManager::create($connectionParameters, $metadataConfiguration);
    }

    /**
     * @param type $className
     * @param type $sessionId
     * @internal param \TechDivision\PersistenceContainer\type $args
     * @return type
     */
    public function lookup($className, $sessionId) {
        return $this->initialContext->lookup($className, $sessionId, array($this));
    }
}