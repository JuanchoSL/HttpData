<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseReader extends MessageReader
{

    protected array $response = [];
    protected array $headers = [];
    protected array $cookies = [];

    public function __construct(StreamInterface $resource, ?string $boundary = null)
    {
        $exploded = explode(PHP_EOL . PHP_EOL, (new StringsManipulators((string) $resource))->eol(PHP_EOL)->__tostring(), 2);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match('/^HTTP\/(\S+)\s(\d+)\s(.*)/', $headers, $request_head);
            list(, $this->response['protocol'], $this->response['status'], $this->response['reason']) = $request_head;

            parent::__construct($resource, $boundary);
            foreach ($this->getHeadersParams() as $header => $value) {
                if (strtolower($header) == 'cookie') {
                    unset($this->headers[$header]);
                    continue;
                } elseif (strtolower($header) == 'set-cookie') {
                    if (!is_iterable($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $val) {
                        $cookie = new CookieReader($val);
                        $this->cookies[] = $cookie();
                        unset($cookie);
                    }
                }
            }
        }
    }

    public function getResponseParams(): array
    {
        return $this->response;
    }

    public function getCookiesParams(): array
    {
        return $this->cookies;
    }

    public function getHeaderParts(): array
    {
        return [$this->getResponseParams(), $this->getHeadersParams(), $this->getCookiesParams()];
    }

    public function __invoke(): ResponseInterface
    {
        $params = $this->getResponseParams();
        $response = (new ResponseFactory())->createResponse(+$params['status'], $params['reason'] ?? '')->withBody($this->getBodyStream());
        foreach ($this->getHeadersParams() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        return $response;
    }
}