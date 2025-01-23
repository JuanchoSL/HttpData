<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\HttpData\Containers\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{

    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        return $this->createStreamFromResource($resource);
    }
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (!in_array($mode, ['r', 'r+', 'w', 'w+', 'a', 'a+'])) {
            throw new \InvalidArgumentException("The argument {$mode} is not a valid mode value");
        }
        if (!is_file($filename) || (!$file = fopen($filename, $mode))) {
            throw new \RuntimeException("The filename {$filename} cannot be opened");
        }
        return $this->createStreamFromResource($file);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}