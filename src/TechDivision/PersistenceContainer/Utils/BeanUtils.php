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

use Herrera\Annotations\Tokens;
use Herrera\Annotations\Tokenizer;
use Herrera\Annotations\Convert\ToArray;
use TechDivision\Context\Context;
use TechDivision\Storage\GenericStackable;
use TechDivision\EnterpriseBeans\TimerConfig;
use TechDivision\EnterpriseBeans\ScheduleExpression;
use TechDivision\PersistenceContainer\Annotations\Schedule;
use TechDivision\PersistenceContainer\TimedObjectInvoker;
use TechDivision\PersistenceContainer\Annotations\PostConstruct;
use TechDivision\PersistenceContainer\Annotations\PreDestroy;
use TechDivision\PersistenceContainer\Annotations\Timeout;
use TechDivision\PersistenceContainer\Annotations\MessageDriven;
use TechDivision\PersistenceContainer\Annotations\Startup;
use TechDivision\PersistenceContainer\Annotations\Stateful;
use TechDivision\PersistenceContainer\Annotations\Stateless;
use TechDivision\PersistenceContainer\Annotations\Singleton;

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
class BeanUtils extends GenericStackable implements Context
{

    /**
     * Registers the value with the passed key in the container.
     *
     * @param string $key   The key to register the value with
     * @param object $value The value to register
     *
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * Returns the attribute with the passed key from the container.
     *
     * @param string $key The key the requested value is registered with
     *
     * @return mixed|null The requested value if available
     */
    public function getAttribute($key)
    {
        if (isset($this[$key])) {
            return $this[$key];
        }
    }

