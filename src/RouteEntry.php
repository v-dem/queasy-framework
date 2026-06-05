<?php

namespace queasy\framework;

use ArrayAccess;
use InvalidArgumentException;

class RouteEntry
{
    private $handler;

    private $middleware;

    private $arguments;

    public function __construct($handlerOrArray, array $arguments)
    {
        if (is_string($handlerOrArray)) {
            $this->handler = $handlerOrArray;
        } elseif (is_array($handlerOrArray) || ($handlerOrArray instanceof ArrayAccess)) {
            if (!isset($handlerOrArray['resource'])) {
                throw new RouteEntryCorruptedException('Required "resource" key not set.');
            }

            $this->handler = $handlerOrArray['resource'];
            $this->middleware = isset($handlerOrArray['middleware'])
                ? $handlerOrArray['middleware']
                : [];

            if (!is_array($this->middleware)) {
                throw new RouteEntryCorruptedException('Value of "middleware" must be an array.');
            }
        } else {
            throw new InvalidArgumentException('Handler must be string or array, given: ' . gettype($handlerOrArray));
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

