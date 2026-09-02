<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\StreamInterface;

class StdInReader
{

    protected ?StreamInterface $body_content = null;
    protected ?string $mime_type = null;

    public function __construct()
    {
        foreach (['php://stdin', 'php://input'] as $input) {
            $resource = fopen($input, "rb");
            $body = (new StreamFactory())->createStreamFromResource($resource);
            if (!$body->isSeekable()) {
                $reader = fopen("php://memory", 'rw');
                stream_copy_to_stream($resource, $reader);
                $body = (new StreamFactory())->createStreamFromResource($reader);
            }
            if ($body->getSize() > 0) {
                if (function_exists('mime_content_type') && ($mimetype = @mime_content_type($reader)) !== false) {
                    $this->mime_type = $mimetype;
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $this->mime_type = finfo_file($finfo, $body);
                }

                $this->body_content = $body;
                break;
            }
        }
    }

    public function getBodyContent(): ?StreamInterface
    {
        return $this->body_content;
    }

    public function getBodyType(): ?string
    {
        return $this->mime_type;
    }
}