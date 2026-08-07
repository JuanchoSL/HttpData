<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    protected string $target = '';
    protected string $method = '';
    protected UriInterface $uri;

    public function getRequestTarget(): string
    {
        $target = $this->target;
        if (empty($target) /*|| $target == '*'*/) {
            if (empty($this->uri)) {
                $target = '/';
            } elseif (empty($this->uri->getPath()) && $this->getMethod() == RequestMethodInterface::METHOD_OPTIONS) {
                $target = '*';
            } elseif ($this->getMethod() == RequestMethodInterface::METHOD_CONNECT) {
                $target = $this->uri->getAuthority();
            } else {
                $target = $this->uri->getPath();
                while (substr($target, 0, 1) === '/') {
                    $target = substr($target, 1);
                }
                $target = '/' . $target;
                if (!empty($this->uri->getQuery())) {
                    $target .= "?" . $this->uri->getQuery();
                }
            }
        }
        return $target;
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

    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): static
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

    public function __tostring(): string
    {
        $body = $this->getMethod() . " " . $this->getRequestTarget() . " HTTP/" . $this->getProtocolVersion() . "\r\n";
        return $body . parent::__tostring();
    }
}