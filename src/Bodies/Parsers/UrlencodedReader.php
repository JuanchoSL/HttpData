<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Contracts\BodyParsers;
use Psr\Http\Message\StreamInterface;

class UrlencodedReader implements BodyParsers
{

    protected StreamInterface $resource;

    public function __construct(StreamInterface $resource)
    {
        $this->resource = $resource;
    }

    public function toPostGlobals(): void
    {
        [$_POST] = $this->getBodyParts();
    }

    public function getBodyParams(): array
    {
        mb_parse_str((string) $this->resource, $data);
        return $data;
    }

    public function getBodyParts(): array
    {
        return [$this->getBodyParams()];
    }
}