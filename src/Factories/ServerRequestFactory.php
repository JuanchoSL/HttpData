<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\HttpData\Containers\ServerRequest;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = (new UriFactory)->createUri($uri);
        }
        $req = (new ServerRequest)
            ->withMethod($method)
            ->withProtocolVersion($_SERVER['SERVER_PROTOCOL'])
            ->withUploadedFiles((new UploadedFileFactory)->fromGlobals())
            ->withCookieParams($_COOKIE ?? [])
            ->withQueryParams($_GET ?? [])
        ;

        foreach (getallheaders() as $key => $value) {
            $req = $req->withAddedHeader($key, $value);
        }
        if (in_array(strtoupper($req->getMethod()), ['POST', 'PUT', 'PATCH'])) {
            $content_types = $req->getHeader('content-type');
            foreach ($content_types as $index => $content_type) {
                if (($length = strpos($content_type, ';')) !== false) {
                    $content_types[$index] = substr($content_type, 0, $length);
                }
            }
            if (in_array('application/x-www-form-urlencoded', $content_types) || in_array('multipart/form-data', $content_types)) {
                $body = $_POST;
            }
            if (!empty($input = file_get_contents('php://input'))) {
                $req = $req->withParsedBody($body)->withBody((new StreamFactory)->createStream($input));
            }
        }
        return $req->withUri($uri);
    }
}