<?php

namespace TechDivision\PersistenceContainer\Application;

/**
 * TechDivision_PersistenceContainer_Application_FLOW3
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

use TechDivision\PersistenceContainer\Application;

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
class FLOW3 extends Application {
    
    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     * 
     * @return \TechDivision\PersistenceContainer\Application The connected application
     */
    public function connect() {
        return $this;
    }
    
    public function lookup($className, $sessionId, array $args = array()) {
        
        // $_SERVER['FLOW3_ROOTPATH'] = trim(getenv('FLOW3_ROOTPATH'), '"\' ') ?: '';
        $_SERVER['FLOW3_ROOTPATH'] = '/Library/WebServer/Documents/ApplicationServer/app/code/lib/FLOW3/';

        $context = trim(getenv('FLOW3_CONTEXT'), '"\' ') ?: 'Development';
        
        $rootPath = isset($_SERVER['FLOW3_ROOTPATH']) ? $_SERVER['FLOW3_ROOTPATH'] : FALSE;
        if ($rootPath === FALSE && isset($_SERVER['REDIRECT_FLOW3_ROOTPATH'])) {
                $rootPath = $_SERVER['REDIRECT_FLOW3_ROOTPATH'];
        }
        if ($rootPath === FALSE) {
                $rootPath = dirname(__FILE__) . '/../';
        } elseif (substr($rootPath, -1) !== '/') {
                $rootPath .= '/';
        }

        require_once $rootPath . 'Packages/Framework/TYPO3.FLOW3/Classes/Core/Bootstrap.php';
        
	    $bootstrap = new \TYPO3\FLOW3\Core\Bootstrap($context);
        
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeClassLoader($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeSignalSlot($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializePackageManagement($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeConfiguration($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeSystemLogger($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeErrorHandling($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeCacheManagement($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeProxyClasses($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeClassLoaderClassesCache($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeObjectManager($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializeReflectionService($bootstrap);
        \TYPO3\FLOW3\Core\Booting\Scripts::initializePersistence($bootstrap);

        if ($className == 'Acme\Demo\Persistence\PersistenceManager') {
            return $bootstrap->getObjectManager()->get('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
        } else {
            return $bootstrap->getObjectManager()->get($className);
        }
    }
    
}