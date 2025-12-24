<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Contracts\BodyParsers;
use Psr\Http\Message\StreamInterface;

class RequestReader extends MessageReader implements BodyParsers
{

    protected string $url = '';
    protected array $request = [];
    protected array $server = [];
    protected array $headers = [];
    protected array $cookies = [];

    public function __construct(StreamInterface $resource, ?string $boundary = null)
    {
        $exploded = $this->fixLineBreaks($resource);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match('/^(\S+)\s(\S+)\sHTTP\/(.+)/', $headers, $request_head);
            list(, $this->server['REQUEST_METHOD'], $this->server['REQUEST_URI'], $this->server['SERVER_PROTOCOL']) = $request_head;
            if (($position = strpos($this->server['REQUEST_URI'], '?')) !== false) {
                $this->server['SCRIPT_URL'] = substr($this->server['REQUEST_URI'], 0, $position);
                $this->server['QUERY_STRING'] = substr($this->server['REQUEST_URI'], $position + 1);
            } else {
                $this->server['SCRIPT_URL'] = $this->server['QUERY_STRING'] = $this->server['REQUEST_URI'];
            }

            parent::__construct($resource, $boundary);
            foreach ($this->getHeadersParams() as $header => $value) {
                if (strtolower($header) == 'set-cookie') {
                    unset($this->headers[$header]);
                    continue;
                } elseif (strtolower($header) == 'cookie') {
                    $cookies = explode(';', $value);
                    foreach ($cookies as $cookie) {
                        list($name, $data) = explode('=', $cookie, 2);
                        $this->cookies[trim($name)] = trim($data);
                    }
                }
                $this->server["HTTP_" . str_replace("-", "_", strtoupper(trim($header)))] = trim($value);
            }

            if ($this->server['SERVER_PROTOCOL'] >= 2 || (array_key_exists('HTTP_ORIGIN', $this->server) && substr($this->server['HTTP_ORIGIN'], 0, 5) == 'https')) {
                $this->server['HTTPS'] = "on";
                $this->server['REQUEST_SCHEME'] = "https";
            }
        }
    }

    public function getRequestParams(): array
    {
        return [
            'method' => $this->server['REQUEST_METHOD'],
            'uri' => $this->server['REQUEST_URI'],
            'protocol' => number_format(floatval($this->server['SERVER_PROTOCOL']), 1, '.', ''),
        ];
    }

    public function getCookiesParams(): array
    {
        return $this->cookies;
    }

    public function getHeaderParts(): array
    {
        return [$this->getRequestParams(), $this->getHeadersParams(), $this->getCookiesParams()];
    }

    public function getServerVars(): array
    {
        return $this->server;
    }

    public function toPostGlobals(): void
    {
        $_COOKIE = $this->getCookiesParams();
        $_SERVER = array_merge($_SERVER, $this->getServerVars());
        mb_parse_str($this->server['QUERY_STRING'], $_GET);
        $this->getBody()?->toPostGlobals();
    }

}