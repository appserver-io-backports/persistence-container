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
use TechDivision\Server\Interfaces\ServerContextInterface;
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
     * Initialize the module.
     *
     * @return void
     */
    public function __construct()
    {

        // call parent constructor
        parent::__construct();

        /**
         * The initialized garbage collector instances.
         *
         * @var \TechDivision\Storage\GenericStackable
         */
        $this->garbageCollectors = new GenericStackable();

        /**
         * The initialized timer service worker instances.
         *
         * @var \TechDivision\Storage\GenericStackable
         */
        $this->timerServiceWorker = new GenericStackable();
    }

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
     * Initializes the module.
     *
     * @param \TechDivision\Server\Interfaces\ServerContextInterface $serverContext The servers context instance
     *
     * @return void
     * @throws \TechDivision\Server\Exceptions\ModuleException
     */
    public function init(ServerContextInterface $serverContext)
    {

        // call parent init() method
        parent::init($serverContext);

        // add a garbage collector and timer service workers for each application
        foreach ($this->getApplications() as $application) {
            $this->garbageCollectors[] = new StandardGarbageCollector($application);
            $this->timerServiceWorkers[] = new TimerServiceExecutor($application);
        }
    }
}
