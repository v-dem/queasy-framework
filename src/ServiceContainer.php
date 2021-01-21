<?php

namespace queasy\framework;

use Psr\Log\NullLogger;

class ServiceContainer
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

    public function __get($service)
    {
        if (isset($this->services[$service])) {
            return $this->services[$service];
        } elseif (isset($this->servicesConfig[$service])) {
            $serviceConfig = $this->servicesConfig[$service];
            $serviceClass = $serviceConfig['class'];

            $args = array();
            if (isset($serviceConfig['construct'])) {
                if (is_array($serviceConfig['construct'])) {
                    foreach($serviceConfig['construct'] as $argConfig) {
                        if (!is_array($argConfig)) {
                            throw new Exception(sprintf('Service "%s": Constructor argument declaration must be of type "array", "%s" given.', $service, gettype($argConfig)));
                        }

                        if (!isset($argConfig['value'])) {
                            throw new Exception(sprintf('Service "%s": Missing value in constructor argument.', $service));
                        }

                        $type = isset($argConfig['type'])
                            ? $argConfig['type']
                            : 'value';

                        $value = $argConfig['value'];

                        switch ($type) {
                            case 'service':
                                $args[] = 
                                break;

                            case 'value':
                            default:
                                
                                break;
                        }
                    }
                } else {
                    throw new Exception();
                }
            }
        }
    }
}

