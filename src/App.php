<?php

namespace queasy\framework;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;

use queasy\framework\container\ServiceContainerInterface;
use queasy\http\Stream;

class App
{
    protected $config;

    protected $serviceContainer;

    public function __construct($config, ServiceContainerInterface $serviceContainer)
    {
        $this->config = $config;
        $this->serviceContainer = $serviceContainer;

        $this->init();
    }

    public function __get($service)
    {
        if ('config' === $service) {
            return $this->config;
        }

        return $this->serviceContainer->get($service);
    }

    public function run()
    {
        try {
            $this->logger->debug('Request path: ' . $this->request->getUri()->getPath());

            $routeEntry = $this->router->route($this->request);
            $handler = $routeEntry->getHandler();
            $arguments = $routeEntry->getArguments();
            $hasOutput = false;
            if (is_callable($handler)) {
                $output = call_user_func_array($handler, $arguments);
                $hasOutput = true;
            } elseif (is_string($handler)) {
                $controller = new $handler($this);
                $method = strtolower($this->request->getMethod());

                $output = call_user_func_array(array($controller, $method), $arguments);
                $hasOutput = true;
            } else {
                throw new InvalidArgumentException(sprintf('Invalid handler type "%s".', gettype($handler)));
            }

            return (!is_string($output) && $this->request->isAjax())
                ? json_encode($output)
                : $output;
        } catch (RouteNotFoundException $e) {
            return $this->page404($this->request);
        }
    }

    protected function page404()
    {
        return $this->response
            ->withBody(new Stream('The requested URL was not found on this server.'))
            ->withStatus(404);
    }

    protected function init()
    {
    }
}

