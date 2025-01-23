<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Containers\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{

    public function testWithStatusCode()
    {
        $response = (new Response)->withStatus(100);
        foreach ([200, 401, 403, 404] as $code) {
            $this->assertNotEquals($code, (int) $response->getStatusCode());
            $response = $response->withStatus($code);
            $this->assertEquals($code, (int) $response->getStatusCode());
        }
    }

}