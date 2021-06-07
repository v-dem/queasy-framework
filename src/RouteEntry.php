<?php

namespace queasy\framework;

class RouteEntry
{
    private $handler;

    private $arguments;

    public function __construct($handler, array $arguments)
    {
        $this->handler = $handler;
        $this->arguments = $arguments;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}

