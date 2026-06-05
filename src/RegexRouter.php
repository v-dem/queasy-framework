<?php

namespace queasy\framework;

use Psr\Http\Message\ServerRequestInterface;

class RegexRouter implements RouteInterface
{
    private $routes;

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    public function route(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();

        $route = $this->routeLookup($this->routes, $path);

        if (null == $route) {
            throw new RouteNotFoundException(sprintf('Route "%s" not found.', $path));
        }

        return $route;
    }

    private function routeLookup($routes, $path)
    {
        foreach ($routes as $route => $handler) {
            if (preg_match($route, $path, $matches)) {
                // TODO: Create RouteGroup class in future
                /*
                if (is_array($handler)) {
                    return $this->routeLookup($handler, $path);
                }
                */

                if ($handler instanceof \queasy\config\Config) {
                    $handler = $handler->toArray();
                }

                array_shift($matches);

                return new RouteEntry($handler, $matches);
            }
        }
    }
}

