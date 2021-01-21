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

    public function match($path)
    {
        return preg_match($this->route, $path);
    }

    public function route($path)
    {
        if (preg_match($this->route, $path, $args)) {
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

