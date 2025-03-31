<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use CURLFile;
use CURLStringFile;
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;
use JuanchoSL\HttpData\Containers\Stream;
use PHPUnit\Framework\TestCase;

class BodyMultipartEncoderTest extends TestCase
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
        $response = (new MultipartCreator('__X_BOUNDARY__'))->appendData($body);
        $handler = fopen('php://memory', 'rw');
        fwrite($handler, (string) $response);
        fseek($handler, 0);
        $response = (new MultipartReader(new Stream($handler)))->getBodyParts();
        $this->assertIsArray($response);
        $this->assertArrayHasKey(0, $response);
        $this->assertArrayHasKey('form', $response[0]);
        $this->assertIsArray($response[0]);
        $this->assertCount(2, $response[0]['form']);
        $this->assertArrayHasKey(1, $response);
        $this->assertIsArray($response[1]);
        $this->assertArrayHasKey('file', $response[1]);
        $this->assertCount(5, $response[1]['file']);
        $this->assertArrayHasKey('tmp_name', $response[1]['file']);
        $this->assertIsArray($response[1]['file']['tmp_name']);
        $this->assertCount(3, $response[1]['file']['tmp_name']);
    }

    protected function getData(): array
    {
        $file = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'composer.json';
        //print_r($file);exit;
        $files = [
            'form' => [
                'name' => "pepe",
                'surname' => "apellidos"
            ],
            'file' => [
                new CURLStringFile(file_get_contents($file), basename($file), 'application/json'),
                new CURLFile($file, 'application/json', basename($file)),
                '@' . $file
            ]
        ];
        return $files;
    }
}