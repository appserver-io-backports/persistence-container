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

/**
 * The application instance holds all information about the deployed application
 * and provides a reference to the entity manager and the initial context.
 *
 * @package TechDivision\PersistenceContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class Application extends AbstractApplication
{

    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     *
     * @return \TechDivision\PersistenceContainer\Application The connected application
     */
    public function connect()
    {

        // initialize the class loader with the additional folders
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath());
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath() . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'classes');
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath() . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR . 'lib');

        // return the instance itself
        return $this;
    }

    /**
     *
     * @param type $className
     * @param type $sessionId
     * @internal param \TechDivision\PersistenceContainer\type $args
     * @return type
     */
    public function lookup($className, $sessionId)
    {
        return $this->initialContext->lookup($className, $sessionId, array(
            $this
        ));
    }
}