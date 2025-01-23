<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Exceptions;

class RequestException extends ClientException
{

    public function getRequest(): RequestInterface{
        
    }
}