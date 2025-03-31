<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Creators;

use JuanchoSL\HttpData\Contracts\BodyCreators;
use Stringable;

class UrlencodedCreator extends AbstractBodyCreator implements BodyCreators, Stringable
{

    public function __tostring(): string
    {
        return http_build_query($this->data);
    }
}