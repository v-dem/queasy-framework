<?php

namespace queasy\framework;

interface RouteInterface
{
    public function match($path);
    
    public function route($path);
}

