<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{

    protected string $protocol_version;

    protected array $headers = [];

    protected StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->protocol_version;
    }

    public function withProtocolVersion($version): static
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

    public function hasHeader($name): bool
    {
        try {
            $this->findHeader($name);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function getHeader($name): array
    {
        $name = $this->findHeader($name);
        return $this->headers[$name];
    }

    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value): static
    {
        $new = clone $this;
        //$new->headers[$name] = [$value];
        //return $new;
        return $new->withoutHeader($name)->withAddedHeader($name, $value);
    }

    public function withAddedHeader($name, $value): static
    {
        $new = clone $this;
        try {
            $name = $new->findHeader($name);
        } catch (\Exception $e) {

        } finally {
            if (str_starts_with(strtolower($name), 'accept') && is_string($value)) {
                $value = explode(',', $value);
                foreach ($value as $header) {
                    $new->headers[$name][] = trim($header);
                }
            } else {
                $new->headers[$name][] = $value;
            }
        }
        return $new;
    }

    public function withoutHeader($name): static
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

    protected function findHeader($find)
    {
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === strtolower($find)) {
                return $name;
            }
        }
        throw new \InvalidArgumentException("The '$find' header does not exists");
    }
}