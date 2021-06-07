<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

class App
{
    protected $config;

    protected $serviceContainer;

    public function __construct($config, $serviceContainer)
    {
        $this->config = $config;
        $this->serviceContainer = $serviceContainer;
    }

    public function __get($service)
    {
        if ('config' === $service) {
            return $this->config;
        }

        return $this->serviceContainer->get($service);
    }

    public function run(ServerRequestInterface $request)
    {
        try {
            $routeEntry = $this->router->route($request);
            $handler = $routeEntry->getHandler();
            $arguments = $routeEntry->getArguments();
            if (is_callable($handler)) {
                array_unshift($arguments, $request);

                return call_user_func_array($handler, $arguments);
            } elseif (is_string($handler)) {
                $controller = new $handler($this, $request);
                $method = strtolower($request->getMethod());

                return call_user_func_array(array($controller, $method), $arguments);
            } else {
                throw new InvalidArgumentException(sprintf('Invalid handler type "%s".', gettype($handler)));
            }
        } catch (RouteNotFoundException $e) {
            return $this->page404($request);
        }
    }
}

