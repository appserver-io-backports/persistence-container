Version 0.7.2

Bugfixes

* None

Features

* Set composer dependency for techdivision/appserver to >=0.8

Version 0.7.1

Bugfixes

* None

Features

* Add REAMDE.md with short description and installation/configuration instructions
* Remove locking/unlocking for beans stackable from BeanManager::attach() method to avoid deadlocks
* Load provide session ID in PersistenceContainerValve::invoke() method to allow stateful session beans
* Temporarily add error_log() statement in catch() block of PersistenceContainerValve::invoke() method
* Temporarily add error_log() statement in catch() block of RequestHandler::run() method