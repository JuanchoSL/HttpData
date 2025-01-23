<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    protected int $status_code;
    protected string $reasonPhrase;

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->status_code = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function send(): void
    {
        //http_response_code($this->getStatusCode());
        header("HTTP/" . $this->getProtocolVersion() . " " . $this->getStatusCode() . " " . $this->getReasonPhrase());
        foreach ($this->headers as $name => $value) {
            header($name . ": " . $this->getHeaderLine($name));
        }
        echo (string) $this->getBody();
        exit;
    }
}