# Version 0.9.4

## Bugfixes

* Remove unnecessary stackable initialization from BeanManagerFactory::visit() method

## Features

* Add session-ID, necessary to create new SFBs, to BeanManager::newInstance() method
* Invoke methods with a @Startup annotation in BeanLocator::lookup() method now

# Version 0.9.3

## Bugfixes

* None

## Features

* Add BeanManager::lookup() method
* Move Annotations to techdivision/enterprisebeans package
* Switch to NamingDirectory integration 

# Version 0.9.2

## Bugfixes

* Bugfix invalid synchronization for SFBs map by making newInstance() + removeBySessionId() methods protected

## Features

* None

# Version 0.9.1

## Bugfixes

* None

## Features

* Add map + factory for SFBs
* Add dependency to new appserver-io/logger library
* Integration of monitoring/profiling functionality
* Revert integration to initialize manager instances with thread based factories

# Version 0.9.0

## Bugfixes

* None

## Features

* Integration to initialize manager instances with thread based factories

# Version 0.8.5

## Bugfixes

* Inject all Stackable instances instead of initialize them in BeanManager::__construct, ServiceRegistry::__construct, TimerServiceRegistry::__construct => pthreads 2.x compatibility

## Features

* None

# Version 0.8.4

## Bugfixes

* Add synchronized() method around all wait()/notify() calls => pthreads 2.x compatibility

## Features

* None

# Version 0.8.3

## Bugfixes

* Add synchronized() method around all wait()/notify() calls => pthreads 2.x compatibility

## Features

* None

# Version 0.8.2

## Bugfixes

* None

## Features

* Integration of techdivision/naming package with basic naming directory functionality

# Version 0.8.1

## Bugfixes

* Move composer dependency to techdivision/enterprisebeans from require-dev to require

## Features

* Issue #185: Add basic timer service functionality

# Version 0.8.0

## Bugfixes

* Bugfix invalid usage of passed class name instead of real class name, loaded from ReflectionClass in BeanLocator::lookup() method

## Features

* Issue #185: Add basic timer service functionality

# Version 0.7.10

## Bugfixes

* None

## Features

* Switch to new ClassLoader + ManagerInterface
* Add configuration parameters to manager configuration
* Implement annotations @PostConstruct and @PreDestroy for session and message beans
* Implement @Startup annotation for singleton session beans

# Version 0.7.9

## Bugfixes

* Only re-attach beans of type @Stateful or @Singleton to container, ignore beans of type @Stateless or @MessageDriven

## Features

* None

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