<?php

namespace Sirian\Helpers;

class Url
{
    protected $url;
    protected $scheme;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $path;
    protected $query;
    protected $fragment;

    public function __construct($url = '')
    {
        $this->setUrl($url);
    }

    public function setUrl($url)
    {
        $this->url = $url;
        $parts = parse_url($url);
        $fields = ['scheme', 'host', 'port', 'user', 'pass', 'path', 'fragment'];
        foreach ($fields as $field) {
            $this->{$field} = isset($parts[$field]) ? $parts[$field] : null;
        }
        if (isset($parts['query'])) {
            parse_str($parts['query'], $this->query);
        } else {
            $this->query = [];
        }
    }

    public function getUrl()
    {
        if (null === $this->url) {
            $this->compile();
        }

        return $this->url;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function setScheme($scheme)
    {
        return $this->setPart('scheme', $scheme);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        return $this->setPart('host', $host);
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        return $this->setPart('port', $port);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        return $this->setPart('user', $user);
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function setPass($pass)
    {
        return $this->setPart('pass', $pass);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        return $this->setPart('path', $path);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery(array $query)
    {
        return $this->setPart('query', $query);
    }

    public function setQueryParam($param, $value)
    {
        $this->query[$param] = $value;
        $this->url = null;
        return $this;
    }

    public function unsetQueryParam($param)
    {
        unset($this->query[$param]);
        $this->url = null;
        return $this;
    }

    public function getQueryParam($param)
    {
        return isset($this->query[$param]) ? $this->query[$param] : null;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function setFragment($fragment)
    {
        return $this->setPart('fragment', $fragment);
    }

    protected function setPart($part, $value)
    {
        if ($this->{$part} != $value) {
            $this->{$part} = $value;
            $this->url = null;
        }
        return $this;
    }

    protected function compile()
    {
        $url = '';
        if ($this->scheme) {
            $url .= $this->scheme . ':';
        }

        if ($this->host) {
            $url .= '//';
            if ($this->user) {
                $url .= $this->user;
                if ($this->pass) {
                    $url .= ':' . $this->pass;
                }
                $url .= '@';
            }
            $url .= $this->host;
        }

        if ($this->path) {
            if ($this->path[0] != '/') {
                $url .= '/';
            }
            $url .= $this->path;
        } else {
            $url .= '/';
        }

        if ($this->query) {
            $url .= '?' . http_build_query($this->query);
        }

        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }

        $this->url = $url;
        return $this;
    }
}
