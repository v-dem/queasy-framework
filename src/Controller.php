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

    protected $request;

    protected $response;

    public function __construct(App $app, $request, $response)
    {
        $this->app = $app;

        $this->get = $request->getQueryParams();

        $this->post = $request->getParsedBody();

        $this->files = $request->getUploadedFiles();

        $this->request = $request;

        $this->response = $response;
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

        return $this->response
            ->withHeader('Allow', implode(', ', $httpMethodsArray))
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

        $this->response->getStream()->write($body);

        return $this->response
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

