<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Contracts;

use Stringable;

interface BodyCreators extends Stringable
{

    /**
     * Append data to encode, you can add multidimensionals arrays in order to complete values when are availables
     * @param array $data Data to append
     * @return void
     */
    public function appendData(array $data): static;
}