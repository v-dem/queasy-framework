<?php

namespace queasy\framework\middleware;

use queasy\framework\MiddlewareInterface;

use Psr\Http\Message\ServerRequestInterface;

use Closure;

class HoneypotProtection implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Closure $next
     */
    #![ReturnTypeWillChange]
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        $serverParams = $request->getServerParams();

        $isAjax = return isset($serverParams['HTTP_X_REQUESTED_WITH'])
            && !empty($serverParams['HTTP_X_REQUESTED_WITH'])
            && ('xmlhttprequest' === strtolower($serverParams['HTTP_X_REQUESTED_WITH']))
            || ('xmlhttprequest' === strtolower($request->getHeaderLine('X-Requested-With')))
            || ('application/json' === strtolower($request->getHeaderLine('Accept')));

        return $next($request->withAttribute('isAjax', $isAjax));
    }
}

