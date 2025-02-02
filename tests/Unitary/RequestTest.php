<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Containers\Request;
use JuanchoSL\HttpData\Factories\UriFactory;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function testWithMethod()
    {
        $request = (new Request)->withMethod('OPTIONS');
        foreach (['GET', 'POST', 'PUT', 'PATH', 'DELETE'] as $method) {
            $this->assertNotEquals($method, $request->getMethod());
            $request = $request->withMethod($method);
            $this->assertEquals($method, $request->getMethod());
        }
    }

    public function testWithTarget()
    {
        $request = (new Request)->withRequestTarget('X');
        foreach (['A', 'B', 'C'] as $method) {
            $this->assertNotEquals($method, $request->getRequestTarget());
            $request = $request->withRequestTarget($method);
            $this->assertEquals($method, $request->getRequestTarget());
        }
    }

    public function testWithUri()
    {
        $url = 'https://blog.tecnicosweb.com';
        $new_url = 'https://www.tecnicosweb.com';

        $request = new Request;
        $request = $request->withUri((new UriFactory)->createUri($url));
        $this->assertNotEquals($new_url, (string) $request->getUri());
        $request = $request->withUri((new UriFactory)->createUri($new_url));
        $this->assertEquals($new_url, (string) $request->getUri());
    }

    public function testWithHeader()
    {
        $request = (new Request)->withHeader('name', 'value');
        $this->assertTrue($request->hasHeader('name'));
        $this->assertEquals('value', current($request->getHeader('name')));
        $this->assertEquals('value', $request->getHeaderLine('name'));
        $this->assertTrue($request->hasHeader('NAME'));
        $this->assertEquals('value', current($request->getHeader('NAME')));
        $this->assertEquals('value', $request->getHeaderLine('NAME'));
    }
}