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

    public function handle(ServerRequestInterface $request)
    {
        try {
            $this->logger->debug('Request path: ' . $request->getUri()->getPath());

            $route = $this->router->route($request);
            $handler = $route->getHandler();
            $arguments = $route->getArguments();

            if (!is_callable($handler) && !is_string($handler)) {
                throw new InvalidArgumentException(sprintf('Invalid handler type "%s".', gettype($handler)));
            }

            if (is_string($handler)) { // Class name?
                $redirect = new Redirect(preg_replace('/index\.php.*/', '', $request->getRequestTarget()), $this->createResponse());

                $controller = new $handler($this, $request, $this->createResponse(), $redirect);
                $method = strtolower($request->getMethod());
                if (!is_callable([ $controller, $method ])) { // Check that method exists and is public
                    return $this->page501($request);
                }

                $handler = array($controller, $method);
            }

            $closure = static function() use($handler, $arguments) {
                return System::callUserFuncArray($handler, $arguments);
            };

            $output = isset($this->middleware)
                ? $this->middleware->handle($request, $closure)
                : $closure();

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

