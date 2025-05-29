<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\HttpData\Containers\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = \UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        if (!$stream->isReadable()) {
            throw new \InvalidArgumentException("The file '{$clientFilename}' is not readable");
        }

        return new UploadedFile($stream, $clientFilename, $clientMediaType, $size, $error);
    }

    /**
     * Summary of fromGlobals
     * @return array<string|int, mixed>
     */
    public function fromGlobals(): array
    {
        $uploadedFiles = $_FILES;
        $uploaded_files = [];
        foreach ($uploadedFiles as $key => $uploadedFile) {
            $uploaded_files[$key] = [];
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $index) {
                $this->fileInputParse($uploadedFiles[$key][$index], $index, $uploaded_files[$key]);
            }
        }
        return $uploaded_files;
    }

    protected function fileInputParse($arr, $name, &$seq = [])
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                $this->fileInputParse($value, $name, $seq[$key]);
            }
        } else {
            $seq[$name] = $arr;
            if ($name == 'size') {
                return $seq = $this->createUploadedFile(
                    (new StreamFactory)->createStreamFromFile($seq['tmp_name']),
                    (int) $seq['size'],
                    (int) $seq['error'],
                    $seq['name'],
                    $seq['type'],
                );
            }
            return $seq[$name];
        }
    }
}