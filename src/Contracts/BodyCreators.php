<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Contracts;

use Stringable;

interface BodyCreators extends Stringable
{
    public function appendData(array $data): static;
}