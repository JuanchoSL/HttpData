<?php

namespace JuanchoSL\HttpData\Tests\Functional;

use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\UriFactory;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function testRequest()
    {
        $factory = new RequestFactory;
        foreach (['GET', 'POST', 'PUT', 'PATH', 'DELETE'] as $method) {
            $request = $factory->createRequest($method, 'https://blog.tecnicosweb.com');
            $this->assertEquals($method, $request->getMethod());
        }
    }

    public function testWithMethod()
    {
        $factory = new RequestFactory;
        foreach (['GET', 'POST', 'PUT', 'PATH', 'DELETE'] as $method) {
            $request = $factory->createRequest($method, 'https://blog.tecnicosweb.com');
            $this->assertEquals($method, $request->getMethod());
            $this->assertEquals('OPTIONS', $request->withMethod('OPTIONS')->getMethod());
        }
    }

    public function testWithUri()
    {
        $url = 'https://blog.tecnicosweb.com';
        $new_url = 'https://www.tecnicosweb.com';
        $factory = new RequestFactory;
        $request = $factory->createRequest('GET', $url);
        $this->assertEquals($url, (string) $request->getUri());
        $this->assertEquals($new_url, (string) $request->withUri((new UriFactory)->createUri($new_url))->getUri());
    }

    public function testWithRequestTarget()
    {
        $target = "/section?param=value";
        $url = 'https://blog.tecnicosweb.com';
        $factory = new RequestFactory;
        $uri = (new UriFactory())->createUri($url)->withPath($target);
        $request = $factory->createRequest('GET', $uri);
        $this->assertEquals($target, (string) $request->getRequestTarget());
        $this->assertEquals($target, (string) $request->getUri()->getPath());
        $request = $request->withRequestTarget('/another');
        $this->assertEquals('/another', (string) $request->getRequestTarget());
        $this->assertNotEquals('/another', (string) $request->getUri()->getPath());
        $this->assertEquals($target, (string) $request->getUri()->getPath());
    }

}