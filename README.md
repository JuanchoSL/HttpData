# HttpData

## Description

This library groups the definitions of the different elements involved in data transmissions such as HTTP, applying the interfaces defined in PSRS 7 and 17, adding the extra objects used and ensuring compliance with the requirements that allow to give the necessary stability To our application, being able to implement for third -party bookstores without the need to adapt our business logic to a new implementation that could modify its consumption or internal structure.

## Install
```bash
composer require juanchosl/httpdata
composer update
```

## Contents

The Containers folders, includes the distinct elements needed for a data transmission. You can view his definitions for each required block on the official page

### PSR-7 Messages

Implementation of elements included into [PSR-7](https://www.php-fig.org/psr/psr-7)

* Message base class
* Request (extends Message, Client side)
* ServerRequest (extends Request, Server side)
* Response (extends Message)
* Streams
* Uris
* Uploaded files

### PSR-17 Factories

Defines how create the objects, ensuring the full compatibility between libraries, applying the [PSR-17](https://www.php-fig.org/psr/psr-17)

* Server request
* Client request
* Response
* Streams
* Uris
* Uploaded files