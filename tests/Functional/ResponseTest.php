<?php

namespace JuanchoSL\HttpData\Tests\Functional;

use JuanchoSL\HttpData\Factories\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{

    public function testResponse()
    {
        $factory = new ResponseFactory;
        foreach ([200, 401, 403, 404] as $code) {
            $response = $factory->createResponse($code);
            $this->assertEquals($code, (int) $response->getStatusCode());
        }
    }

    public function testWithStatusCode()
    {
        $factory = new ResponseFactory;
        foreach ([200, 401, 403, 404] as $code) {
            $response = $factory->createResponse($code);
            $this->assertEquals($code, (int) $response->getStatusCode());
            $this->assertEquals(100, (int) $response->withStatus(100)->getStatusCode());
        }
    }

}