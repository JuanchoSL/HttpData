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
        $exploded = $this->fixLineBreaks($resource);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match_all('/(\S+):\s*(.+)/m', $headers, $first);
            $headers = array_combine($first[1], $first[2]);
            $headers = array_change_key_case($headers);
            foreach ($headers as $header => $value) {
                $this->headers[ucfirst(strtolower(trim($header)))] = trim($value);
            }
        }
        $this->body = (new StreamFactory)->createStream($exploded[1] ?? '');
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

    protected function fixLineBreaks(StreamInterface $stream): array
    {
        $exploded = (string) $stream;
        if (PHP_EOL == "\r\n") {
            $exploded = str_replace("\r\r", PHP_EOL . PHP_EOL, $exploded);
            $exploded = str_replace("\n\n", PHP_EOL . PHP_EOL, $exploded);
        } else {
            $exploded = str_replace("\r\n\r\n", PHP_EOL . PHP_EOL, $exploded);
            if (PHP_EOL == "\n") {
                $exploded = str_replace("\r\r", PHP_EOL . PHP_EOL, $exploded);
            } else {
                $exploded = str_replace("\n\n", PHP_EOL . PHP_EOL, $exploded);
            }
        }

        return $exploded = explode(PHP_EOL . PHP_EOL, $exploded, 2);
    }
}