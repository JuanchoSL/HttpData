<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    protected StreamInterface $stream;
    protected int $error;
    protected ?string $client_name = null;
    protected ?string $media_type = null;
    protected ?int $size = null;
    protected bool $moved = false;

    public function __construct(StreamInterface $stream, ?string $client_name = null, ?string $media_type = null, ?int $size = null, int $error = 0)
    {
        $this->stream = $stream;
        $this->client_name = $client_name;
        $this->media_type = $media_type;
        $this->size = $size;
        $this->error = $error;
    }

    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new \RuntimeException("The stream has been moved");
        }
        if (empty($this->stream)) {
            throw new \RuntimeException("No stream available");
        }
        return $this->stream;
    }

    public function moveTo($targetPath): void
    {
        if ($this->moved) {
            throw new \RuntimeException("The file has been moved previously");
        } else {
            if (!is_file($targetPath)) {
                throw new \InvalidArgumentException("The path '{$targetPath}' is invalid");
            } elseif (empty($this->client_name) || !is_uploaded_file($this->client_name) || !move_uploaded_file($this->client_name, $targetPath)) {
                throw new \RuntimeException("The file cannot be moved");
            }
        }

        $this->moved = true;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->client_name;

    }
    public function getClientMediaType(): ?string
    {
        return $this->media_type;
    }
}