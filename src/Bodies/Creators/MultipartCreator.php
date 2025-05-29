<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Creators;

use CURLFile;
use CURLStringFile;
use JuanchoSL\HttpData\Contracts\BodyCreators;
use Stringable;

class MultipartCreator extends AbstractBodyCreator implements BodyCreators, Stringable
{

    protected string $eol = "\r\n";
    protected string $boundary;

    public function __construct(string $boundary)
    {
        $this->boundary = $boundary;// ?? md5(uniqid());
    }
    public function __tostring(): string
    {
        $body = "";
        $this->parsePart($body, $this->data);
        $body .= '--' . $this->boundary . '--' . $this->eol;
        return $body;
    }

    /**
     * Summary of parsePart
     * @param string $data
     * @param iterable<string, mixed> $part
     * @param string $name
     * @return void
     */
    protected function parsePart(string &$data, iterable $part, string $name = ''): void
    {
        foreach ($part as $key => $value) {
            $subname = empty($name) ? $key : $name;
            if (is_iterable($value)) {
                if ($subname != $key) {
                    $subname .= (is_numeric($key)) ? "[]" : "[$key]";
                }
                $this->parsePart($data, $value, $subname);
            } else {
                if ($subname != $key) {
                    $subname .= (is_numeric($key)) ? "[]" : "[$key]";
                }
                $data .= '--' . $this->boundary . $this->eol;
                if (is_string($value) && substr($value, 0, 1) == '@' && is_file($path = substr($value, 1))) {
                    $value = new CURLFile($path, mime_content_type($path), basename($path));
                }
                if (is_scalar($value)) {
                    $data .= 'Content-Disposition: form-data; name="' . $subname . '"' . $this->eol
                        . $this->eol;
                    $data .= $value . $this->eol;
                } elseif ($value instanceof CURLFile) {
                    $content = file_get_contents($value->getFilename());
                    if ($content === false) {
                        throw new \RuntimeException("Failing reading the file contents");
                    }
                    $data .= 'Content-Disposition: form-data; name="' . $subname . '"; filename="' . $value->getPostFilename() . '"' . $this->eol
                        . 'Content-Type: ' . $value->getMimeType() . $this->eol
                        . 'Content-Transfer-Encoding: binary' . $this->eol
                        . 'Content-Length: ' . strlen($content) . $this->eol
                        . $this->eol;
                    $data .= $content . $this->eol;
                } elseif (class_exists(CURLStringFile::class) && $value instanceof CURLStringFile) {
                    $data .= 'Content-Disposition: form-data; name="' . $subname . '"; filename="' . $value->postname . '"' . $this->eol
                        . 'Content-Type: ' . $value->mime . $this->eol
                        . 'Content-Transfer-Encoding: binary' . $this->eol
                        . 'Content-Length: ' . strlen($value->data) . $this->eol
                        . $this->eol;
                    $data .= $value->data . $this->eol;
                }
            }
        }
    }
}