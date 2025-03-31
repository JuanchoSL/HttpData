<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\HttpData\Bodies\Parsers\UrlencodedReader;
use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\HttpData\Containers\ServerRequest;
use JuanchoSL\HttpData\Bodies\Parsers\MultipartReader;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RequestParseBodyException;

class ServerRequestFactory implements ServerRequestFactoryInterface
{

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return $this->init($method, $uri, $serverParams, (new StreamFactory)->createStreamFromResource(fopen("php://input", "rw")));
    }

    protected function init(string $method, $uri, array $serverParams, StreamInterface $body): ServerRequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = (new UriFactory)->createUri($uri);
        }
        mb_parse_str($uri->getQuery(), $_GET);
        $req = (new ServerRequest)
            ->withMethod($method)
            ->withProtocolVersion($_SERVER['SERVER_PROTOCOL'] ?? '1.1')
            ->withCookieParams($_COOKIE ?? [])
            ->withQueryParams($_GET ?? [])
            ->withUri($uri)
        ;

        $headers = (function_exists('getallheaders') && !empty(getallheaders())) ? getallheaders() : $this->getallheaders();
        foreach ($headers as $key => $value) {
            $req = $req->withAddedHeader($key, $value);
        }
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $req = $req->withBody($body);
            $req = $this->addBodyParsedData($req);
        }
        return $req;
    }

    public function fromGlobals(): ServerRequestInterface
    {
        $uri = array_key_exists('HTTPS', $_SERVER) && strtoupper($_SERVER['HTTPS']) == 'ON' ? 'https' : 'http';
        $uri .= '://';
        $uri .= $_SERVER['HTTP_HOST'];
        $uri .= $_SERVER['REQUEST_URI'];

        return $this->createServerRequest($_SERVER['REQUEST_METHOD'], $uri, $_SERVER);
    }
    public function fromRequest(RequestInterface $request): ServerRequestInterface
    {
        return $this->init($request->getMethod(), $request->getUri(), $_SERVER, $request->getBody());
    }

    protected function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    protected function addBodyParsedData(ServerRequestInterface $req)
    {
        $content_types = $req->getHeader('content-type');
        foreach ($content_types as $index => $content_type) {
            if (($length = strpos($content_type, ';')) !== false) {
                $content_types[$index] = substr($content_type, 0, $length);
            }
        }
        if (in_array('application/x-www-form-urlencoded', $content_types) || in_array('multipart/form-data', $content_types)) {
            if (($req->getMethod() != RequestMethodInterface::METHOD_POST || empty($_POST)) && $req->getBody()->getSize() > 0) {
                if (in_array('application/x-www-form-urlencoded', $content_types)) {
                    (new UrlencodedReader($req->getBody()))->toPostGlobals();
                } else {
                    try {
                        [$_POST, $_FILES] = request_parse_body();
                    } catch (RequestParseBodyException $e) {
                        (new MultipartReader($req->getBody()))->toPostGlobals();
                    }
                }
            }
            if (!empty($_FILES))
                $req = $req->withUploadedFiles((new UploadedFileFactory)->fromGlobals());
            $req = $req->withParsedBody($_POST ?? []);
        }
        return $req;
    }
}