<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Creators;

use Stringable;

class UrlencodeCreator extends AbstractBodyCreator implements Stringable
{

    public function __tostring(): string
    {
        return http_build_query($this->data);
    }
}