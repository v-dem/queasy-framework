<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

interface RouteInterface
{
    public function route(ServerRequestInterface $request);
}

