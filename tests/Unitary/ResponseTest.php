<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Bodies\Parsers\ResponseReader;
use JuanchoSL\HttpData\Containers\Response;
use JuanchoSL\HttpData\Factories\StreamFactory;
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
    public function testStringable()
    {
        $response = (new Response)->withStatus(200)->withHeader('Content-type', 'text/plain')->withBody((new StreamFactory())->createStream('hello'));
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaderLine('content-type'));
        $this->assertEquals('hello', (string) $response->getBody());
    }
    public function testParseable()
    {
        $response = <<<"EOH"
HTTP/2 200 
server: nginx/1.29.3
content-type: text/html; charset=UTF-8
content-length: 1331
x-powered-by: PHP/8.4.15
expires: Thu, 19 Nov 1981 08:52:00 GMT
cache-control: no-store, no-cache, must-revalidate
pragma: no-cache
set-cookie: TestCookie=The%20Cookie%20Value; expires=Sat, 20 Dec 2025 16:57:05 GMT; Max-Age=60; path=/; domain=host.docker.internal; secure; HttpOnly; SameSite=Strict
set-cookie: TestCookie=The%20Cookie%20Value; expires=Sat, 20 Dec 2025 16:57:05 GMT; Max-Age=60; path=/; domain=host.docker.internal; secure; HttpOnly; SameSite=Strict
date: Sat, 20 Dec 2025 16:56:05 GMT
vary: Accept-Encoding
content-encoding: gzip

hello
EOH;
        $stream = (new StreamFactory())->createStream($response);
        $response = new ResponseReader($stream);

        //echo "<pre>".print_r($response, true);
        $response = $response();
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
        $this->assertEquals('hello', (string) $response->getBody());
    }

}