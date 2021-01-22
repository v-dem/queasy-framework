<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

class App
{
    protected $config;

    protected $serviceContainer;

    public function __construct($config, $serviceContainer)
    {
        $this->config = $config;
        $this->serviceContainer = $serviceContainer;
    }

    public function __get($service)
    {
        return $this->serviceContainer->get($service);
    }

    public function run(ServerRequestInterface $request)
    {
        
    }
}

