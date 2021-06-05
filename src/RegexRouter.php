<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

class RegexRouter implements RouteInterface
{
    private $route;

    private $handler;

    private $request;

    public function __construct($route, $handler, ServerRequestInterface $request)
    {
        $this->route = $route;
        $this->handler = $handler;
        $this->request = $request;
    }

    public function match($url)
    {
        return preg_match($this->route, $url);
    }

    public function route($url)
    {
        if (preg_match($this->route, $url, $args)) {
            array_shift($args);
            array_unshift($matches, $this->request);
            if (is_callable($this->handler)) {
                $handler = $this->handler;


                return call_user_func_array($this->handler, $args);
            } elseif (is_string($this->handler)) {
                $class = $this->handler;
                $controller = new $class();
            }
        }
    }
}

