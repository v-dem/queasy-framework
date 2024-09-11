<?php

namespace queasy\framework;

use queasy\http\Stream;

use ReflectionClass;
use ReflectionMethod;

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

    public function options()
    {
        $class = new ReflectionClass($this);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $httpMethodsArray = array();
        foreach ($methods as $method) {
            if ($method->isAbstract() || $method->isStatic() || $method->isConstructor() || $method->isDestructor()) {
                continue;
            }

            $httpMethodsArray[] = strtoupper($method->getName());
        }

        return $this->app->response
            ->withHeader('Allow', implode(', ', $httpMethodsArray))
            ->withBody($this->app->stream)
            ->withStatus(200);
    }

    protected function preview($__page, array $__data = array())
    {
        extract($__data);

        ob_start();

        $__config = $this->app->config;

        require isset($__config['viewsPath'])
            ? $__config['viewsPath'] . $__page
            : $__page;

        return ob_get_clean();
    }

    protected function view($page, array $data = array(), $responseCode = 200)
    {
        $body = $this->preview($page, $data);

        $this->app->stream->write($body);

        return $this->app->response
            ->withBody($this->app->stream)
            ->withStatus($responseCode);
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

