<?php

/**
 * TechDivision\PersistenceContainer\ServiceContext
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\Application\Interfaces\ManagerInterface;

/**
 * The interface for the service manager registered for the application.
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
interface ServiceContext extends ManagerInterface
{

    /**
     * Returns the absolute path to the web application.
     *
     * @return string The absolute path
     */
    public function getWebappPath();

    /**
     * Return the service locator instance.
     *
     * @return \TechDivision\PersistenceContainer\ServiceLocator The service locator instance
     */
    public function getServiceLocator();

    /**
     * Tries to locate and return the service with the passed name.
     *
     * @param string $serviceName The name of the requested service
     * @param array  $args        The arguments passed to the service providers constructor
     *
     * @return \TechDivision\PersistenceContainer\ServiceProvider The requested service instance
     */
    public function locate($serviceName, array $args = array());

    /**
     * Attaches the passed service, to the context.
     *
     * @param \TechDivision\PersistenceContainer\ServiceProvider $instance The service instance to attach
     *
     * @return void
     * @throws \TechDivision\PersistenceContainer\ServiceAlreadyRegisteredException Is thrown if the passed service has already been registered
     */
    public function register(ServiceProvider $instance);

    /**
     * Return the storage with the services.
     *
     * @return \TechDivision\Storage\StorageInterface The storage with the services
     */
    public function getServices();
}
