<?php
/**
 * 调度器
 */
namespace Coroutines;

use SplQueue;
use Generator;

class Scheduler {
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;
    // resourceID => [socket, tasks]
    protected $waitingForRead = [];
    protected $waitingForWrite = [];

    public function __construct() {
        $this->taskQueue = new SplQueue();
    }

    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task) {
        $this->taskQueue->enqueue($task);
    }

    public function run() {
        while (!$this->taskQueue->isEmpty()) {
//            var_dump('队列任务总数：' . $this->taskQueue->count());
            $task = $this->taskQueue->dequeue();
//            var_dump('当前任务id:' . $task->taskId);
            $retval = $task->run();
            if ($retval instanceof SystemCall) {
                $retval($task, $this);
                continue;
            }
            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }

    public function killTask($tid) {
        if (!isset($this->taskMap[$tid])) {
            return false;
        }
        unset($this->taskMap[$tid]);
        // This is a bit ugly and could be optimized so it does not have to walk the queue,
        // but assuming that killing tasks is rather rare I won't bother with it now
        foreach ($this->taskQueue as $i => $task) {
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }
        return true;
    }

    public function waitForRead($socket, Task $task) {
        if (isset($this->waitingForRead[(int) $socket])) {
            $this->waitingForRead[(int) $socket][1][] = $task;
        } else {
            $this->waitingForRead[(int) $socket] = [$socket, [$task]];
        }
    }
    public function waitForWrite($socket, Task $task) {
        if (isset($this->waitingForWrite[(int) $socket])) {
            $this->waitingForWrite[(int) $socket][1][] = $task;
        } else {
            $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
        }
    }

    protected function ioPoll($timeout) {
        var_dump('111');
        $rSocks = [];
        foreach ($this->waitingForRead as list($socket)) {
            $rSocks[] = $socket;
        }
        $wSocks = [];
        foreach ($this->waitingForWrite as list($socket)) {
            $wSocks[] = $socket;
        }
        $eSocks = []; // dummy

        // 流选择函数(stream_select)，会保留那些状态改变了的数组元素，直等到某个套接口准备就绪，才会继续运行后面的代码
        if (!stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
            var_dump('return');
            return;
        }
        var_dump('通过');
        foreach ($rSocks as $socket) {
            list(, $tasks) = $this->waitingForRead[(int) $socket];
            unset($this->waitingForRead[(int) $socket]);
            foreach ($tasks as $task) {
                var_dump('增加任务r');
                $this->schedule($task);
            }
        }
        foreach ($wSocks as $socket) {
            list(, $tasks) = $this->waitingForWrite[(int) $socket];
            unset($this->waitingForWrite[(int) $socket]);
            foreach ($tasks as $task) {
                var_dump('增加任务w');
                $this->schedule($task);
            }
        }
    }

    public function ioPollTask() {
        while (true) {
            if ($this->taskQueue->isEmpty()) {
                $this->ioPoll(null);
            } else {
                $this->ioPoll(0);
            }
            yield;
        }
    }
}