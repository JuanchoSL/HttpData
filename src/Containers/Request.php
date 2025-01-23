<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    protected string $target;
    protected string $method;
    protected UriInterface $uri;

    public function getRequestTarget(): string
    {
        return $this->target ?? $this->uri->getPath() . $this->uri->getQuery();
    }

    public function withRequestTarget($requestTarget): static
    {
        $new = clone $this;
        $new->target = $requestTarget;
        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): static
    {
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;
        if (!$preserveHost || !$this->hasHeader('host')) {
            if (!empty($host = $uri->getHost())) {


                if ($uri->getPort()) {
                    $host .= ':' . $uri->getPort();
                }
                $new = $new->withoutHeader('host')->withAddedHeader('host', $host);
            }
        }
        return $new;
    }
}