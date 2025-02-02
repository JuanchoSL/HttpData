<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Factories;

use JuanchoSL\DataTransfer\Enums\Format;
use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\Exceptions\UnsupportedMediaTypeException;
use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\HttpData\Containers\ServerRequest;
use JuanchoSL\HttpHeaders\Constants\Types\MimeTypes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = (new UriFactory)->createUri($uri);
        }
        $req = (new ServerRequest)
            ->withMethod($method)
            ->withProtocolVersion($_SERVER['SERVER_PROTOCOL'])
            ->withUploadedFiles((new UploadedFileFactory)->fromGlobals())
            ->withCookieParams($_COOKIE ?? [])
            ->withQueryParams($_GET ?? [])
        ;

        foreach (getallheaders() as $key => $value) {
            $req = $req->withAddedHeader($key, $value);
        }
        if (in_array(strtoupper($req->getMethod()), ['POST', 'PUT', 'PATCH'])) {

            $content_types = $req->getHeader('content-type');
            if (in_array('application/x-www-form-urlencoded', $content_types) || in_array('multipart/form-data', $content_types)) {
                $body = $_POST;
            } else {
                foreach ($content_types as $content_type) {
                    switch ($content_type) {
                        case MimeTypes::JSON:
                            $type = Format::JSON;
                            break;
                        case MimeTypes::CSV:
                            $type = Format::CSV;
                            break;
                        case MimeTypes::XML:
                            $type = Format::XML;
                            break;
                        case MimeTypes::EXCEL:
                            $type = Format::EXCEL_XLSX;
                            break;
                        default:
                            throw new UnsupportedMediaTypeException("The media type {$content_type} is not supported");
                    }
                }
                $body = DataTransferFactory::byString(file_get_contents('php://input'), $type);
            }
            if (!empty($body)) {
                $req = $req->withParsedBody($body)->withBody((new StreamFactory)->createStream(file_get_contents('php://input')));
            }
        }
        return $req->withUri($uri);
    }

    public function createServerResponse(ServerRequestInterface $server_request): ResponseInterface
    {
        $response = (new ResponseFactory)->createResponse()->withProtocolVersion($server_request->getProtocolVersion());
        $accepts = $server_request->hasHeader('accept') ? $server_request->getHeaderLine('accept') : '';
        if (!empty($accepts)) {
            foreach (explode(',', $accepts) as $accept) {
                if (in_array($accept, [MimeTypes::HTML, MimeTypes::JSON, MimeTypes::CSV, MimeTypes::XML, MimeTypes::EXCEL])) {
                    $content_type = $accept;
                    break;
                }
            }
            if (empty($content_type)) {
                throw new UnsupportedMediaTypeException("Any acepted media type ({$accepts}) are supported");
            }
            $response = $response->withAddedHeader('Content-type', $content_type);
        }
        return $response;
    }

}