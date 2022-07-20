<?php

namespace queasy\framework;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareInterface;

use queasy\helper\System;

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

            if (!$this->has('request')) {
                throw new Exception('No "request" service configured.');
            }

            if (!$this->has('response')) {
                throw new Exception('No "response" service configured.');
            }

            if (!$this->has('stream')) {
                throw new Exception('No "stream" service configured.');
            }

            $route = $this->router->route($this->request);
            $handler = $route->getHandler();
            $arguments = $route->getArguments();

            if (!is_callable($handler) && !is_string($handler)) {
                throw new InvalidArgumentException(sprintf('Invalid handler type "%s".', gettype($handler)));
            }

            if (is_string($handler)) {
                $controller = new $handler($this);
                $method = strtolower($this->request->getMethod());
                $handler = array($controller, $method);
            }

            $output = System::callUserFuncArray($handler, $arguments);

            return (!is_string($output) && method_exists($this->request, 'isAjax') && $this->request->isAjax())
                ? json_encode($output)
                : $output;
        } catch (RouteNotFoundException $e) {
            return $this->page404($this->request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return $this->page500($this->request);
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
        }

        if (!isset($this->config[$serviceId])) {
            throw new NotFoundException(sprintf('Service "%s" is not configured.', $serviceId));
        }

        $serviceConfig = $this->config[$serviceId];
        $serviceClass = $serviceConfig['class'];

        // Instantiate service

        if (isset($serviceConfig['construct'])) {
            // Using declared constructor

            $args = $this->parseArgs($serviceConfig['construct']);
            if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                $serviceInstance = new $serviceClass(...$args);
            } else {
                $reflect = new ReflectionClass($serviceClass);
                $serviceInstance = $reflect->newInstanceArgs($args);
            }
        } else {
            // Using default constructor

            $serviceInstance = new $serviceClass();
        }

        // Set default logger if custom logger not configured

        if (!isset($serviceConfig['setLogger']) && ($serviceInstance instanceof LoggerAwareInterface)) {
            $serviceInstance->setLogger($this->logger);
        }

        // Call service initialization methods

        foreach ($serviceConfig as $method => $args) {
            if (('class' === $method) || ('construct' === $method)) {
                continue;
            }

            System::callUserFuncArray(array($serviceInstance, $method), $this->parseArgs($args));
        }

        $this->services[$serviceId] = $serviceInstance;

        return $serviceInstance;
    }

    protected function page404()
    {
        $this->stream->write('The requested URL was not found on this server.');

        return $this->response
            ->withBody($this->stream)
            ->withStatus(404);
    }

    protected function page500()
    {
        $this->stream->write('Internal error.');

        return $this->response
            ->withBody($this->stream)
            ->withStatus(500);
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

