<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Containers\Response;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseReader extends MessageReader
{

    protected array $response = [];
    protected array $headers = [];
    protected array $cookies = [];

    public function __construct(StreamInterface $resource, ?string $boundary = null)
    {
        $exploded = (string) $resource;
        $exploded = str_replace("\r\n", "\r", $exploded);
        $exploded = str_replace("\n", "\r", $exploded);
        $exploded = explode("\r\r", $exploded, 2);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match('/^HTTP\/(\S+)\s(\d+)\s(.*)/', $headers, $request_head);
            list(, $this->response['protocol'], $this->response['status'], $this->response['reason']) = $request_head;

            parent::__construct($resource, $boundary);
            foreach ($this->getHeadersParams() as $header => $value) {
                if (strtolower($header) == 'set-cookie') {
                    $cookie_parts = explode(';', $value);
                    foreach ($cookie_parts as $cookie_part) {
                        if (strpos($cookie_part, '=') !== False) {
                            list($name, $data) = explode('=', $cookie_part);
                        } else {
                            $name = $cookie_part;
                            $data = true;
                        }

                        if (empty($cookie)) {
                            $cookie = [
                                'name' => trim($name),
                                'value' => trim($data)
                            ];
                        } else {
                            $cookie[trim($name)] = trim($data);
                        }
                    }
                    $this->cookies[] = $cookie;
                    unset($cookie);
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