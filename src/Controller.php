<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

use queasy\http\Stream;

class Controller
{
    protected $app;
    
    protected $request;

    public function __construct(App $app, ServerRequestInterface $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    protected function view($__page, array $__data = array(), $__responseCode = 200)
    {
        extract($__data);

        ob_start();

        require($__page);

        $__body = ob_get_contents();
        ob_end_clean();

        return $this->app->response->withBody(new Stream($__body))->withStatus($__responseCode);
    }
}

