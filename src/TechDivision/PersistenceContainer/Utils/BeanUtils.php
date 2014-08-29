<?php

/**
 * TechDivision\PersistenceContainer\Utils\BeanUtils
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_PersistenceContainer
 * @subpackage Utils
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer\Utils;

/**
 * Utility class with some bean utilities.
 *
 * @category   Appserver
 * @package    TechDivision_PersistenceContainer
 * @subpackage Utils
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class BeanUtils
{

    /**
     * Private to constructor to avoid instancing this class.
     */
    private function __construct()
    {
    }

    /**
     * The key for a stateful session bean.
     *
     * @var string
     */
    const STATEFUL = 'stateful';

    /**
     * The key for a stateless session bean.
     *
     * @var string
     */
    const STATELESS = 'stateless';

    /**
     * The key for a singleton session bean.
     *
     * @var string
     */
    const SINGLETON = 'singleton';

    /**
     * The key for a message bean.
     *
     * @var string
     */
    const MESSAGEDRIVEN = 'messagedriven';

    /**
     * The key for a singleton session bean that has to be started after deployment.
     *
     * @var string
     */
    const STARTUP = 'startup';

    /**
     * The annotation for a method that has to be invoked after the instance has been created.
     *
     * @var string
     */
    const POST_CONSTRUCT = 'postconstruct';

    /**
     * The annotation for a method that has to be invoked before the instance will be destroyed
     *
     * @var string
     */
    const PRE_DESTROY = 'predestroy';

    /**
     * The annotation for a method that has to be invoked before the instance (can only be a stateful session bean) will be activated.
     *
     * @var string
     */
    const POST_ACTIVATE = 'postactivate';

    /**
     * The annotation for a method that has to be invoked before the instance (can only be a stateful session bean) will be passivated.
     *
     * @var string
     */
    const PRE_PASSIVATE = 'prepassivate';
}
