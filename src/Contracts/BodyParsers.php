<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Contracts;

interface BodyParsers
{
    public function toPostGlobals(): void;

    public function getBodyParts(): array;

    public function getBodyParams(): array;
}