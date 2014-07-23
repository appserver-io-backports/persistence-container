# TechDivision_PersistenceContainer

[![Latest Stable Version](https://poser.pugx.org/techdivision/persistencecontainer/v/stable.png)](https://packagist.org/packages/techdivision/persistencecontainer) [![Total Downloads](https://poser.pugx.org/techdivision/persistencecontainer/downloads.png)](https://packagist.org/packages/techdivision/persistencecontainer) [![Latest Unstable Version](https://poser.pugx.org/techdivision/persistencecontainer/v/unstable.png)](https://packagist.org/packages/techdivision/persistencecontainer) [![License](https://poser.pugx.org/techdivision/persistencecontainer/license.png)](https://packagist.org/packages/techdivision/persistencecontainer) [![Build Status](https://travis-ci.org/techdivision/TechDivision_PersistenceContainer.png)](https://travis-ci.org/techdivision/TechDivision_PersistenceContainer)[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/techdivision/TechDivision_PersistenceContainer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/techdivision/TechDivision_PersistenceContainer/?branch=master)[![Code Coverage](https://scrutinizer-ci.com/g/techdivision/TechDivision_PersistenceContainer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/techdivision/TechDivision_PersistenceContainer/?branch=master)

## Introduction

This package provides generic application implementation designed to work in an application
server like appserver.io. The persistence container handles the lifecycle of all enterprise
PHP components it provides. Therefore components, like Session- or MessageBeans are created
when a client requests a new instance, or if a Bean is marked to be instanciated at startup
time.

## Installation

You don't have to install this package, as it'll be delivered with the latest appserver.io 
release. If you want to install it with your application only, you do this by add

```sh
{
    "require": {
        "techdivision/persistencecontainer": "dev-master"
    },
}
```

to your ```composer.json``` and invoke ```composer update``` in your project.

## Usage

As described in the introduction the application is designed inside a runtime environment like
an application server as appserver.io is. The following example gives you a short introdction 
how you can create a stateful session bean and the way you can invoke it's method on client side.

First thing you've to do is to create your SessionBean. What is a SessionBean? It's not simple
to describe it in only a few words, but i'll try. A SessionBean basically is plain PHP class.
You MUST not instanciate it directly, because the application server takes care of its complete
lifecycle. Therefore, if you need an instance of a SessionBean, you'll ask the application server 
to give you a instance. This can be done by the (persistence container client)[https://github.com/techdivision/TechDivision_PersistenceContainerClient].

The persistence container client will give you a proxy to the session bean that allows you to
invoke all methods the SessionBean provides as you can do if you would have real instance. But
the proxy also allows you to call this method over a network as remote method call. Using the 
persistence container client makes it obvious for you if your SessionBean is on the same 
application server instance or on another one in your network. This gives you the possibilty
to distribute the components of your application over your network what includes a great and
seemless scalabilty.

You have to tell the persistence container of the type the SessionBean should have. This MUST 
be done by simply add an annotation to the class doc block. The possible annotations therefore 
are

* @Singleton
* @Stateless
* @Stateful

The SessionBean types are self explained i think.

### @Singleton SessionBean

A SessionBean with a @Singleton annotation will be created only one time for each application.
This means, whenever you'll request an instance, you'll receive the same one. If you set a
variable in the SessionBean, it'll be available until you'll overwrite it, or the application
server has been restarted.

### @Stateless SessionBean

In opposite to a singleton session bean, a SessionBean with a @Stateless annotation will always
be instanciated when you request it. It has NO state, only for the time you invoke a method on
it.

### @Stateful SessionBean

The @Stateful SessionBean is something between the other types. It is stateful for the session
with the ID you pass to the client when you request the instance. A stateful SessionBean is 
useful if you want to implement something like a shopping cart. If you declare the shopping cart 
instance a class member of your SessionBean makes it persistent for your session lifetime.

## Example

The following example shows you a really simple implementation of a stateful SessionBean providing
a counter that'll be raised whenever you call the raiseMe() method.

```php

namespace Namespace\Module;

/**
 * This is demo implementation of stateful session bean.
 *
 * @Stateful
 */
class MyStatefulSessionBean
{

    /**
     * Stateful counter that exists as long as your session exists.
     *
     * @var integer
     */
    protected $counter = 0;

    /**
     * Passes a reference to the application context to our session bean.
     *
     * @param \TechDivision\Application\Interface\ApplicationInterface $application The application instance
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Example method that raises the counter by one each time you'll invoke it.
     *
     * @return void
     */
    public function raiseMe()
    {
        $this->counter++;
    }
}
```

As described above, you MUST not instanciate it directly. The request an instance of the SessionBean
you MUST use the persistence container client. With the lookup() method you'll receive a proxy to
your SessionBean, on that you can invoke the methods as you can do with a real instance.

```php

// initialize the connection and the session
$connection = ConnectionFactory::createContextConnection('your-application-name');
$contextSession = $connection->createContextSession();

// set the session ID of the actual request (necessary for SessionBeans declared as @Stateful)
$contextSession->setSessionId('your-session-id');

// create an return the proxy instance and call a method, invokeSomeMethod() in this example
$proxyInstance = $contextSession->createInitialContext()->lookup('Namespace\Module\MyStatefulSessionBean');
$proxyIntance->raiseMe();

```