<?php

namespace queasy\framework;

use queasy\http\ServerRequest;

class Controller
{
    protected $app;
    
    protected $request;

    public function __construct(App $app, ServerRequest $request)
    {
        $this->app = $app;
        $this->request = $request;
    }
}

