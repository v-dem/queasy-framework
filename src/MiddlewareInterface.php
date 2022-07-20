<?php

namespace queasy\framework;

use Closure;

use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    public function handle(ServerRequestInterface $request, Closure $next);
}

