<?php

namespace queasy\framework;

class Redirect
{
    protected $baseUrl;

    protected $response;

    public function __construct($baseUrl, $response)
    {
        $this->baseUrl = $baseUrl;
        $this->response = $response;
    }

    public function path($path = '')
    {
        return $this->response->withHeader('Location', $baseUrl . $path);
    }
}

