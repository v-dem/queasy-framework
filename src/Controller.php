<?php

namespace queasy\framework;

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

        $this->get = $app->request->getQueryParams();

        $this->post = $app->request->getParsedBody();

        $this->files = $app->request->getUploadedFiles();
    }

    protected function view($__page, array $__data = array(), $__responseCode = 200)
    {
        extract($__data);

        ob_start();

        $__config = $this->app->config;

        require isset($__config['viewsPath'])
            ? $__config['viewsPath'] . $__page
            : $__page;

        $__body = ob_get_contents();

        ob_end_clean();

        $this->app->stream->write($__body);

        return $this->app->response
            ->withBody($this->app->stream)
            ->withStatus($__responseCode);
    }

    protected function json($data, $jsonFlags = 0, $responseCode = 200)
    {
        $json = json_encode($data, $jsonFlags);

        $this->app->stream->write($json);

        return $this->app->response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->app->stream)
            ->withStatus($responseCode);
    }
}

