<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

class App implements Psr\Log\LoggerAwareInterface
{
    protected $config;

    protected $serviceLocator;

    public function __construct($config, $serviceLocator)
    {
        $this->config = $config;
        $this->serviceLocator = $serviceLocator;
    }

    public function __get($service)
    {
        return $this->$service;
    }

    public function run(ServerRequestInterface $request)
    {
    }
}

