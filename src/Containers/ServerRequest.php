<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected null|array|object $parsed_body = null;
    protected array $server_params = [];
    protected array $cookie_params = [];
    protected array $query_params = [];
    protected array $attributes = [];
    protected array $uploaded_files = [];

    public function getServerParams(): array
    {
        return $_SERVER;//$this->server_params;
    }

    public function getCookieParams(): array
    {
        return $this->cookie_params;
    }

    public function withCookieParams(array $cookies): static
    {
        $new = clone $this;
        $new->cookie_params = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->query_params;
    }

    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->query_params = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploaded_files;
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $new = clone $this;
        $new->uploaded_files = $uploadedFiles;
        return $new;
    }

    public function getParsedBody(): array|object|null
    {
        return $this->parsed_body;
    }

    public function withParsedBody($data): static
    {
        $new = clone $this;
        $new->parsed_body = $data;
        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, $value): static
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute(string $name): static
    {
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }
}