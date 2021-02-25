<?php
require_once '../vendor/autoload.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

$iterator = function() {
    $index = 0;
    while (true) {
        $client = new GuzzleHttp\Client(['timeout' => 20]);;
        $url = 'http://127.0.0.1:8000/?' . $index++;
        $request = new Request('GET',$url, []);
        echo "Queuing $url @ " . date('Y-m-d H:i:s') . PHP_EOL;
        yield $client
            ->sendAsync($request)
            ->then(function(Response $response) use ($request) {
                return [$request, $response];
            });
    }
};

$promise = \GuzzleHttp\Promise\each_limit(
    $iterator(),
    10,  /// concurrency,
    function($result, $index) {
        /** GuzzleHttp\Psr7\Request $request */
        list($request, $response) = $result;
        echo (string) $request->getUri() . ' completed '.PHP_EOL;
    },
    function(RequestException $reason, $index) {
        // left empty for brevity
    }
);
$promise->wait();

