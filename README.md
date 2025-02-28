# HttpData

## Description

This library implements the definitions of the different elements involved in data transmissions such as HTTP, applying the interfaces defineds in PSRS 7 and 17, adding the extra objects used and ensuring compliance with the requirements that allow to give the necessary stability to our application, being able to implement for third -party bookstores without the need to adapt our business logic to a new implementation that could modify its consumption or internal structure.

## Install

```bash
composer require juanchosl/httpdata
```

## Contents

Includes the needed implementations for a data transmission. You can view his definitions for each required block on the official page

### PSR-7 Messages

The Containers folder/namespace, implementation of elements included into [PSR-7](https://www.php-fig.org/psr/psr-7)

- Message base class
- Request (extends Message, Client side)
- ServerRequest (extends Request, Server side)
- Response (extends Message)
- Streams
- Uris
- Uploaded files

### PSR-17 Factories

The Factories folder/namespace, defines how create the objects, ensuring the full compatibility between libraries, applying the [PSR-17](https://www.php-fig.org/psr/psr-17)

- Server request
- Client request
- Response
- Streams
- Uris
- Uploaded files

### FIG Message utils

The library includes the messages utils from FIGs of PHP, includes the

#### RequestMethodsInterface

Constants for the distincts http available methods

- GET
- POST
- PUT
- PATCH
- DELETE
- HEAD
- OPTIONS
- PURGE
- TRACE
- CONNECT

#### StatusCodesInterface

All the standard status codes, for use across your system
