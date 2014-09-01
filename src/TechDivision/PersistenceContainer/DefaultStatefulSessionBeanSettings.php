<?php

/**
 * TechDivision\PersistenceContainer\DefaultStatefulSessionBeanSettings
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

use TechDivision\Http\HttpCookie;
use TechDivision\Servlet\ServletSession;
use TechDivision\Servlet\ServletContext;
use TechDivision\Storage\GenericStackable;

/**
 * Interface for all session storage implementation.
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class DefaultStatefulSessionBeanSettings extends GenericStackable implements StatefulSessionBeanSettings
{

    /**
     * The default lifetime in seconds.
     *
     * @var string
     */
    const DEFAULT_LIFETIME = 1440;

    /**
     * The default probaility the garbage collection will be invoked.
     *
     * @var string
     */
    const DEFAULT_GARBAGE_COLLECTION_PROBABILITY = 0.1;

    /**
     * Initialize the default session settings.
     */
    public function __construct()
    {
        // initialize the default values
        $this->setLifetime(DefaultStatefulSessionBeanSettings::DEFAULT_LIFETIME);
        $this->setGarbageCollectionProbability(DefaultStatefulSessionBeanSettings::DEFAULT_GARBAGE_COLLECTION_PROBABILITY);
    }

    /**
     * Sets the number of seconds for a stateful session bean lifetime.
     *
     * @param integer $lifetime The stateful session bean lifetime
     *
     * @return void
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * Returns the number of seconds for a stateful session bean lifetime.
     *
     * @return integer The stateful session bean lifetime
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Sets the probability the garbage collector will be invoked on the session.
     *
     * @param float $garbageCollectionProbability The garbage collector probability
     *
     * @return void
     */
    public function setGarbageCollectionProbability($garbageCollectionProbability)
    {
        $this->garbageCollectionProbability = $garbageCollectionProbability;
    }

    /**
     * Returns the probability the garbage collector will be invoked on the session.
     *
     * @return float The garbage collector probability
     */
    public function getGarbageCollectionProbability()
    {
        return $this->garbageCollectionProbability;
    }

    /**
     * Merge the passed params with the default settings.
     *
     * @param array $params The associative array with the params to merge
     *
     * @return void
     */
    public function mergeWithParams(array $params)
    {
        // merge the passed properties with the default settings for the stateful session beans
        foreach (array_keys(get_object_vars($this)) as $propertyName) {
            if (array_key_exists($propertyName, $params)) {
                $this->$propertyName = $params[$propertyName];
            }
        }
    }
}
