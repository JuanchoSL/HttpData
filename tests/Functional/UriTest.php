<?php

namespace JuanchoSL\HttpData\Tests\Functional;

use JuanchoSL\HttpData\Factories\UriFactory;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{

    public function testUri()
    {
        $uris = [
            "http://www.tecnicosweb.com",
            "http://www.tecnicosweb.com:8081",
            "http://usuario@www.tecnicosweb.com",
            "http://usuario:password@www.tecnicosweb.com",
            "http://usuario:password@www.tecnicosweb.com:8081",
            "http://usuario:password@www.tecnicosweb.com/status.php",
            "http://usuario:password@www.tecnicosweb.com/status.php#fragment",
            "http://usuario:password@www.tecnicosweb.com/subfolder/status.php",
            "http://usuario:password@www.tecnicosweb.com/subfolder/status.php#fragment",
        ];
        $factory = new UriFactory;
        foreach ($uris as $uri) {
            $this->assertEquals($uri, (string) $factory->createUri($uri));
        }
    }

    public function testUriStandardPort()
    {
        $uris = [
            "http://www.tecnicosweb.com:80",
            "https://www.tecnicosweb.com:443",
            "ftp://ftp.tecnicosweb.com:21"
        ];
        $factory = new UriFactory;
        foreach ($uris as $uri) {
            $result = (string) $factory->createUri($uri);
            $this->assertNotEquals($uri, $result);
            $this->assertStringStartsWith($result, $uri);
        }
    }
    public function testUriWithScheme()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com');
        $cloned = $original->withScheme('https');
        $this->assertNotEquals($original->getScheme(), $cloned->getScheme());
        $this->assertEquals('http', $original->getScheme());
        $this->assertEquals('https', $cloned->getScheme());
    }
    public function testUriWithHost()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com');
        $cloned = $original->withHost('www.google.com');
        $this->assertNotEquals($original->getHost(), $cloned->getHost());
        $this->assertEquals('www.tecnicosweb.com', $original->getHost());
        $this->assertEquals('www.google.com', $cloned->getHost());
    }
    public function testUriWithPort()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com:8080');
        $cloned = $original->withPort('8081');
        $this->assertNotEquals($original->getPort(), $cloned->getPort());
        $this->assertEquals('8080', $original->getPort());
        $this->assertEquals('8081', $cloned->getPort());
    }
    public function testUriWithPath()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withPath('/paginas/noticias');
        $this->assertNotEquals($original->getPath(), $cloned->getPath());
        $this->assertEquals('/paginas/servicios', $original->getPath());
        $this->assertEquals('/paginas/noticias', $cloned->getPath());
    }
    public function testUriWithFragment()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withFragment('fragment');
        $this->assertNotEquals($original->getFragment(), $cloned->getFragment());
        $this->assertEquals('', $original->getFragment());
        $this->assertEquals('fragment', $cloned->getFragment());
    }
    public function testUriWithUserinfo()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withUserInfo('fragment');
        $this->assertNotEquals($original->getUserInfo(), $cloned->getUserInfo());
        $this->assertEquals('', $original->getUserInfo());
        $this->assertEquals('fragment', $cloned->getUserInfo());
    }
    public function testUriWithUserinfoEncoder()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withUserInfo('@fragment');
        $this->assertNotEquals($original->getUserInfo(), $cloned->getUserInfo());
        $this->assertEquals('', $original->getUserInfo());
        $this->assertEquals(urlencode('@fragment'), $cloned->getUserInfo());
    }
    public function testUriWithUserinfoEncoderFactory()
    {
        $original = (new UriFactory)->createUri('http://user:pass&@www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withUserInfo('user', '@pass');
        $this->assertNotEquals($original->getUserInfo(), $cloned->getUserInfo());
        $this->assertEquals('user:' . urlencode('pass&'), $original->getUserInfo());
        $this->assertEquals('user:' . urlencode('@pass'), $cloned->getUserInfo());
    }
    public function testUriWithUserinfoEncoded()
    {
        $original = (new UriFactory)->createUri('http://www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withUserInfo('%40fragment');
        $this->assertNotEquals($original->getUserInfo(), $cloned->getUserInfo());
        $this->assertEquals('', $original->getUserInfo());
        $this->assertEquals('%40fragment', $cloned->getUserInfo());
    }
    public function testUriWithUserinfoEncodedFactory()
    {
        $original = (new UriFactory)->createUri('http://user:pass&@www.tecnicosweb.com/paginas/servicios');
        $cloned = $original->withUserInfo('user', '%40pass');
        $this->assertNotEquals($original->getUserInfo(), $cloned->getUserInfo());
        $this->assertEquals('user:' . urlencode('pass&'), $original->getUserInfo());
        $this->assertEquals('user:' . '%40pass', $cloned->getUserInfo());
    }
}