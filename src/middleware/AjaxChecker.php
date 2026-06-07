<?php

namespace queasy\framework\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use Closure;

class AjaxChecker implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\MiddlewareInterface $next
     */
    #![ReturnTypeWillChange]
    public function process(ServerRequestInterface $request, MiddlewareInterface $next)
    {
        $serverParams = $request->getServerParams();

        $isAjax = isset($serverParams['HTTP_X_REQUESTED_WITH'])
            && !empty($serverParams['HTTP_X_REQUESTED_WITH'])
            && ('xmlhttprequest' === strtolower($serverParams['HTTP_X_REQUESTED_WITH']))
            || ('xmlhttprequest' === strtolower($request->getHeaderLine('X-Requested-With')))
            || ('application/json' === strtolower($request->getHeaderLine('Accept')));

        return $next->handle($request->withAttribute('isAjax', $isAjax));
    }
}

