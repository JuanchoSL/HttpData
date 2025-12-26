<?php declare(strict_types=1);

namespace JuanchoSL\HttpData\Containers;

class SetCookie
{
    protected string $name;
    protected string $value;
    protected string $path = "/";
    protected string $domain;
    protected string $same_site = '';
    protected bool $secure = false;
    protected bool $httponly = false;
    protected int $expires = 0;
    protected int $max_age = 0;

    const COOKIE_SAMESITE_STRICT = 'strict';
    const COOKIE_SAMESITE_NONE = 'none';
    const COOKIE_SAMESITE_LAX = 'lax';

    public function getName(): string
    {
        return $this->name;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getDomain(): string
    {
        return $this->domain ?? $_SERVER['HTTP_HOST'];
    }
    public function getSecure(): bool
    {
        return $this->secure;
    }
    public function getHttpOnly(): bool
    {
        return $this->httponly;
    }
    public function getMaxAge(): int
    {
        return $this->max_age;
    }
    public function getExpires(): int
    {
        return $this->expires;
    }
    public function getSameSite(): string
    {
        return $this->same_site;
    }
    public function withName(string $name): static
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function withValue(string $value): static
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }
    public function withPath(string $path): static
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }
    public function withDomain(string $domain): static
    {
        $new = clone $this;
        $new->domain = $domain;
        return $new;
    }
    public function withExpires(int $expires): static
    {
        $new = clone $this;
        $new->expires = $expires;
        return $new;
    }
    public function withMaxAge(int $max_age): static
    {
        $new = clone $this;
        $new->max_age = $max_age;
        return $new;
    }
    public function withSecure(): static
    {
        $new = clone $this;
        $new->secure = true;
        return $new;
    }
    public function withoutSecure(): static
    {
        $new = clone $this;
        $new->secure = false;
        return $new;
    }
    public function withHttpOnly(): static
    {
        $new = clone $this;
        $new->httponly = true;
        return $new;
    }
    public function withoutHttpOnly(): static
    {
        $new = clone $this;
        $new->httponly = false;
        return $new;
    }
    public function withSameSite(string $same_site): static
    {
        $new = clone $this;
        $new->same_site = in_array(strtolower($same_site), [static::COOKIE_SAMESITE_STRICT, static::COOKIE_SAMESITE_LAX, static::COOKIE_SAMESITE_NONE]) ? $same_site : '';
        return $new;
    }

    public function __tostring(): string
    {
        $cookie = sprintf(
            "%s=%s; expires=%s; Max-Age=%d; path=%s",
            $this->getName(),
            rawurlencode($this->getValue()),
            date(DATE_COOKIE, ((empty($this->getExpires())) ? time() : $this->getExpires()) + $this->getMaxAge()),
            $this->getMaxAge(),
            $this->getPath(),
        );
        if (!empty($this->getDomain())) {
            $cookie .= sprintf("; domain=%s", $this->getDomain());
        }
        if ($this->getSecure()) {
            $cookie .= "; secure";
        }
        if ($this->getHttpOnly()) {
            $cookie .= "; HttpOnly";
        }
        if (!empty($this->getSameSite())) {
            $cookie .= sprintf("; samesite=%s", ucfirst(strtolower($this->getSameSite())));
        }
        return $cookie;
    }

    public function __invoke()
    {
        $options = [
            'path' => $this->getPath(),
            'domain' => $this->getDomain(),
        ];
        if ($this->getExpires() == 0 && $this->getMaxAge() > 0) {
            $this->expires = time() + $this->getMaxAge();
        }
        if ($this->getExpires() > 0) {
            $options["expires"] = $this->getExpires();
        }
        if ($this->getSecure()) {
            $options["secure"] = true;
        }
        if ($this->getHttpOnly()) {
            $options["httponly"] = true;
        }
        if (!empty($this->getSameSite())) {
            $options["samesite"] = $this->getSameSite();
        }
        if (!$this->getSecure() || ($this->getSecure() && filter_input(INPUT_SERVER, 'HTTPS') !== false)) {
            setcookie($this->getName(), $this->getValue(), $options);
        }
    }
}