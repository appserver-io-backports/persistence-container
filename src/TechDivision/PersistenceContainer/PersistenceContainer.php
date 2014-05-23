<?php

/**
 * TechDivision\PersistenceContainer\PersistenceContainer
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
 * @author    Bernhard Wick <b.wick@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainer;

use Herrera\Annotations\Tokens;
use Herrera\Annotations\Tokenizer;
use Herrera\Annotations\Convert\ToArray;
use TechDivision\MessageQueueProtocol\Message;
use TechDivision\ApplicationServer\ServerNodeConfiguration;
use TechDivision\PersistenceContainer\Utils\BeanUtils;
use TechDivision\ApplicationServer\AbstractContainerThread;

/**
 * Class Container
 *
 * @category  Appserver
 * @package   TechDivision_PersistenceContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @author    Bernhard Wick <b.wick@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class PersistenceContainer extends AbstractContainerThread
{

    /**
     * Updates the message monitor.
     *
     * @param Message $message The message to update the monitor for
     *
     * @return void
     */
    public function updateMonitor(Message $message)
    {
        error_log('Update message monitor for message: ' . spl_object_hash($message));
    }

    /**
     * Attaches the passed bean, depending on it's type to the container.
     *
     * @param object $instance  The bean instance to attach
     * @param string $sessionId The session-ID when we have stateful session bean
     *
     * @return void
     * @throws \Exception Is thrown if we have a stateful session bean, but no session-ID passed
     */
    public function attach($instance, $sessionId = null)
    {

        try {

            // we need a reflection object to read the annotations
            $reflectionObject = new \ReflectionObject($instance);

            // check what kind of bean we have
            switch ($beanType = $this->getBeanAnnotation($reflectionObject)) {

                case BeanUtils::STATEFUL: // @Stateful

                    // lock the container
                    $this->lock();

                    // check if we've a session-ID available
                    if ($sessionId == null) {
                        throw new \Exception("Can't find a session-ID to attach stateful session bean");
                    }

                    // load the session's from the initial context
                    $session = $this->getAttribute($sessionId);

                    // if an instance exists, load and return it
                    if (is_array($session) === false) {
                        $session = array();
                    }

                    // store the bean back to the container
                    $session[$reflectionObject->getName()] = $instance;
                    $this->setAttribute($sessionId, $session);

                    // unlock the container
                    $this->unlock();

                    break;

                case BeanUtils::SINGLETON: // @Singleton

                    // lock the container
                    $this->lock();

                    // replace any existing bean in the container
                    $this->setAttribute($reflectionObject->getName(), $instance);

                    // unlock the container
                    $this->unlock();

                    break;

                case BeanUtils::STATELESS: // @Stateless
                case BeanUtils::MESSAGEDRIVEN: // @MessageDriven

                    // we do nothing here, because we have not state
                    break;

                default: // this should never happen

                    throw new InvalidBeanTypeException("Try to attach invalid bean type '$beanType'");
                    break;
            }

        } catch (\Exception $e) {
            $this->unlock();
            throw $e;
        }
    }

    /**
     * Run's a lookup for the session bean with the passed class name and
     * session ID.
     * If the passed class name is a session bean an instance
     * will be returned.
     *
     * @param string $className The name of the session bean's class
     * @param string $sessionId The session ID
     * @param array  $args      The arguments passed to the session beans constructor
     *
     * @return object The requested session bean
     * @throws \Exception Is thrown if passed class name is no session bean or is a entity bean (not implmented yet)
     */
    public function lookup($className, $sessionId, array $args = array())
    {

        // get the reflection class for the passed class name
        $reflectionClass = $this->newReflectionClass($className);

        // check what kind of bean we have
        switch ($beanType = $this->getBeanAnnotation($reflectionClass)) {

            case BeanUtils::STATEFUL: // @Stateful

                // load the session's from the initial context
                $session = $this->getAttribute($sessionId);

                // if an instance exists, load and return it
                if (is_array($session)) {
                    if (array_key_exists($className, $session)) {
                        return $session[$className];
                    }
                }

                // if not, initialize a new instance, add it to the container and return it
                return $this->newInstance($className, $args);

            case BeanUtils::SINGLETON: // @Singleton

                // check if an instance is available
                if ($this->getAttribute($className)) {
                    return $this->getAttribute($className);
                }

                // if not create a new instance and return it
                return $this->newInstance($className, $args);

            case BeanUtils::STATELESS: // @Stateless
            case BeanUtils::MESSAGEDRIVEN: // @MessageDriven

                return $this->newInstance($className, $args);
                break;

            default: // this should never happen

                throw new InvalidBeanTypeException("Try to lookup invalid bean type '$beanType'");
                break;
        }
    }

    /**
     * Returns the bean annotation for the passed reflection class, that can be
     * one of Entity, Stateful, Stateless, Singleton.
     *
     * @param \ReflectionClass $reflectionClass The class to return the annotation for
     *
     * @throws \Exception Is thrown if the class has NO bean annotation
     * @return string The found bean annotation
     */
    public function getBeanAnnotation(\ReflectionClass $reflectionClass)
    {

        // load the class name to get the annotation for
        $className = $reflectionClass->getName();

        // check if an array with the bean types has already been registered
        $beanTypes = $this->getAttribute('beanTypes');
        if (is_array($beanTypes)) {
            if (array_key_exists($className, $beanTypes)) {
                return $beanTypes[$className];
            }
        } else {
            $beanTypes = array();
        }

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('author', 'package', 'license', 'copyright'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionClass->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // defines the available bean annotations
        $beanAnnotations = array(
            BeanUtils::SINGLETON,
            BeanUtils::STATEFUL,
            BeanUtils::STATELESS,
            BeanUtils::MESSAGEDRIVEN
        );

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {
            $tokeName = strtolower($token->name);
            if (in_array($tokeName, $beanAnnotations)) {
                $beanTypes[$className] = $tokeName;
                $this->setAttribute('beanTypes', $beanTypes);
                return $tokeName;
            }
        }

        // throw an exception if the requested class
        throw new \Exception(sprintf("Missing enterprise bean annotation for %s", $reflectionClass->getName()));
    }

    /**
     * Returns a reflection class instance for the passed class name.
     *
     * @param string $className The class name to return the reflection instance for
     *
     * @return \ReflectionClass The reflection instance
     */
    public function newReflectionClass($className)
    {
        return $this->getInitialContext()->newReflectionClass($className);
    }

    /**
     * Registers the value with the passed key in the container.
     *
     * @param string $key   The key to register the value with
     * @param object $value The value to register
     *
     * @return void
     */
    protected function setAttribute($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * Returns the attribute with the passed key from the container.
     *
     * @param string $key The key the requested value is registered with
     *
     * @return object The requested value
     */
    protected function getAttribute($key)
    {
        return $this[$key];
    }
}
