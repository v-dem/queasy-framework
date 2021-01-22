<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    protected $app;
    
    protected $request;

    public function __construct(App $app, ServerRequestInterface $request)
    {
        $this->app = $app;
        $this->request = $request;
    }
}

