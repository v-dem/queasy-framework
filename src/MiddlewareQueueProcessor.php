<?php

namespace queasy\framework;

use queasy\container\ServiceContainer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Closure;

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
            $handler = $this->handler;

            return $handler($request);
        }

        $middleware = array_shift($this->middlewares);

        return $middleware->process($request, $this);
    }
}

