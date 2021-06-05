<?php

namespace queasy\framework;

interface RouteInterface
{
    public function match($url);
    
    public function route($url);
}

