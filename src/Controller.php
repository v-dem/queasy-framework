<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

use queasy\http\Stream;

class Controller
{
    protected $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    protected function view($__page, array $__data = array(), $__responseCode = 200)
    {
        extract($__data);

        ob_start();

        require(isset($this->app->config['viewsPath'])
            ? $this->app->config['viewsPath'] . $__page
            : $__page);

        $__body = ob_get_contents();
        ob_end_clean();

        return $this->app->response->withBody(new Stream($__body))->withStatus($__responseCode);
    }
}

