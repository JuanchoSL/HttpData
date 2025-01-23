<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Containers\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{

    public function testUriStandardPort()
    {
        $uri_object = new Uri;
        foreach (['https' => 443, 'http' => 80, 'ftp' => 21] as $scheme => $port) {
            $uri_object = $uri_object->withScheme($scheme)->withPort($port);
            $this->assertEmpty($uri_object->getPort());
            $this->assertEquals($scheme, $uri_object->getScheme());
        }
    }
    public function testUriWithScheme()
    {
        $original = (new Uri)->withScheme('http');
        $cloned = $original->withScheme('https');
        $this->assertNotEquals($original->getScheme(), $cloned->getScheme());
        $this->assertEquals('http', $original->getScheme());
        $this->assertEquals('https', $cloned->getScheme());
    }
    public function testUriWithHost()
    {
        $original = (new Uri)->withHost('www.tecnicosweb.com');
        $cloned = $original->withHost('www.google.com');
        $this->assertNotEquals($original->getHost(), $cloned->getHost());
        $this->assertEquals('www.tecnicosweb.com', $original->getHost());
        $this->assertEquals('www.google.com', $cloned->getHost());
    }
    public function testUriWithPort()
    {
        $original = (new Uri)->withPort('8080');
        $cloned = $original->withPort('8081');
        $this->assertNotEquals($original->getPort(), $cloned->getPort());
        $this->assertEquals('8080', $original->getPort());
        $this->assertEquals('8081', $cloned->getPort());
    }
    public function testUriWithPath()
    {
        $original = (new Uri)->withPath('/paginas/servicios');
        $cloned = $original->withPath('/paginas/noticias');
        $this->assertNotEquals($original->getPath(), $cloned->getPath());
        $this->assertEquals('/paginas/servicios', $original->getPath());
        $this->assertEquals('/paginas/noticias', $cloned->getPath());
    }
    public function testUriWithFragment()
    {
        $original = (new Uri)->withFragment('tag1');
        $cloned = $original->withFragment('tag2');
        $this->assertNotEquals($original->getFragment(), $cloned->getFragment());
        $this->assertEquals('tag1', $original->getFragment());
        $this->assertEquals('tag2', $cloned->getFragment());
    }
}