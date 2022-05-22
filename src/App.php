<?php

namespace queasy\framework;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;

use queasy\http\Stream;

class App implements ContainerInterface
{
    protected $config;

    protected $services;

    public function __construct($config)
    {
        $this->config = $config;
        $this->services = array();

        if (!isset($this->config['logger'])) {
            $this->services['logger'] = new NullLogger();
        }

        $this->init();
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
                switch (count($arguments)) { // Attempt to increase performance
                    case 0:
                        $output = $handler();
                        break;
                    case 1:
                        $output = $handler($arguments[0]);
                        break;
                    case 2:
                        $output = $handler($arguments[0], $arguments[1]);
                        break;
                    default:
                        $output = call_user_func_array($handler, $arguments);
                }
            } elseif (is_string($handler)) {
                $controller = new $handler($this);
                $method = strtolower($this->request->getMethod());
                switch (count($arguments)) { // Attempt to increase performance
                    case 0:
                        $output = $controller->$method();
                        break;
                    case 1:
                        $output = $controller->$method($arguments[0]);
                        break;
                    case 2:
                        $output = $controller->$method($arguments[0], $arguments[1]);
                        break;
                    default:
                        $output = call_user_func_array(array($controller, $method), $arguments);
                }
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

    public function __isset($serviceId)
    {
        return $this->has($serviceId);
    }

    public function __get($serviceId)
    {
        if ('config' === $serviceId) {
            return $this->config;
        }

        return $this->get($serviceId);
    }

    /**
     * Returns true if the container can return a service for the given identifier.
     * Returns false otherwise.
     *
     * `has($serviceId)` returning true does not mean that `get($serviceId)` will not throw an exception.
     * It does however mean that `get($serviceId)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $serviceId Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($serviceId)
    {
        return isset($this->config[$serviceId]);
    }

    /**
     * Finds a service of the container by its identifier and returns it.
     *
     * @param string $serviceId Identifier of the service to look for.
     *
     * @throws NotFoundExceptionInterface  No service was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the service.
     *
     * @return mixed Entry.
     */
    public function get($serviceId)
    {
        if (isset($this->services[$serviceId])) {
            return $this->services[$serviceId];
        } elseif (isset($this->config[$serviceId])) {
            $serviceConfig = $this->config[$serviceId];
            $serviceClass = $serviceConfig['class'];
            if (isset($serviceConfig['construct'])) {
                $args = $this->parseArgs($serviceConfig['construct']);
                if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                    $serviceInstance = new $serviceClass(...$args);
                } else {
                    $reflect = new ReflectionClass($serviceClass);
                    $serviceInstance = $reflect->newInstanceArgs($args);
                }
            } else {
                $serviceInstance = new $serviceClass();
            }

            foreach ($serviceConfig as $method => $args) {
                if (('class' === $method) || ('construct' === $method)) {
                    continue;
                }

                call_user_func_array(array($serviceInstance, $method), $this->parseArgs($args));
            }

            $this->services[$serviceId] = $serviceInstance;

            return $serviceInstance;
        } else {
            throw new NotFoundException(sprintf('Service "%s" is not configured.', $serviceId));
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

    private function parseArgs($args)
    {
        $result = [];
        foreach ($args as $arg) {
            foreach ($arg as $argType => $argValue) {
                if ('value' === $argType) {
                    $result[] = $argValue;
                } elseif ('service' === $argType) {
                    $result[] = ('this' === $argValue)
                        ? $this
                        : $this->$argValue;
                } else {
                    throw new ContainerException(sprintf('Unknown argument type "%s".', $argType));
                }
            }
        }

        return $result;
    }
}

