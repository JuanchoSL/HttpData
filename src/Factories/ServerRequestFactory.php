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
            ->withUploadedFiles($_FILES??[])
            ->withCookieParams($_COOKIE??[])
            ->withQueryParams($_GET??[])
        ;

        foreach (getallheaders() as $key => $value) {
            $req = $req->withAddedHeader($key, $value);
        }

        return $req->withUri($uri)->withParsedBody($_POST);
    }
}