<?php

namespace queasy\framework;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\ServerRequestInterface;

class App implements Psr\Log\LoggerAwareInterface
{
    protected $config;

    protected $logger;

    public function __construct($config)
    {
        $this->config = $config;

        $this->logger = new Psr\Log\NullLogger();
    }

    public function handle(ServerRequestInterface $request)
    {
    }

    public function config()
    {
        return $this->config;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logger()
    {
        return $this->logger();
    }
}

