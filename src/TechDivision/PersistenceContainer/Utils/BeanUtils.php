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
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * The key for a stateful session bean.
     *
     * @return string
     */
    const STATEFUL = 'stateful';

    /**
     * The key for a stateless session bean.
     *
     * @return string
     */
    const STATELESS = 'stateless';

    /**
     * The key for a singleton session bean.
     *
     * @return string
     */
    const SINGLETON = 'singleton';

    /**
     * The key for a message bean.
     *
     * @return string
     */
    const MESSAGEDRIVEN = 'messagedriven';
}
