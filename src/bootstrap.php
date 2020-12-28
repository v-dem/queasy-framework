<?php

$appClass = $config['appClass'];
$app = new $appClass($config);

if (isset($config['loggerClass'])) {
    $loggerClass = $config['loggerClass'];
    $logger = new $loggerClass($config['logger']);
} else {
    $logger = new Psr\Log\NullLogger();
}

$app->setLogger($logger);

$requestClass = isset($config['requestClass'])
    ? $config['requestClass']
    : 'queasy\http\ServerRequest';
$request = new $requestClass();

$response = $app->handle($request);

echo $response;

$logger->debug('Execution time: ' . (microtime(true) - QUEASY_START_TIME));

