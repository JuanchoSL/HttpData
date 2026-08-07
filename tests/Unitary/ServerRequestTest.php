<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Containers\Request;
use JuanchoSL\HttpData\Containers\ServerRequest;
use JuanchoSL\HttpData\Containers\Stream;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpData\Bodies\Creators\MultipartCreator;
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;
use JuanchoSL\HttpData\Factories\UriFactory;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{

    public function testGet()
    {
        $query = ['clave' => 'valor'];
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
    public function testPostBodyFile()
    {
        $boundary = '__TEST_HTTP_DATA__';
        $data = <<<EOH
'name','surname'
'pepe','lopez'
EOH;
        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . "file.csv", $data);
        $body_string = (string) (new MultipartCreator($boundary))->appendData(['file' => '@' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'file.csv']);

        $handle = fopen("php://memory", "rw");
        fwrite($handle, $body_string);
        fseek($handle, 0);
        $body = (new StreamFactory)->createStreamFromResource($handle);
        $new_body = (new MultipartReader($body));
        $req = (new ServerRequest)
            ->withMethod('POST')
            ->withProtocolVersion('1.1')
            ->withQueryParams(['clave' => 'valor'])
            ->withBody($body)
            ->withParsedBody($new_body->getBodyParams())
            ->withUploadedFiles($new_body->getBodyFiles())
        ;
        $this->assertEquals($body_string, (string) $req->getBody());
        $this->assertArrayHasKey('file', $req->getUploadedFiles());
        $this->assertArrayHasKey('name', current($req->getUploadedFiles()));
        $this->assertEquals('file.csv', current($req->getUploadedFiles())['name']);
    }

    public function testWithInvalidHeader()
    {
        $request = (new Request)->withUri((new UriFactory())->createUri('http://localhost/'))->withHeader("1name\r\n", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertFalse($server->hasHeader('1name'));
        $request = (new Request)->withUri((new UriFactory())->createUri('http://localhost/'))->withHeader("1name\r", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertFalse($server->hasHeader('1name'));

        $request = (new Request)->withUri((new UriFactory())->createUri('http://localhost/'))->withHeader("1name\n", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertFalse($server->hasHeader('1name'));

        $request = (new Request)->withUri((new UriFactory())->createUri('http://localhost/'))->withHeader("1name \0", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertFalse($server->hasHeader('1name'));
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('http://localhost/'))->withHeader("2name", "value\r\n a");
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertTrue($server->hasHeader('2name'));
        $this->assertEquals('value a', $server->getHeaderLine('2name'));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("3name", "value\r\n\ta");
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertTrue($server->hasHeader('3name'));
        $this->assertEquals('value a', $server->getHeaderLine('3name'));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("31name", "value\n");
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("32name", "value\r");
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("32name", "value\0");
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("32name", "value\r\n");
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("4name \0", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("4name \0", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("a\0a", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("a\20a", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));
        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("a\x00a", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("a\x1fa", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(1, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("a\x21a", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(2, count($server->getHeaders()));

        $request = (new Request)->withUri((new UriFactory())->createUri('hhtp://localhost/'))->withHeader("a\x21a", 'value');
        $server = (new ServerRequestFactory())->fromRequest($request);
        $this->assertLessThanOrEqual(2, count($server->getHeaders()));
    }
}