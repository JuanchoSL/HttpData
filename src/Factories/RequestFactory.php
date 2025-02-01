<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\HttpData\Containers\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RequestFactory implements RequestFactoryInterface
{

    public function createRequest(string $method, $uri): RequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = (new UriFactory)->createUri($uri);
        }
        return (new Request)->withMethod($method)->withUri($uri);
    }
}