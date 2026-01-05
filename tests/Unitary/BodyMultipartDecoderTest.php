<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;
use JuanchoSL\HttpData\Containers\Stream;
use PHPUnit\Framework\TestCase;

class BodyMultipartDecoderTest extends TestCase
{

    public function testBodyWithPlainArray()
    {
        $body = ['clave' => 'valor'];
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, (string) $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParams();
        $this->assertEquals($body, $response);
    }

    public function testBodyWithPlainArrayPartial()
    {
        $body = ['clave' => 'valor'];
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData(['clave' => ''])->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, (string) $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParams();
        $this->assertEquals($body, $response);
    }

    public function testBodyWithMultiAssocArray()
    {
        $body = ['clave' => ['subclave' => 'valor']];
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParams();
        $this->assertEquals($body, $response);
    }

    public function testBodyWithMultiAssocArrayPartial()
    {
        $body = ['clave' => ['subclave' => 'valor']];
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData(['clave' => []])->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParams();
        $this->assertEquals($body, $response);
    }

    public function testBodyWithMultiIndexedArray()
    {
        $body = ['clave' => ['subclave', 'valor']];
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParams();
        $this->assertEquals($body, $response);
    }

    public function testBodyWithMultiIndexedArrayPartial()
    {
        $body = ['clave' => ['subclave', 'valor']];
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData(['clave' => []])->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParams();
        $this->assertEquals($body, $response);
    }

    public function testBodyWithMultiFile()
    {
        $body = $this->getData();
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, $body);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParts();
        $this->assertIsArray($response);
        $this->assertArrayHasKey(1, $response);
        foreach ($response[1] as $file_name => $file_values) {
            $this->assertEquals($file_name, 'file');
            $this->assertIsArray($file_values);
            $this->assertArrayHasKey('tmp_name', $file_values);
        }
    }

    protected function getData(): string
    {
        return '--_X_BOUNDARY_TEST_CURL_
Content-Disposition: form-data; name="file[uno][]"; filename="github_token.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 10

qwertyuiop
--_X_BOUNDARY_TEST_CURL_
Content-Disposition: form-data; name="file[uno][]"; filename="github_token1.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary
Content-Length: 10

qwertyuiop
--_X_BOUNDARY_TEST_CURL_
Content-Disposition: form-data; name="file[tres]"; filename="source-libs.code-workspace"
Content-Type: application/json
Content-Transfer-Encoding: binary
Content-Length: 2050

{
        "folders": [
                {
                        "name": "APITemplate",
                        "path": "APITemplate"
                },
                {
                        "name": "APITemplateInstaller",
                        "path": "APITemplateInstaller"
                },
                {
                        "name": "APITemplatePlugins",
                        "path": "APITemplatePlugins"
                },
                {
                        "name": "AssetMinifyer",
                        "path": "AssetMinifyer"
                },
                {
                        "name": "Backups",
                        "path": "Backups"
                },
                {
                        "name": "Cronjobs",
                        "path": "Cronjobs"
                },
                {
                        "name": "Cryptology",
                        "path": "Cryptology"
                },
                {
                        "name": "CurlClient",
                        "path": "CurlClient"
                },
                {
                        "name": "DataTransfer",
                        "path": "DataTransfer"
                },
                {
                        "name": "Email",
                        "path": "Email"
                },
                {
                        "name": "EnvVars",
                        "path": "EnvVars"
                },
                {
                        "name": "Exceptions",
                        "path": "Exceptions"
                },
                {
                        "name": "FtpClient",
                        "path": "FtpClient"
                },
                {
                        "path": "Gsuite"
                },
                {
                        "name": "HtmlGenerator",
                        "path": "HtmlGenerator"
                },
                {
                        "name": "HttpHeaders",
                        "path": "HttpHeaders"
                },
                {
                        "name": "Logger",
                        "path": "Logger"
                },
                {
                        "name": "Monitor",
                        "path": "Monitor"
                },
                {
                        "name": "Orm",
                        "path": "Orm"
                },
                {
                        "name": "SimpleCache",
                        "path": "SimpleCache"
                },
                {
                        "name": "Sockets",
                        "path": "Sockets"
                },
                {
                        "name": "TemplateRender",
                        "path": "TemplateRender"
                },
                {
                        "name": "Terminal",
                        "path": "Terminal"
                },
                {
                        "name": "Tokenizer",
                        "path": "Tokenizer"
                },
                {
                        "name": "Validators",
                        "path": "Validators"
                },
                {
                        "name": "WebCache",
                        "path": "WebCache"
                },
                {
                        "path": "RequestListener"
                },
                {
                        "path": "HttpData"
                },
                {
                        "path": "CliData"
                }
        ],
        "settings": {
                "phpunit.postTask": "",
                "php.version": "v8.4ts",
                "sqltools.useNodeRuntime": true,
                "php.debug.ideKey": "xdebug",
                "php.debug.executablePath": "C:\\php\\8.4ts\\php.exe",
                "github-actions.workflows.pinned.workflows": [],
                "php.debug.port": [],
                "php.validate.executablePath": "C:\\php\\8.4ts\\php.exe",
                "phpunit.phpunit": "\"${cwd}\\vendor\\bin\\phpunit\"",
                "phpunit.command": "\"${php}\" ${phpargs} ${phpunit} ${phpunitargs}"
        }
}
--_X_BOUNDARY_TEST_CURL_--';
    }
}