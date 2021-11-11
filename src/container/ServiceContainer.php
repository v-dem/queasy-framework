<?php

namespace queasy\framework\container;

use Psr\Log\NullLogger;

class ServiceContainer implements ServiceContainerInterface
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
    }

    public function has($service)
    {
        return isset($this->config[$service]);
    }

    public function get($service)
    {
        if (isset($this->services[$service])) {
            return $this->services[$service];
        } elseif (isset($this->config[$service])) {
            $serviceConfig = $this->config[$service];
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

            $this->services[$service] = $serviceInstance;

            return $serviceInstance;
        } else {
            throw new NotFoundException(sprintf('Service "%s" is not configured.', $service));
        }
    }

    public function __get($service)
    {
        return $this->get($service);
    }

    public function __isset($service)
    {
        return $this->has($service);
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

