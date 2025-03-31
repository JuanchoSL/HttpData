<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Contracts;

interface BodyParsers
{

    /**
     * Put into superglobals the posted values
     * @return void
     */
    public function toPostGlobals(): void;

    /**
     * Retrieve the posted body values and convert to array, data form are available into index 0 and files, if availables, into index 1
     * @return array The extracted values
     */
    public function getBodyParts(): array;

    /**
     * Retrieve the posted data form values and convert it to an array
     * @return array The extracted data form values
     */
    public function getBodyParams(): array;
}