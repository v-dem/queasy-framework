<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

use queasy\http\Stream;

class Controller
{
    protected $app;

    protected $get;

    protected $post;

    protected $files;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->get = filter_input_array(INPUT_GET);

        $this->post = filter_input_array(INPUT_POST);

        $this->files = $_FILES;
    }

    protected function view($__page, array $__data = array(), $__responseCode = 200)
    {
        extract($__data);

        ob_start();

        require isset($this->app->config['viewsPath'])
            ? $this->app->config['viewsPath'] . $__page
            : $__page;

        $__body = ob_get_contents();

        ob_end_clean();

        return $this->app->response
            ->withBody(new Stream($__body))
            ->withStatus($__responseCode);
    }
}

