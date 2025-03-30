<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Exceptions;

use Psr\Http\Client\NetworkExceptionInterface;

class NetworkException extends ClientException implements NetworkExceptionInterface
{

}