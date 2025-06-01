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

    /**
     * Summary of createServerRequest
     * @param string $method
     * @param mixed $uri
     * @param array<string, mixed> $server_params
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $server_params = []): ServerRequestInterface
    {
        $headers = (function_exists('getallheaders') && !empty(getallheaders())) ? getallheaders() : $this->getallheaders();
        return $this->init($method, $uri, $server_params, (new StreamFactory)->createStreamFromResource(fopen("php://input", "rw")), $headers);
    }

    /**
     * Summary of init
     * @param string $method
     * @param mixed $uri
     * @param array<string, mixed> $server_params
     * @param \Psr\Http\Message\StreamInterface $body
     * @param array<string, mixed> $headers
     * @return ServerRequest|ServerRequestInterface
     */
    protected function init(string $method, $uri, array $server_params, StreamInterface $body, array $headers = []): ServerRequestInterface
    {
        foreach (['SERVER_PROTOCOL' => '1.1'] as $server_index => $default) {
            $server_params[$server_index] ??= $_SERVER[$server_index] ?? $default;
        }
        if (!$uri instanceof UriInterface) {
            $uri = (new UriFactory)->createUri($uri);
        }
        mb_parse_str($uri->getQuery(), $_GET);
        $req = (new ServerRequest)
            ->withMethod($method)
            ->withProtocolVersion($server_params['SERVER_PROTOCOL'])
            ->withCookieParams($_COOKIE ?? [])
            ->withQueryParams($_GET ?? [])
            ->withUri($uri)
        ;

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
        return $this->init($request->getMethod(), $request->getUri(), ['SERVER_PROTOCOL' => $request->getProtocolVersion()], $request->getBody(), $request->getHeaders());
    }

    /**
     * Summary of getallheaders
     * @return array<string, mixed>
     */
    protected function getallheaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    protected function addBodyParsedData(ServerRequestInterface $req): ServerRequestInterface
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
                } elseif (version_compare(PHP_VERSION, '8.4.0', '<')) {
                    (new MultipartReader($req->getBody()))->toPostGlobals();
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