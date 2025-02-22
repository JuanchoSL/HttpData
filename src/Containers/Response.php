<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use JuanchoSL\HttpHeaders\Headers;
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
        if (empty($reasonPhrase)) {
            $reasonPhrase = Headers::getMessage($code) ?? '';
        }
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}