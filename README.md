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

#### Server factory

As an extra, we can create a full formatted Server request, ir order to retrieve a standard object from

- using the **fromGlobals** method and adquire data from **\_SERVER** superglobal constant without pass any parameter
- using the **fromRequest** method, using a pre created Request as parameter, usefull for tests and console actions

#### Uri factory

As an extra, we can create a full formatted URI, ir order to retrieve a standard object from

- using the **fromGlobals** method and adquire data from **\_SERVER** superglobal constant without pass any parameter

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

### Body String creators

In order to create standard sender bodies, are providing 2 tools in order to create _multipart/form-data_ and _application/x-www-form-urlencoded_

#### URL Encoder

Available for GET or BODY params, creating an urlencoded string from any array type

```php
use JuanchoSL\HttpData\Bodies\Creators\UrlencodedCreator;

echo (new UrlencodedCreator)->appendData([
    'form' => [
        'name' => "nombre",
        'surname' => "apellidos"
    ]
]);

#> form%5Bname%5D=nombre&form%5Bsurname%5D=apellidos
```

#### Multipart Encoder

Available for BODY params, creating multipar/form-data srting from any array type

```php
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;

echo (new MultipartCreator(md5(uniqid())))->appendData([
    'form' => [
        'name' => "nombre",
        'surname' => "apellidos"
    ]
]);

/*
--47608c536770a84a371e08a0ffd92e95
Content-Disposition: form-data; name="form[name]"

nombre
--47608c536770a84a371e08a0ffd92e95
Content-Disposition: form-data; name="form[surname]"

apellidos
--47608c536770a84a371e08a0ffd92e95--
*/
```

Can add files from 3 distincts formats too

```php
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;

$file = realpath('../../file.txt');
$data = [
    'form' => [
        'name' => "nombre",
        'surname' => "apellidos"
    ],
    'file' => [
        new CURLStringFile(file_get_contents($file), basename($file), 'text/plain'),
        new CURLFile($file, 'text/plain', basename($file)),
        '@' . $file
    ]
];
echo (new MultipartCreator(md5(uniqid())))->appendData($data);
/*
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="form[name]"

nombre
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="form[surname]"

apellidos
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="file[]"; filename="file.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 95

esto es un texto de ejemplo desde un fichero
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="file[]"; filename="file.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 95

esto es un texto de ejemplo desde un fichero
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="file[]"; filename="file.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 95

esto es un texto de ejemplo desde un fichero
--b660ef4adf655b4043ab99cd21c6fa92--
*/
```

### Body String reverse parsing

In order to receive standard bodies and parse to use it, you can read and convert the _multipart/form-data_ and _application/x-www-form-urlencoded_ string body contents

#### Multipart decoder

Available for BODY contents, reading multipar/form-data srting to array type

```php
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;

$str = <<< 'EOH'
--47608c536770a84a371e08a0ffd92e95
Content-Disposition: form-data; name="form[name]"

nombre
--47608c536770a84a371e08a0ffd92e95
Content-Disposition: form-data; name="form[surname]"

apellidos
--47608c536770a84a371e08a0ffd92e95--
EOH;

//$body = (new StreamFactory)->createStream($str);
$body = (string) $server_request->getBody();
$body_parsed = (new MultipartReader($body))->getBodyParams();
echo "<pre>" . print_r($body_parsed, true);

<pre>Array
(
    [form] => Array
        (
            [name] => nombre
            [surname] => apellidos
        )

)
```

Can extract files too

```php
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;

$str = <<< 'EOH'
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="form[name]"

nombre
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="form[surname]"

apellidos
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="file[]"; filename="file.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 95

esto es un texto de ejemplo desde un fichero
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="file[]"; filename="file.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 95

esto es un texto de ejemplo desde un fichero
--b660ef4adf655b4043ab99cd21c6fa92
Content-Disposition: form-data; name="file[]"; filename="file.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 95

esto es un texto de ejemplo desde un fichero
--b660ef4adf655b4043ab99cd21c6fa92--
EOH;

//$body = (new StreamFactory)->createStream($str);
$body = (string) $server_request->getBody();
$body_parsed = (new MultipartReader($body))->getBodyFiles();
echo "<pre>" . print_r($body_parsed, true);
<pre>Array
(
    [file] => Array
        (
            [tmp_name] => Array
                (
                    [0] => C:\Users\juan\AppData\Local\Temp\php474B.tmp
                    [1] => C:\Users\juan\AppData\Local\Temp\php474C.tmp
                    [2] => C:\Users\juan\AppData\Local\Temp\php474D.tmp
                )

            [size] => Array
                (
                    [0] => 46
                    [1] => 46
                    [2] => 46
                )

            [error] => Array
                (
                    [0] => 0
                    [1] => 0
                    [2] => 0
                )

            [name] => Array
                (
                    [0] => file.txt
                    [1] => file.txt
                    [2] => file.txt
                )

            [type] => Array
                (
                    [0] => text/plain
                    [1] => text/plain
                    [2] => text/plain
                )

        )
)
```

We can extract both value groups, as array, with data into index 0 and files into index 1

```php
[$_POST, $_FILES] = (new MultipartReader($body))->getBodyParts();
```

Or populate to globals directly in order to use it for PATCH and PUT requests

```php
(new MultipartReader($body))->toGlobals();
```
