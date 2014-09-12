<?php

/**
 * TechDivision\PersistenceContainer\ServiceResourceLocator
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\PersistenceContainer\ServiceContext;

/**
 * Interface for the service resource locator instances.
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainer
 * @link      http://www.appserver.io
 */
interface ServiceResourceLocator
{

    /**
     * Tries to locate the service with the passed identifier
     *
     * @param \TechDivision\PersistenceContainer\ServiceContext $serviceContext    The service context instance
     * @param string                                            $serviceIdentifier The identifier of the service to be located
     * @param array                                             $args              The arguments passed to the service providers constructor
     *
     * @return \TechDivision\PersistenceContainer\ServiceProvider The requested service provider instance
     * @see \TechDivision\PersistenceContainer\ServiceResourceLocator::locate()
     */
    public function locate(ServiceContext $serviceContext, $serviceIdentifier, array $args = array());
}
