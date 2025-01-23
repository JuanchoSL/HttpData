<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{

    protected $resource;
    protected array $meta = [];
    protected int $seek;
    public function __construct($resource)
    {
        $this->resource = $resource;
    }
    public function __toString(): string
    {
        if (empty($this->resource)) {
            throw new \RuntimeException("No resource available");
        }
        $this->seek = $this->tell();
        $this->rewind();
        $data = $this->getContents();
        $this->seek($this->seek);
        return $data;
    }

    public function close(): void
    {
        fclose($this->resource);
    }

    public function detach()
    {
        $this->close();
        $this->resource = null;
    }

    public function getSize(): int|null
    {
        if (empty($this->resource)) {
            throw new \RuntimeException("No resource available");
        }
        $size = fstat($this->resource);

        return array_key_exists('size', $size) && is_numeric($size['size']) ? $size['size'] : null;
    }

    public function tell(): int
    {
        if (empty($this->resource) || ($data = ftell($this->resource)) === false) {
            throw new \RuntimeException("No resource available");
        }
        return $data;
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable');
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (empty($this->resource) || ($data = fseek($this->resource, $offset, $whence)) < 0) {
            throw new \RuntimeException("No resource available");
        }
    }

    public function rewind(): void
    {
        if (empty($this->resource) || rewind($this->resource) === false) {
            throw new \RuntimeException("No resource available");
        }
    }

    public function isWritable(): bool
    {
        $mode = $this->getMetadata('mode');
        return (strstr($mode, 'w') !== false || strstr($mode, '+') !== false);
    }

    public function write($string): int
    {
        if (empty($this->resource) || ($data = fwrite($this->resource, $string)) === false) {
            throw new \RuntimeException("No resource available");
        }
        return $data;
    }

    public function isReadable(): bool
    {
        $mode = $this->getMetadata('mode');
        return (strstr($mode, 'r') !== false || strstr($mode, '+') !== false);
    }

    public function read($length): string
    {
        if (empty($this->resource) || ($data = fread($this->resource, $length)) === false) {
            throw new \RuntimeException("No resource available");
        }
        return $data;
    }

    public function getContents(): string
    {
        if (empty($this->resource)) {
            throw new \RuntimeException("No resource available");
        }
        $data = '';
        while (!$this->eof()) {
            $data .= $this->read(1024);
        }
        return $data;
    }

    public function getMetadata($key = null): mixed
    {
        if (!$this->resource) {
            return null;
        }

        $this->meta = stream_get_meta_data($this->resource);

        if (!$key) {
            return $this->meta;
        }

        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }
}