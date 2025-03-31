<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Bodies\Creators\UrlencodedCreator;
use PHPUnit\Framework\TestCase;

class BodyUrlEncoderTest extends TestCase
{

    public function testBodyWithPlainArray()
    {
        $body = ['clave' => 'valor'];
        $origin = http_build_query($body);
        $response = (new UrlencodedCreator)->appendData($body);

        $this->assertEquals($origin, (string) $response);
    }
    public function testBodyWithPlainArrayPartial()
    {
        $body = ['clave' => 'valor'];
        $origin = http_build_query($body);
        $response = (new UrlencodedCreator)->appendData(['clave' => ''])->appendData($body);

        $this->assertEquals($origin, (string) $response);
    }

    public function testBodyWithMultiAssocArray()
    {
        $body = ['clave' => ['subclave' => 'valor']];
        $origin = http_build_query($body);
        $response = (new UrlencodedCreator)->appendData($body);

        $this->assertEquals($origin, (string) $response);
    }
    public function testBodyWithMultiAssocArrayPartial()
    {
        $body = ['clave' => ['subclave' => 'valor']];
        $origin = http_build_query($body);
        $response = (new UrlencodedCreator)->appendData(['clave' => []])->appendData($body);

        $this->assertEquals($origin, (string) $response);
    }

    public function testBodyWithMultiIndexedArray()
    {
        $body = ['clave' => ['subclave', 'valor']];
        $origin = http_build_query($body);
        $response = (new UrlencodedCreator)->appendData($body);

        $this->assertEquals($origin, (string) $response);
    }
    public function testBodyWithMultiIndexedArrayPartial()
    {
        $body = ['clave' => ['subclave', 'valor']];
        $origin = http_build_query($body);
        $obj = new \stdClass;
        $obj->clave = ['subclave', 'valor'];
        $response = (new UrlencodedCreator)->appendData(['clave' => []])->appendData((array) $obj);

        $this->assertEquals($origin, (string) $response);
    }

}