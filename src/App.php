<?php

namespace queasy\framework;

use Exception;
use InvalidArgumentException;

use queasy\container\ServiceContainer;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareInterface;

use queasy\helper\System;

class App extends ServiceContainer
{
    protected $config;

    public function __construct($config)
    {
        if (!isset($config['logger'])) {
            $config['logger'] = new NullLogger();
        }

        parent::__construct($config);
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

            if (is_string($handler)) { // Class name
                $controller = new $handler($this);
                $method = strtolower($this->request->getMethod());
                if (!is_callable([ $controller, $method ])) { // Check that method exists and is public
                    return $this->page501($this->request);
                }

                $handler = array($controller, $method);
            }

            $closure = static function() use($handler, $arguments) {
                return System::callUserFuncArray($handler, $arguments);
            };

            $output = isset($this->middleware)
                ? $this->middleware->handle($this->request, $closure)
                : $closure();

            return $output;
        } catch (RouteNotFoundException $e) {
            return $this->page404($this->request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return $this->page500($this->request);
        }
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

    protected function page501()
    {
        $this->stream->write('Not implemented.');

        return $this->response
            ->withBody($this->stream)
            ->withStatus(501);
    }
}

