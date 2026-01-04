<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\DataManipulation\Manipulators\Strings\StringsManipulators;
use JuanchoSL\HttpData\Contracts\BodyParsers;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\StreamInterface;

class MessageReader
{

    protected array $headers = [];
    protected ?StreamInterface $body = null;

    public function __construct(StreamInterface $resource, ?string $boundary = null)
    {
        $exploded = explode(PHP_EOL . PHP_EOL, (new StringsManipulators((string) $resource))->eol(PHP_EOL)->__tostring(), 2);
        if (isset($exploded[0])) {
            $headers = $exploded[0];
            preg_match_all('/(\S+):\s*(.+)/m', $headers, $first);

            $this->headers = [];
            foreach ($first[1] as $index => $name) {
                $name = (new StringsManipulators($name))->trim()->toLower()->toUpperWords('-')->__tostring();
                if (array_key_exists($name, $this->headers) && is_string($this->headers[$name])) {
                    $this->headers[$name] = [$this->headers[$name]];
                    $this->headers[$name][] = trim($first[2][$index]);
                } else {
                    $this->headers[$name] = trim($first[2][$index]);
                }
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
        if (isset($this->headers['Content-Type'], $this->body)) {
            if (strpos($this->headers['Content-Type'], 'application/x-www-form-urlencoded') !== false) {
                $body = new UrlencodedReader($this->body);
            } elseif (strpos($this->headers['Content-Type'], 'multipart/form-data') !== false) {
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