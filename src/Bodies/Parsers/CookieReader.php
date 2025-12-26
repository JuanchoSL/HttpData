<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Containers\SetCookie;

class CookieReader
{

    protected $cookie;

    public function __construct(string $cookie_header)
    {
        $cookie_parts = explode(';', $cookie_header);
        foreach ($cookie_parts as $cookie_part) {
            if (strpos($cookie_part, '=') !== false) {
                list($name, $data) = explode('=', $cookie_part);
            } else {
                $name = $cookie_part;
                $data = true;
            }

            if (empty($this->cookie)) {
                $this->cookie = (new SetCookie())->withName(trim($name))->withValue(trim($data));
            } else {
                //TestCookie=The%20Cookie%20Value; expires=Sat, 20 Dec 2025 16:57:05 GMT; Max-Age=60; path=/; domain=host.docker.internal; secure; HttpOnly; SameSite=Strict
                switch (strtolower(trim($name))) {
                    case 'expires':
                        $this->cookie = $this->cookie->withExpires(strtotime($data));
                        break;
                    case 'max-age':
                        $this->cookie = $this->cookie->withMaxAge(+$data);
                        break;
                    case 'path':
                        $this->cookie = $this->cookie->withPath(trim($data));
                        break;
                    case 'domain':
                        $this->cookie = $this->cookie->withDomain(trim($data));
                        break;
                    case 'secure':
                        $this->cookie = $this->cookie->withSecure();
                        break;
                    case 'httponly':
                        $this->cookie = $this->cookie->withHttpOnly();
                        break;
                    case 'samesite':
                        $this->cookie = $this->cookie->withSameSite(trim($data));
                        break;
                }
            }
        }
    }

    public function __invoke(): SetCookie
    {
        return $this->cookie;
    }
}