<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{

    protected string $protocol_version = '1.1';

    protected array $headers = [];

    protected StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->protocol_version;
    }

    public function withProtocolVersion(string $version): static
    {
        if (strpos($version, '/') !== false) {
            $version = substr($version, strpos($version, '/') + 1);
        }
        $new = clone $this;
        $new->protocol_version = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        try {
            $this->findHeader($name);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function getHeader(string $name): array
    {
        try {
            $name = $this->findHeader($name);
            return $this->headers[$name];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): static
    {
        $new = clone $this;
        return $new->withoutHeader($name)->withAddedHeader($name, $value);
    }

    public function withAddedHeader(string $name, $value): static
    {
        $new = clone $this;
        try {
            $name = $new->findHeader($name);
        } catch (\Exception $e) {
            $new->headers[$name] = [];
        } finally {
            if (!is_iterable($value)) {
                $value = explode(',', $value);
            }
            foreach ($value as $header) {
                $new->headers[$name][] = trim($header);
            }
        }
        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        try {
            $name = $new->findHeader($name);
            unset($new->headers[$name]);
        } catch (\Exception $e) {

        }
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body ?? (new StreamFactory)->createStream();
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    protected function findHeader(string $find): string
    {
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === strtolower($find)) {
                return $name;
            }
        }
        throw new \InvalidArgumentException("The '{$find}' header does not exists");
    }
}