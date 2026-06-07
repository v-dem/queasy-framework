<?php

namespace queasy\framework;

use queasy\container\ServiceContainer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareQueueProcessor implements RequestHandlerInterface
{
    private $middlewares;

    private $handler;

    public function __construct(array $middlewares = array(), Closure $handler)
    {
        $this->middlewares = $middlewares;
        $this->handler = $handler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares)) {
            return $this->handler($request);
        }

        $middleware = array_shift($this->middlewares);

        return $middleware->process($request, $this);
    }
}

