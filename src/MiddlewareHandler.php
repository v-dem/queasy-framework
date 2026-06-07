<?php

namespace queasy\framework;

use queasy\container\ServiceContainer;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MiddlewareHandler extends ServiceContainer implements LoggerAwareInterface
{
    private $config;

    private $app;

    private $logger;

    public function __construct($config, App $app)
    {
        $this->config = $config;

        $this->handler = $handler;

        $this->app = $app;

        $this->logger = new NullLogger();
    }

    public function handle(array $middlewares = array(), Closure $handler, ServerRequestInterface $request)
    {
        foreach ($middlewares as &$middleware) {
            $middleware = $this->$middleware;
        }

        $queueProcessor = new MiddlewareQueueProcessor($middlewares, $handler);

        return $queueProcessor->handle($request);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function app()
    {
        return $this->app;
    }
}

