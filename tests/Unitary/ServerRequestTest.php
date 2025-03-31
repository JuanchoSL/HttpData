<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Containers\ServerRequest;
use JuanchoSL\HttpData\Containers\Stream;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{

    public function testGet()
    {
        $query=['clave' => 'valor'];
        $req = (new ServerRequest)
            ->withMethod('GET')
            ->withProtocolVersion('1.1')
            ->withQueryParams($query)
            ;
            $this->assertEquals($query, $req->getQueryParams());
    }
    
    public function testPost()
    {
        $body_array = ['cuerpo' => 'dato'];
        $body_string = http_build_query($body_array);
        $body = (new StreamFactory)->createStream($body_string);
        parse_str($body, $post);
        $req = (new ServerRequest)
            ->withMethod('POST')
            ->withProtocolVersion('1.1')
            ->withQueryParams(['clave' => 'valor'])
            ->withBody($body)
            ->withParsedBody($post)
        ;
        $this->assertEquals($body_string, (string) $req->getBody());
        $this->assertEquals($body_array, $req->getParsedBody());
    }
    public function testPostBodyMultilevel()
    {
        $boundary = '__TEST_HTTP_DATA__';
        $body_array = ['form' => ['name' => 'pepe', 'surname' => 'apellidos']];
        $body_string = (string) (new MultipartCreator($boundary))->appendData($body_array);

        $handle = fopen("php://memory", "rw");
        fwrite($handle, $body_string);
        fseek($handle, 0);
        $body = (new StreamFactory)->createStream($body_string);

        $req = (new ServerRequest)
            ->withMethod('POST')
            ->withProtocolVersion('1.1')
            ->withQueryParams(['clave' => 'valor'])
            ->withBody($body)
                        ->withParsedBody((new MultipartReader(new Stream($handle)))->getBodyParams())
        ;

        $this->assertEquals($body_string, (string) $req->getBody());
        $this->assertEquals($body_array, $req->getParsedBody());
    }
    public function testPostBody()
    {
        $boundary = '__TEST_HTTP_DATA__';
        $body_array = ['name' => 'pepe', 'surname' => 'apellidos'];
        $body_string = (string) (new MultipartCreator(boundary: $boundary))->appendData($body_array);

        $handle = fopen("php://memory", "rw");
        fwrite($handle, $body_string);
        fseek($handle, 0);
        $body = (new StreamFactory)->createStream($body_string);

        $req = (new ServerRequest)
            ->withMethod('POST')
            ->withProtocolVersion('1.1')
            ->withQueryParams(['clave' => 'valor'])
            ->withBody($body)
            ->withParsedBody((new MultipartReader(new Stream($handle)))->getBodyParams())
        ;

        $this->assertEquals($body_string, (string) $req->getBody());
        $this->assertEquals($body_array, $req->getParsedBody());
    }
}