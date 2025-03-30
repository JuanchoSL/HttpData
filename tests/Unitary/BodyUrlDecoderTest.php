<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use PHPUnit\Framework\TestCase;

class BodyUrlDecoderTest extends TestCase
{

    public function testBodyWithPlainArray()
    {
        $origin = ['clave' => 'valor'];
        $response = [];
        mb_parse_str(http_build_query($origin), $response);
        $this->assertEquals($origin, $response);
    }

    public function testBodyWithMultiArray()
    {
        $origin = ['clave' => ['subclave' => 'valor']];
        $response = [];
        mb_parse_str(http_build_query($origin), $response);
        $this->assertEquals($origin, $response);
    }

}