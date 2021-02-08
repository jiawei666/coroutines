<?php
/**
 * 任务条度 -- 协程堆栈
 */

require_once '../vendor/autoload.php';

use Coroutines\Scheduler;

function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield;
    }
}
function task() {
    yield echoTimes('foo', 10); // print foo ten times
    echo "---\n";
    yield echoTimes('bar', 5); // print bar five times
    yield; // force it to be a coroutine
}
$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();