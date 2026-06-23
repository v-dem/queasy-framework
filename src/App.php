<?php

namespace queasy\framework;

use Exception;
use InvalidArgumentException;

use queasy\container\ServiceContainer;

use Psr\Http\Message\ResponseInterface;
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

    public function handle(ServerRequestInterface $request)
    {
        try {
            $this->logger->debug('Request path: ' . $request->getUri()->getPath());

            $route = $this->router->route($request);
            $handler = $route->getHandler();
            $arguments = $route->getArguments();
            $middlewares = $route->getMiddleware();

            if (!is_callable($handler) && !is_string($handler)) {
                throw new InvalidArgumentException(sprintf('Invalid handler type "%s".', gettype($handler)));
            }

            $closure = function(ServerRequestInterface $request) use($handler, $arguments) {
                if (is_string($handler)) { // Class name, not callable
                    $resource = new $handler($this, $request, $this->createResponse());
                    if (!is_callable([ $resource, $request->getMethod() ])) { // Check that method exists and is public
                        return $this->page501($request);
                    }

                    $handler = [ $resource, $request->getMethod() ];
                } else {
                    // TODO: Implement handler function call
                }

                $response = System::callUserFuncArray($handler, $arguments);
                if (!$response) {
                    $reponse = $this->createResponse();
                }

                return $response;
            };

            if (count($middlewares) && !isset($this->middleware)) {
                throw new MiddlewareException('No middleware configured.');
            }

            $middlewaresPrepared = [];
            foreach ($middlewares as $method => $middleware) {
                if (is_int($method) || strtolower($request->getMethod()) === strtolower($method)) {
                    $middlewaresPrepared = array_merge($middlewaresPrepared, $middleware);
                }
            }

            $output = isset($this->middleware)
                ? $this->middleware->handle($middlewaresPrepared, $closure, $request)
                : $closure($request);

            return $output;
        } catch (RouteNotFoundException $e) {
            return $this->page404($request);
        } catch (AuthException $e) {
            return $this->page403($request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return $this->page500($request);
        }
    }

    protected function page404()
    {
        return $this->createResponse('The requested URL was not found on this server.')
            ->withStatus(404);
    }

    protected function page403()
    {
        return $this->createResponse('You are not authorized to view this page.')
            ->withStatus(403);
    }

    protected function page500()
    {
        return $this->createResponse('Internal error.')
            ->withStatus(500);
    }

    protected function page501()
    {
        return $this->createResponse('Not implemented.')
            ->withStatus(501);
    }

    protected function createResponse($contents = '')
    {
        return $this->http->responseFactory->createResponse()
            ->withBody($this->http->streamFactory->createStream($contents));
    }
}

