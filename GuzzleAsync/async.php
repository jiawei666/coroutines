<?php
require_once '../vendor/autoload.php';


use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;


use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client();

// Initiate each request but do not block
$promises = [
    0 => $client->getAsync('http://127.0.0.1:8000/?1'),
    1 => $client->getAsync('http://127.0.0.1:8000/?2'),
    2 => $client->getAsync('http://127.0.0.1:8000/?3'),
    3 => $client->getAsync('http://127.0.0.1:8000/?4')
];

// Wait on all of the requests to complete.
$results = Promise\unwrap($promises);

// You can access each result using the key provided to the unwrap
// function.
var_dump($results[0]->getStatusCode());


echo 'endddddd';