<?php

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json;charset=utf-8;\r\nContent-Length: ",
        'content' => 'asdasdas',
        'asdsad' => 'asdasdas',
    ],
    'http2' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json;charset=utf-8;\r\nContent-Length: ",
        'content' => 'asdasdas',
        'asdsad' => 'asdasdas',
    ],
]);

var_dump($context);
exit();

$rSocks = [];
foreach ([[1], [2], [3]] as list($socket)) {
    $rSocks[] = $socket;
}

var_dump($rSocks);

//function server($port) {
//    echo "Starting server at port $port...\n";
//    $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
//    var_dump($socket);
//    if (!$socket) throw new Exception($errStr, $errNo);
//    stream_set_blocking($socket, 0);
//
//    $clientSocket = stream_socket_accept($socket, 0);
//    var_dump($clientSocket);
////    while (true) {
////        yield waitForRead($socket);
////        $clientSocket = stream_socket_accept($socket, 0);
////        yield newTask(handleClient($clientSocket));
////    }
//}
//
//server(8000);