<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Contracts\BodyParsers;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\StreamInterface;

class MessageReader
{

    protected array $headers = [];
    protected ?StreamInterface $body = null;

    public function __construct(StreamInterface $resource, ?string $boundary = null)
    {
        $exploded = explode("\r\n\r\n", (string) $resource, 2);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match_all('/(\S+):\s*(.+)/m', $headers, $first);
            $headers = array_combine($first[1], $first[2]);
            $headers = array_change_key_case($headers);
            foreach ($headers as $header => $value) {
                if (str_contains(strtolower($header), 'cookie')) {
                    continue;
                }
                $this->headers[ucfirst(strtolower(trim($header)))] = trim($value);
            }
            if (isset($exploded[1])) {
                $this->body = (new StreamFactory)->createStream($exploded[1]);
            }
        }
    }

    public function getHeadersParams(): array
    {
        return $this->headers;
    }

    public function getBodyStream(): ?StreamInterface
    {
        return $this->body;
    }

    public function getBody(): ?BodyParsers
    {
        $body = null;
        if (isset($this->headers['Content-type'], $this->body)) {
            if (strpos($this->headers['Content-type'], 'application/x-www-form-urlencoded') !== false) {
                $body = new UrlencodedReader($this->body);
            } elseif (strpos($this->headers['Content-type'], 'multipart/form-data') !== false) {
                $body = new MultipartReader($this->body);
            }
        }
        return $body;
    }
    public function getBodyParams(): array
    {
        return (is_object($this->getBody())) ? $this->getBody()->getBodyParams() : [];
    }
    public function getBodyParts(): array
    {
        return (is_object($this->getBody())) ? $this->getBody()->getBodyParts() : [];
    }

}