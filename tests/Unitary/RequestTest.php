<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use Fig\Http\Message\RequestMethodInterface;
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
    public function testWithTargetAsteriskForm()
    {
        $request = (new Request)->withUri((new UriFactory())->createUri('http://aaa.com:80/path/'))->withRequestTarget('*');
        $this->assertEquals('*', $request->getRequestTarget());
        $request = (new Request)->withUri((new UriFactory())->createUri('http://aaa.com:80/path/'))->withRequestTarget('/');
        $this->assertEquals('/', $request->getRequestTarget());
        $request = (new Request)->withUri((new UriFactory())->createUri('http://aaa.com:80'))->withMethod(RequestMethodInterface::METHOD_OPTIONS);
        $this->assertEquals('*', $request->getRequestTarget());
        $request = (new Request)->withUri((new UriFactory())->createUri('http://aaa.com:80/'))->withMethod(RequestMethodInterface::METHOD_OPTIONS);
        $this->assertNotEquals('*', $request->getRequestTarget());
        $this->assertEquals('/', $request->getRequestTarget());
        $request = (new Request)->withUri((new UriFactory())->createUri('http://aaa.com:80/path/'))->withMethod(RequestMethodInterface::METHOD_OPTIONS);
        $this->assertNotEquals('*', $request->getRequestTarget());
        $this->assertEquals('/path/', $request->getRequestTarget());
    }

    public function testWithUri()
    {
        $url = (new UriFactory)->createUri('https://blog.tecnicosweb.com');
        $new_url = (new UriFactory)->createUri('https://www.tecnicosweb.com');

        $request = new Request;
        $request = $request->withUri($url);
        $this->assertNotEquals((string) $new_url, (string) $request->getUri());
        $request = $request->withUri($new_url);
        $this->assertEquals((string) $new_url, (string) $request->getUri());

        $request = new Request;
        $request = $request->withUri($url);
        $this->assertEquals($url->getHost(), (string) $request->getHeaderLine('host'));
        $request = $request->withUri($new_url, true);
        $this->assertEquals($url->getHost(), (string) $request->getHeaderLine('host'));
        $request = $request->withUri($new_url, false);
        $this->assertEquals($new_url->getHost(), (string) $request->getHeaderLine('host'));
        $request = $request->withoutHeader('host');
        $this->assertFalse($request->hasHeader('host'));
        $this->assertNotEquals($new_url->getHost(), (string) $request->getHeaderLine('host'));
        $request = $request->withUri($url, true);
        $this->assertTrue($request->hasHeader('host'));
        $this->assertEquals($url->getHost(), (string) $request->getHeaderLine('host'));
        $request = $request->withUri($new_url->withHost(''), false);
        $this->assertEquals($url->getHost(), (string) $request->getHeaderLine('host'));
        $request = $request->withUri($new_url->withHost(''), true);
        $this->assertEquals($url->getHost(), (string) $request->getHeaderLine('host'));
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