<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\HttpData\Containers\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
{

    public function createRequest(string $method, $uri): RequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = (new UriFactory)->createUri($uri);
        }
        return (new Request)->withMethod($method)->withUri($uri);
    }

    public function fromFile(string $filepath): RequestInterface
    {
        return $this->fromRawString(file_get_contents($filepath));
    }

    public function fromInputStream($input): RequestInterface
    {
        return $this->fromRawString(stream_get_contents($input));
    }

    public function fromRawString(string $raw_string): RequestInterface
    {
        $exploded = explode("\r\n\r\n", $raw_string, 2);
        if (count($exploded) == 1) {
            $exploded[] = null;
        }
        list($headers, $body) = $exploded;
        preg_match('/^(\S+)\s(\S+)\sHTTP\/(.+)/', $headers, $request_head);
        preg_match_all('/(\S+):\s*(.+)/m', $headers, $first);
        $headers = array_combine($first[1], $first[2]);
        $headers = array_change_key_case($headers);

        $url = (isset($request_head[3]) && $request_head[3] >= 2) ? 'https://' : 'http://';
        $url .= (array_key_exists('host', $headers)) ? trim($headers['host']) : '';
        $url .= $request_head[2] ?? '';

        $url = (new UriFactory())->createUri($url);
        $request = $this->createRequest($request_head[1], $url)->withProtocolVersion($request_head[3]);

        foreach ($headers as $header => $value) {
            if (strtolower($header) == 'cookie') {
                $cookies = explode(';', $value);
                foreach ($cookies as $cookie) {
                    list($name, $data) = explode('=', $cookie, 2);
                    $_COOKIE[trim($name)] = trim($data);
                }
                continue;
            }
            $request = $request->withHeader(trim($header), trim($value));
        }
        if (!empty($body)) {
            $stream = (new StreamFactory())->createStream($body);
            $request = $request->withBody($stream);
        }
        return $request;
    }
}