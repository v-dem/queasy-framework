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

    protected $redirect;

    public function __construct(App $app, $request, $response, $redirect)
    {
        $this->app = $app;

        $this->get = $request->getQueryParams();

        $this->post = $request->getParsedBody();

        $this->files = $request->getUploadedFiles();

        $this->request = $request;

        $this->response = $response;

        $this->redirect = $redirect;
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
}

