<?php

/**
 * 系统调用 - 任务调度进阶
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

function childTask() {
    $tid = (yield getTaskId());
    while (true) {
        echo "Child task $tid still alive!\n";
        sleep(1);
        yield;
    }
}
function task() {
    $tid = (yield getTaskId());
    $childTid = (yield newTask(childTask()));
    for ($i = 1; $i <= 6; ++$i) {
        echo "Parent task $tid iteration $i.\n";
        yield;
        if ($i == 3) yield killTask($childTid);
    }
}
$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();