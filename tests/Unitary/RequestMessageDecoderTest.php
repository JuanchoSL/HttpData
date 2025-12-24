<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\HttpData\Bodies\Parsers\RequestReader;
use JuanchoSL\HttpData\Factories\StreamFactory;
use PHPUnit\Framework\TestCase;

class RequestMessageDecoderTest extends TestCase
{

    public function testGet()
    {
        $message = <<<"EOH"
GET /data HTTP/2
Host: host.docker.internal
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: es-ES,es;q=0.9,en-US;q=0.8,en;q=0.7
Accept-Encoding: gzip, deflate, br, zstd
Connection: keep-alive
Referer: https://host.docker.internal/
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i
Pragma: no-cache
Cache-Control: no-cache
TE: trailers
EOH;

        $message = new RequestReader((new StreamFactory())->createStream($message));
        $request = $message->getRequestParams();
        $this->assertEquals(RequestMethodInterface::METHOD_GET, $request['method']);
        $this->assertEquals("/data", $request['uri']);
        $this->assertEquals(2, $request['protocol']);
    }

    public function testGetWithParams()
    {
        $message = <<<"EOH"
GET /data?a=b HTTP/2
Host: host.docker.internal
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: es-ES,es;q=0.9,en-US;q=0.8,en;q=0.7
Accept-Encoding: gzip, deflate, br, zstd
Connection: keep-alive
Referer: https://host.docker.internal/
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i
Pragma: no-cache
Cache-Control: no-cache
TE: trailers
EOH;

        $message = new RequestReader((new StreamFactory())->createStream($message));
        $request = $message->getRequestParams();
        $this->assertEquals(RequestMethodInterface::METHOD_GET, $request['method']);
        $this->assertEquals("/data?a=b", $request['uri']);
        $this->assertEquals(2, $request['protocol']);
    }

    public function testPostUrlEncode()
    {
        $message = <<<"EOH"
POST /whois HTTP/2
Host: host.docker.internal
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: es-ES,es;q=0.9,en-US;q=0.8,en;q=0.7
Accept-Encoding: gzip, deflate, br, zstd
Content-Type: application/x-www-form-urlencoded
Content-Length: 85
Origin: https://host.docker.internal
Connection: keep-alive
Referer: https://host.docker.internal/whois
Cookie: PHPSESSID=e366f2348c2c0b7573fabd766fc1d1cb
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i
Pragma: no-cache
Cache-Control: no-cache

domain=www.tecnicosweb.com&csrf_token=0121rrn0ro3q678q085455o27r1622pp&submit=Revisar
EOH;
        $message = new RequestReader((new StreamFactory())->createStream($message));
        $request = $message->getRequestParams();
        $this->assertEquals(RequestMethodInterface::METHOD_POST, $request['method']);
        $this->assertEquals("/whois", $request['uri']);
        $this->assertEquals(2, $request['protocol']);

        $headers = $message->getHeadersParams();
        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-type', $headers);

        $post = $message->getBodyParams();
        $this->assertIsArray($post);
        $this->assertArrayHasKey('domain', $post);
        $this->assertEquals('www.tecnicosweb.com', $post['domain']);
        $this->assertArrayHasKey('csrf_token', $post);
        $this->assertArrayHasKey('submit', $post);
    }

    public function testPostUrlMultipart()
    {

        $message = <<<"EOH"
POST /data/converter HTTP/2
Host: host.docker.internal
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: es-ES,es;q=0.9,en-US;q=0.8,en;q=0.7
Accept-Encoding: gzip, deflate, br, zstd
Content-Type: multipart/form-data; boundary=----geckoformboundary79f4e3be2c91314ae7979b2f43e245af
Content-Length: 1726
Origin: https://host.docker.internal
Connection: keep-alive
Referer: https://host.docker.internal/data/converter
Cookie: PHPSESSID=4db91aac71366767a09d9772e2882815;Valor=Dato
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i
Pragma: no-cache

------geckoformboundary79f4e3be2c91314ae7979b2f43e245af
Content-Disposition: form-data; name="input"; filename="Salaria_ABM_20251117135000.csv"
Content-Type: text/csv

clientID,period,value
"583ef6329df6b","2016-01","37232"
"583sasda9asda","2016-02","36537"
------geckoformboundary79f4e3be2c91314ae7979b2f43e245af
Content-Disposition: form-data; name="output"


------geckoformboundary79f4e3be2c91314ae7979b2f43e245af
Content-Disposition: form-data; name="csrf_token"

q8470soo63po9s78114294o3nr87q836
------geckoformboundary79f4e3be2c91314ae7979b2f43e245af
Content-Disposition: form-data; name="submit"

Convertir
------geckoformboundary79f4e3be2c91314ae7979b2f43e245af--

EOH;

        $message = new RequestReader((new StreamFactory())->createStream($message));
        $request = $message->getRequestParams();
        $this->assertEquals(RequestMethodInterface::METHOD_POST, $request['method']);
        $this->assertEquals("/data/converter", $request['uri']);
        $this->assertEquals(2, $request['protocol']);

        [$post, $files] = $message->getBodyParts();
        //$post = $message->getBodyParams();
        $this->assertIsArray($post);
        $this->assertArrayHasKey('submit', $post);
        $this->assertArrayHasKey('csrf_token', $post);

        //$files = $message->getBodyFiles();
        $this->assertIsArray($files);
        $this->assertArrayHasKey('input', $files);
        $this->assertIsArray($files['input']);
        $this->assertArrayHasKey('name', $files['input']);
        $this->assertArrayHasKey('type', $files['input']);
        $this->assertArrayHasKey('size', $files['input']);
        $this->assertArrayHasKey('error', $files['input']);
    }
}