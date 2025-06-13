<?php

namespace JuanchoSL\HttpData\Tests\Functional;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestTest extends TestCase
{

    public function testGet()
    {
        $query = ["clave" => "valor"];
        foreach ([RequestMethodInterface::METHOD_GET] as $method) {
            $req = (new RequestFactory)->createRequest($method, 'http://localhost?' . http_build_query($query))
                ->withProtocolVersion('1.1')
            ;
            $req = (new ServerRequestFactory)->fromRequest($req);

            $this->assertEquals($query, $req->getQueryParams());
        }
    }

    public function testGetFromGlobalsRequest()
    {
        $_SERVER['HTTPS'] = 'OFF';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/usercase?' . http_build_query([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);

        $request = (new ServerRequestFactory)->fromGlobals();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals(sprintf('http://%s/usercase?%s', 'localhost', http_build_query(['required_void' => 1, 'required_multi' => ['a', 'b', 'c']])), (string) $request->getUri());
        $attributes = $request->getQueryParams();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('required_multi', $attributes);
        $multi = $attributes['required_multi'];
        $this->assertIsArray($multi);
        $this->assertContains('a', $multi);
        $this->assertContains('b', $multi);
        $this->assertContains('c', $multi);
    }

    public function testGetFromGlobalsScript()
    {
        $_SERVER['HTTPS'] = 'OFF';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/usercase';
        $_SERVER['QUERY_STRING'] = http_build_query([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);

        $request = (new ServerRequestFactory)->fromGlobals();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals(sprintf('http://%s/usercase?%s', 'localhost', http_build_query(['required_void' => 1, 'required_multi' => ['a', 'b', 'c']])), (string) $request->getUri());
        $attributes = $request->getQueryParams();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('required_multi', $attributes);
        $multi = $attributes['required_multi'];
        $this->assertIsArray($multi);
        $this->assertContains('a', $multi);
        $this->assertContains('b', $multi);
        $this->assertContains('c', $multi);
    }
    public function testPost()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $body_array = ['cuerpo' => 'dato'];
        $body_string = http_build_query($body_array);
        $body = (new StreamFactory)->createStream($body_string);
        foreach ([RequestMethodInterface::METHOD_POST, RequestMethodInterface::METHOD_PUT, RequestMethodInterface::METHOD_PATCH] as $method) {
            $req = (new RequestFactory)->createRequest($method, 'http://localhost')
                ->withProtocolVersion('1.1')
                ->withBody($body)
                ->withAddedHeader('content-type', 'application/x-www-form-urlencoded')
            ;
            $req = (new ServerRequestFactory)->fromRequest($req);

            $this->assertEquals($body_string, (string) $req->getBody(), $method);
            $this->assertEquals($body_array, $req->getParsedBody(), $method);
        }
    }
    public function testPostBodyMultilevel()
    {
        $boundary = '__TEST_HTTP_DATA__';
        $_SERVER['HTTP_CONTENT_TYPE'] = "multipart/form-data; boundary={$boundary}";
        $body_array = ['form' => ['name' => 'pepe', 'surname' => 'apellidos']];
        $body_string = (string) (new MultipartCreator($boundary))->appendData($body_array);
        $body = (new StreamFactory)->createStream($body_string);
        foreach ([RequestMethodInterface::METHOD_POST, RequestMethodInterface::METHOD_PUT, RequestMethodInterface::METHOD_PATCH] as $method) {
            $req = (new RequestFactory)->createRequest($method, 'http://localhost')
                ->withProtocolVersion('1.1')
                ->withAddedHeader("content-type", "multipart/form-data; boundary={$boundary}")
                ->withBody($body)
            ;
            $req = (new ServerRequestFactory)->fromRequest($req);

            $this->assertEquals($body_string, (string) $req->getBody(), $method);
            $this->assertEquals($body_array, $req->getParsedBody(), $method);
        }
    }
    public function testPostBody()
    {
        $boundary = '__TEST_HTTP_DATA__';
        $_SERVER['HTTP_CONTENT_TYPE'] = "multipart/form-data; boundary={$boundary}";
        $body_array = ['name' => 'pepe', 'surname' => 'apellidos'];
        $body_string = (string) (new MultipartCreator($boundary))->appendData($body_array);
        $body = (new StreamFactory)->createStream($body_string);
        foreach ([RequestMethodInterface::METHOD_POST, RequestMethodInterface::METHOD_PUT, RequestMethodInterface::METHOD_PATCH] as $method) {
            $req = (new RequestFactory)->createRequest($method, 'http://localhost')
                ->withProtocolVersion('1.1')
                ->withAddedHeader("content-type", "multipart/form-data; boundary={$boundary}")
                ->withBody($body)
            ;
            $req = (new ServerRequestFactory)->fromRequest($req);

            $this->assertEquals($body_string, (string) $req->getBody(), $method);
            $this->assertEquals($body_array, $req->getParsedBody(), $method);
        }
    }
}