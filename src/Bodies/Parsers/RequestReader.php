<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Containers\Request;
use JuanchoSL\HttpData\Contracts\BodyParsers;
use Psr\Http\Message\RequestInterface;
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
        $exploded = explode(PHP_EOL . PHP_EOL, (new StringsManipulators((string) $resource))->eol(PHP_EOL)->__tostring(), 2);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match('/^(\S+)\s(\S+)\sHTTP\/(.+)/', $headers, $request_head);
            list(, $this->server['REQUEST_METHOD'], $this->server['REQUEST_URI'], $this->server['SERVER_PROTOCOL']) = $request_head;
            if (($position = strpos($this->server['REQUEST_URI'], '?')) !== false) {
                $this->server['SCRIPT_URL'] = (new StringsManipulators($this->server['REQUEST_URI'])->trim()->substring(0, $position))->__tostring();
                $this->server['QUERY_STRING'] = (new StringsManipulators($this->server['REQUEST_URI'])->trim()->substring($position + 1))->__tostring();
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
                $header = (new StringsManipulators($header))->trim()->toUpper()->preppend('HTTP', '-')->replace('-', '_')->__tostring();
                $this->server[$header] = trim($value);
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

    public function __invoke(): RequestInterface
    {
        $req_params = $this->getRequestParams();
        $request = (new Request())
            ->withMethod($req_params['method'])
            ->withRequestTarget($req_params['uri'])
            ->withProtocolVersion($req_params['protocol'])
            ->withBody($this->getBodyStream())
        ;
        foreach ($this->getHeadersParams() as $key => $values) {
            if (!is_iterable($values)) {
                $values = [$values];
            }
            foreach ($values as $value) {
                $request = $request->withAddedHeader($key, $value);
            }
        }
        foreach ($this->getCookiesParams() as $key => $values) {
            if (!is_iterable($values)) {
                $values = [$values];
            }
            foreach ($values as $value) {
                $request = $request->withAddedHeader('cookie', $value);
            }
        }
        return $request;
    }
}