# Version 0.7.8

## Bugfixes

* None

## Features

* Switch to TechDivision_EnterpriseBeans version 0.1.0

# Version 0.7.7

## Bugfixes

* None

## Features

* Use default RequestHandler class of ServletEngine instead of own implementation

# Version 0.7.6

## Bugfixes

* None

## Features

* Add shutdown handler method to RequestHandler class

# Version 0.7.5

## Bugfixes

* Make BeanManager::registerBeans() method Windows compliant

## Features

* None

# Version 0.7.4

## Bugfixes

* Remove HTTP status code from PersistenceContainerValve when catching an application exception to allow pass exception as body content
* Add ModuleException use statuement to PersistenceContainerModule
* Change composer dependency from appserver to servletengine
* Minor bugfixes to optimize PHP mess detector analysis

## Features

* None

# Version 0.7.3

## Bugfixes

* None

## Features

* Refactoring ANT PHPUnit execution process
* Composer integration by optimizing folder structure (move bootstrap.php + phpunit.xml.dist => phpunit.xml)
* Switch to new appserver-io/build build- and deployment environment

# Version 0.7.2

## Bugfixes

* None

## Features

* Set composer dependency for techdivision/appserver to >=0.8

# Version 0.7.1

## Bugfixes

* None

## Features

* Add REAMDE.md with short description and installation/configuration instructions
* Remove locking/unlocking for beans stackable from BeanManager::attach() method to avoid deadlocks
* Load provide session ID in PersistenceContainerValve::invoke() method to allow stateful session beans
* Temporarily add error_log() statement in catch() block of PersistenceContainerValve::invoke() method
* Temporarily add error_log() statement in catch() block of RequestHandler::run() method