<?php

namespace queasy\framework;

use Closure;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MiddlewareHandler implements LoggerAwareInterface
{
    private $config;

    private $app;

    private $logger;

    public function __construct($config, App $app)
    {
        $this->config = $config;

        $this->app = $app;

        $this->logger = new NullLogger();
    }

    public function handle(ServerRequestInterface $request, Closure $controllerClosure)
    {
        
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

