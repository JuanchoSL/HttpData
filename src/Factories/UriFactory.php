<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\HttpData\Containers\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface
    {
        $uri_parsed = parse_url($uri);
        return (new Uri())
            ->withScheme($uri_parsed["scheme"] ?? "")
            ->withUserInfo($uri_parsed["user"] ?? "", $uri_parsed["pass"] ?? "")
            ->withHost($uri_parsed["host"] ?? "")
            ->withPort($uri_parsed["port"] ?? null)
            ->withPath($uri_parsed["path"] ?? "")
            ->withQuery($uri_parsed["query"] ?? "")
            ->withFragment($uri_parsed["fragment"] ?? "");
    }
}