<?php
require_once '../vendor/autoload.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$client = new GuzzleHttp\Client(['timeout' => 20]);

$iterator = function () use ($client) {
    $index = 0;
    while (true) {
        if ($index === 10) {
            break;
        }

        $url = 'http://127.0.0.1:8000/?' . $index++;
        $request = new Request('GET', $url, []);

        echo "Queuing $url @ " . date('Y-m-d H:i:s') . PHP_EOL;

        yield $client
            ->sendAsync($request)
            ->then(function (Response $response) use ($request) {
                return [$request, $response];
            });

    }
};

$promise = \GuzzleHttp\Promise\each_limit(
    $iterator(),
    10,  /// concurrency,
    function ($result, $index) {
        /** @var GuzzleHttp\Psr7\Request $request */
        list($request, $response) = $result;
        echo (string)$request->getUri() . ' completed ' . PHP_EOL;
    }
);

$promise->wait();

