<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected string $scheme = "";
    protected string $userinfo = "";
    protected string $host = "";
    protected ?int $port = null;
    protected string $path = "";
    protected string $query = "";
    protected string $fragment = "";

    public function getScheme(): string
    {
        return strtolower($this->scheme);
    }

    public function getAuthority(): string
    {
        $authority = '';
        if (!empty($this->getUserInfo())) {
            $authority .= $this->getUserInfo() . '@';
        }
        $authority .= $this->getHost();
        if (!empty($this->getPort())) {
            $authority .= ":" . $this->getPort();
        }
        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userinfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int|null
    {
        $ports = [
            21 => "ftp",
            22 => "ssh",
            25 => "smtp",
            80 => "http",
            110 => "pop3",
            220 => "imap",
            443 => "https",
            990 => "ftps",
        ];
        if (!empty($this->getScheme())) {
            $port = array_search($this->scheme, $ports);
            if ($port && $this->port === $port) {
                return null;
            }
        }
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $new = clone $this;
        $new->userinfo = $user;
        if (!empty($password)) {
            $new->userinfo .= ":" . $password;
        }
        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    public function __toString(): string
    {
        $url = '';
        if (!empty($this->getScheme())) {
            $url .= $this->getScheme() . ":";
        }
        if (!empty($this->getAuthority())) {
            $url .= '//' . $this->getAuthority();
        }
        if (!empty($this->getPath())) {
            $path = $this->getPath();
            while (substr($path, 0, 1) === '/') {
                $path = substr($path, 1);
            }
            $url .= '/' . $path;
        }
        if (!empty($this->getQuery())) {
            $url .= '?' . $this->getQuery();
        }
        if (!empty($this->getFragment())) {
            $url .= '#' . $this->getFragment();
        }
        return $url;
    }
}