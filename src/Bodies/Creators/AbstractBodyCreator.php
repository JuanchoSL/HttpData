<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Creators;

class AbstractBodyCreator
{

    /**
     * Summary of data
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Summary of appendData
     * @param array<string, mixed> $data 
     * @return static
     */
    public function appendData(array $data): static
    {
        $this->data = array_merge($this->data, (array) $data);
        return $this;
    }
}