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

            if (is_string($handler)) { // Class name
                $controller = new $handler($this);
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
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return $this->page500($request);
        }
    }

    protected function page404()
    {
        return $this->http->responseFactory->createResponse(404)
            ->withBody($this->http->streamFactory->createStream('The requested URL was not found on this server.'));
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

    protected function createResponse($contents)
    {
        return $this->http->responseFactory->createResponse()
            ->withBody($this->http->streamFactory->createStream($contents));
    }

    protected function createRequestFromGlobals()
    {
        $request = $this->http->serverRequestFactory->createServerRequest(
            isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET',
            $this->detectUri(),
            $_SERVER
        );

        return $request
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles($this->normalizeFiles($_FILES));
    }

    private function detectUri(): UriInterface
    {
        $scheme = $this->detectScheme();

        if (isset($_SERVER['HTTP_HOST'])) {
            $authority = $_SERVER['HTTP_HOST'];
        } else {
            $authority = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';

            $port = (int) (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80);

            $defaultPort = ($scheme === 'https')
                ? 443
                : 80;

            if ($port !== $defaultPort) {
                $authority .= ':' . $port;
            }
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

        return new Uri(
            $scheme . '://' . $authority . $requestUri
        );
    }

    private function detectScheme(): string
    {
        if (
            isset($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== '' &&
            strtolower($_SERVER['HTTPS']) !== 'off'
        ) {
            return 'https';
        }

        return 'http';
    }

    private function normalizeFiles(array $files): array
    {
        $normalized = array();
        foreach ($files as $key => $value) {
            $normalized[$key] = $this->normalizeFile($value);
        }

        return $normalized;
    }

    private function normalizeFile(array $file)
    {
        if ($this->isUploadedFileSpec($file)) {
            return new UploadedFile(
                $file['tmp_name'],
                $file['size'],
                $file['error'],
                isset($file['name']) ? $file['name'] : null,
                isset($file['type']) ? $file['type'] : null
            );
        }

        $normalized = array();
        foreach ($file as $key => $value) {
            $normalized[$key] = is_array($value)
                ? $this->normalizeFile($value)
                : $value;
        }

        return $normalized;
    }

    private function isUploadedFileSpec(array $file): bool
    {
        return isset(
            $file['tmp_name'],
            $file['size'],
            $file['error']
        );
    }
}

