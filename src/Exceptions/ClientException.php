<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends \Exception implements ClientExceptionInterface
{

}