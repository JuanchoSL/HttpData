<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Creators;

class AbstractBodyCreator
{

    protected array $data = [];

    public function appendData(array $data): static
    {
        $this->data = array_merge($this->data, (array) $data);
        return $this;
    }
}