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
        if ($uri_parsed === false) {
            throw new \InvalidArgumentException("The uri: '{$uri}' cannot be parsed");
        }
        return (new Uri())
            ->withScheme($uri_parsed["scheme"] ?? "")
            ->withUserInfo(!empty($uri_parsed["user"]) ? urldecode($uri_parsed["user"]) : "", !empty($uri_parsed["pass"]) ? urldecode($uri_parsed["pass"]) : "")
            ->withHost($uri_parsed["host"] ?? "")
            ->withPort($uri_parsed["port"] ?? null)
            ->withPath($uri_parsed["path"] ?? "")
            ->withQuery($uri_parsed["query"] ?? "")
            ->withFragment($uri_parsed["fragment"] ?? "");
    }


    public function fromGlobals(): UriInterface
    {
        $uri = array_key_exists('HTTPS', $_SERVER) && strtoupper($_SERVER['HTTPS']) == 'ON' ? 'https' : 'http';
        $uri .= '://';
        foreach (['HTTP_HOST', 'SERVER_NAME', 'HOSTNAME'] as $target) {
            if (array_key_exists($target, $_SERVER)) {
                $uri .= $_SERVER[$target];
                break;
            }
        }
        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $uri .= $_SERVER['REQUEST_URI'];
        } else {
            foreach (['SCRIPT_URL', 'PATH_INFO'] as $target) {
                if (array_key_exists($target, $_SERVER)) {
                    $uri .= $_SERVER[$target];
                    break;
                }
            }
            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $this->createUri($uri);
    }

}