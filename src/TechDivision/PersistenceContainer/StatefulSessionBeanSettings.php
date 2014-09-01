<?php

/**
 * TechDivision\PersistenceContainer\StatefulSessionBeanSettings
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

/**
 * Interface for the stateful session bean settings.
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
interface StatefulSessionBeanSettings
{

    /**
     * Returns the number of seconds for a stateful session bean lifetime.
     *
     * @return integer The stateful session bean lifetime
     */
    public function getLifetime();

    /**
     * Returns the probability the garbage collector will be invoked on the session.
     *
     * @return float The garbage collector probability
     */
    public function getGarbageCollectionProbability();
}
