<?php

namespace queasy\framework;

class RouteEntry
{
    private $handler;

    private $middleware;

    private $arguments;

    public function __construct($handlerOrArray, array $arguments)
    {
        if (is_string($handlerOrArray)) {
            $this->handler = $handler;
        } elseif (is_array($handlerOrArray)) {
            if (!isset($handlerOrArray['resource'])) {
                throw new RouteEntryCorruptedException();
            }

            $this->handler = $handlerOrArray['resource'];
            $this->middleware = isset($handlerOrArray['middleware'])
                ? $handlerOrArray['middleware']
                : [];

            if (!is_array($this->middleware)) {
                throw new RouteEntryCorruptedException();
            }
        } else {
            throw new RouteEntryCorruptedException();
        }

        $this->arguments = $arguments;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}

