<?php
/**
 * TechDivision\PersistenceContainer\Application
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use TechDivision\ApplicationServer\AbstractApplication;

/**
 * The application instance holds all information about the deployed application
 * and provides a reference to the entity manager and the initial context.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
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
     * Look's up the classname
     *
     * @param string $className The classname
     * @param string $sessionId The session id
     *
     * @return mixed The instantiated class
     */
    public function lookup($className, $sessionId)
    {
        return $this->initialContext->lookup($className, $sessionId, array(
            $this
        ));
    }
}
