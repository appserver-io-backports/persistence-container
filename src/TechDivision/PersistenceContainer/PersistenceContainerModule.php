<?php

/**
 * TechDivision\PersistenceContainer\PersistenceContainerModule
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

use TechDivision\Storage\GenericStackable;
use TechDivision\ServletEngine\ServletEngine;
use TechDivision\PersistenceContainer\RequestHandler;
use TechDivision\PersistenceContainer\PersistenceContainerValve;

/**
 * A persistence container module implementation.
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class PersistenceContainerModule extends ServletEngine
{

    /**
     * The unique module name in the web server context.
     *
     * @var string
     */
    const MODULE_NAME = 'persistence-container';

    /**
     * Returns the module name.
     *
     * @return string The module name
     */
    public function getModuleName()
    {
        return PersistenceContainerModule::MODULE_NAME;
    }

    /**
     * Initialize the valves that handles the requests.
     *
     * @return void
     */
    public function initValves()
    {
        $this->valves[] = new PersistenceContainerValve();
    }

    /**
     * Initialize the request handlers.
     *
     * @return void
     */
    public function initRequestHandlers()
    {
        // we want to prepare an request for each application and each worker
        foreach ($this->getApplications() as $pattern => $application) {
            $this->requestHandlers['/' . $application->getName()] = new GenericStackable();
            for ($i = 0; $i < 4; $i++) {
                $this->requestHandlers['/' . $application->getName()][$i] = new RequestHandler($application);
            }
        }
    }
}