    /**
     * Returns TRUE if the class has the passed annotation, else FALSE.
     *
     * @param \ReflectionClass $reflectionClass The class to return the annotation for
     * @param string           $annotation      The annotation to check for
     *
     * @return boolean TRUE if the bean has the passed annotation, else FALSE
     */
    public function hasBeanAnnotation(\ReflectionClass $reflectionClass, $annotation)
    {

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('author', 'package', 'license', 'copyright'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionClass->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {
            $tokeName = strtolower($token->name);
            if ($tokeName === $annotation) {
                return true;
            }
        }

        // return FALSE if bean annotation has not been found
        return false;
    }

    /**
     * Returns TRUE if the method has the passed annotation, else FALSE.
     *
     * @param \ReflectionMethod $reflectionMethod The method to return the annotation for
     * @param string            $annotation       The annotation to check for
     *
     * @return boolean TRUE if the bean has the passed annotation, else FALSE
     */
    public function hasMethodAnnotation(\ReflectionMethod $reflectionMethod, $annotation)
    {

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('author', 'package', 'license', 'copyright'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionMethod->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {
            $tokeName = strtolower($token->name);
            if ($tokeName === $annotation) {
                return true;
            }
        }

        // return FALSE if bean annotation has not been found
        return false;
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
            MessageDriven::ANNOTATION,
            Stateful::ANNOTATION,
            Stateless::ANNOTATION,
            Singleton::ANNOTATION,
            Startup::ANNOTATION
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
        throw new \Exception(sprintf('Missing enterprise bean annotation for %s', $reflectionClass->getName()));
    }

    /**
     * Returns the method annotation for the passed reflection method, that can be
     * one of PostConstruct or PreDestroy.
     *
     * @param \ReflectionMethod $reflectionMethod The method to return the annotation for
     *
     * @return string|null The found method annotation
     */
    public function getMethodAnnotation(\ReflectionMethod $reflectionMethod)
    {

        // load the method name to get the annotation for
        $methodName = $reflectionMethod->getName();

        // check if an array with the message types has already been registered
        $methodTypes = $this->getAttribute('methodTypes');
        if (is_array($methodTypes)) {
            if (array_key_exists($methodName, $methodTypes)) {
                return $methodTypes[$methodName];
            }
        } else {
            $methodTypes = array();
        }

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('param', 'return', 'throws', 'see', 'link'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionMethod->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // defines the available method annotations
        $methodAnnotations = array(
            PostConstruct::ANNOTATION,
            PreDestroy::ANNOTATION
        );

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {
            $tokeName = strtolower($token->name);
            if (in_array($tokeName, $methodAnnotations)) {
                $methodTypes[$methodName] = $tokeName;
                $this->setAttribute('methodTypes', $methodTypes);
                return $tokeName;
            }
        }
    }

    /**
     * Returns the value of the method annotation for the passed reflection method.
     *
     * @param \ReflectionMethod $reflectionMethod The method to return the annotation value for
     * @param string            $annotation       The annotation to check for
     *
     * @return \TechDivision\PersistenceContainer\Annotations\AnnotationInterface|null The found method annotation
     */
    public function getMethodAnnotationInstance(\ReflectionMethod $reflectionMethod, $annotation)
    {

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('param', 'return', 'throws', 'see', 'link'));

        // set the aliases
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionMethod->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {

            // check if the passed token name equals the requested one
            if (strtolower($token->name) === $annotation) {

                // prepare the name of the annotation class
                $annotationClass = sprintf('TechDivision\\PersistenceContainer\\Annotations\\%s', $token->name);

                // create a new instance, initialize and return it
                $reflectionClass = new \ReflectionClass($annotationClass);
                return $reflectionClass->newInstance($token);
            }
        }
    }

    /**
     * Returns an array with the bean annotations for the passed reflection class.
     *
     * @param \ReflectionClass $reflectionClass The bean to return the annotations for
     *
     * @return array The found bean annotations
     */
    public function getBeanAnnotations(\ReflectionClass $reflectionClass)
    {

        // initialize the array with the found annotations
        $annotationsFound = array();

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('author', 'package', 'license', 'copyright'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionClass->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // defines the available method annotations
        $beanAnnotations = array(
            MessageDriven::ANNOTATION,
            Stateful::ANNOTATION,
            Stateless::ANNOTATION,
            Singleton::ANNOTATION,
            Startup::ANNOTATION
        );

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {

            // prepare the annotation name
            $annotationName = strtolower($token->name);

            // check if the passed token name equals the requested one
            if (in_array($annotationName, $beanAnnotations)) {

                // prepare the name of the annotation class
                $annotationClass = sprintf('TechDivision\\PersistenceContainer\\Annotations\\%s', $token->name);

                // create a new instance, initialize and return it
                $reflectionClass = new \ReflectionClass($annotationClass);
                $annotationsFound[$annotationName] = $reflectionClass->newInstance($token);
            }
        }

        // returns the array with the found annotations
        return $annotationsFound;
    }

    /**
     * Returns an array with the method annotations for the passed reflection method.
     *
     * @param \ReflectionMethod $reflectionMethod The method to return the annotations for
     *
     * @return array The found method annotations
     */
    public function getMethodAnnotations(\ReflectionMethod $reflectionMethod)
    {

        // initialize the array with the found annotations
        $annotationsFound = array();

        // initialize the annotation tokenizer
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('param', 'return', 'throws', 'see', 'link'));
        $aliases = array();

        // parse the doc block
        $parsed = $tokenizer->parse($reflectionMethod->getDocComment(), $aliases);

        // convert tokens and return one
        $tokens = new Tokens($parsed);
        $toArray = new ToArray();

        // defines the available method annotations
        $methodAnnotations = array(
            PostConstruct::ANNOTATION,
            PreDestroy::ANNOTATION,
            Schedule::ANNOTATION,
            Timeout::ANNOTATION
        );

        // iterate over the tokens
        foreach ($toArray->convert($tokens) as $token) {

            // prepare the annotation name
            $annotationName = strtolower($token->name);

            // check if the passed token name equals the requested one
            if (in_array($annotationName, $methodAnnotations)) {

                // prepare the name of the annotation class
                $annotationClass = sprintf('TechDivision\\PersistenceContainer\\Annotations\\%s', $token->name);

                // create a new instance, initialize and return it
                $reflectionClass = new \ReflectionClass($annotationClass);
                $annotationsFound[$annotationName] = $reflectionClass->newInstance($token);
            }
        }

        // returns the array with the found annotations
        return $annotationsFound;
    }
}
