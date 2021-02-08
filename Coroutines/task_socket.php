<?php

/**
 * 任务调度(web实例) todo 这里还有很多不懂，stream函数等
 */

require_once '../vendor/autoload.php';

use Coroutines\Scheduler;
use Coroutines\SystemCall;
use Coroutines\Task;

// 系统调用 - 获取任务id，并设置为下一次发送值
function getTaskId() {
    return new SystemCall(function(Task $task, Scheduler $scheduler) {
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}

// 系统调用 - 新任务
function newTask(Generator $coroutine) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($coroutine) {
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}

// 系统调用 - 杀死任务
function killTask($tid) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) {
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}


// 系统调用 - 等待套接字读
function waitForRead($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForRead($socket, $task);
//            $scheduler->schedule($task);
        }
    );
}

// 系统调用 - 等待套接字写
function waitForWrite($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForWrite($socket, $task);
//            $scheduler->schedule($task);
        }
    );
}


function server($port) {
    echo "Starting server at port $port...\n";
    $socket = @stream_socket_server("tcp://0.0.0.0:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);
    stream_set_blocking($socket, 0);
    while (true) {
        yield waitForRead($socket);
        $clientSocket = stream_socket_accept($socket, 0);
        yield newTask(handleClient($clientSocket));
    }
}
function handleClient($socket) {
    yield waitForRead($socket);
    $data = fread($socket, 8192);
    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);
    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;
    yield waitForWrite($socket);
    fwrite($socket, $response);
    fclose($socket);
}
$scheduler = new Scheduler;
$scheduler->newTask(server(8000));
$scheduler->newTask($scheduler->ioPollTask());
$scheduler->run();