<?php

function processWait()
{
    $pid = pcntl_fork();
    if (0 > $pid) {
        exit('fork error');
    } elseif (0 < $pid) {
        pcntl_signal(SIGCHLD, function () use ($pid) {
            echo 'received child process exit' . PHP_EOL;
            pcntl_waitpid($pid, $status, WNOHANG);
        });
        cli_set_process_title('php father process');
        while (true) {
            pcntl_signal_dispatch();
        }
    } elseif (0 == $pid) {
        cli_set_process_title('php child process');
        sleep(20);exit;
    }
}

function processTask()
{
    umask(0);
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('fork error');
    } elseif ($pid > 0) {
        exit();
    }

    if (!posix_setsid()) {
        exit('set sid error');
    }

    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('fork error');
    } elseif ($pid > 0) {
        exit;
    }
    cli_set_process_title('php master process');

    $childPid = [];
    pcntl_signal(SIGCHLD, function () {
        global $childPid;
        $childPidNum = count($childPid);
        if ($childPidNum > 0) {
            foreach ($childPid as $pidKey => $pidItem) {
                $waitResult = pcntl_waitpid($pidItem, $status, WNOHANG);
                if ($waitResult == $pidItem || -1 == $waitResult) {
                    unset($childPid[$pidKey]);
                }
            }
        }
    });

    for ($i = 1; $i <= 5; $i++) {
        $_pid = pcntl_fork();
        if ($_pid < 0) {
            exit;
        } elseif (0 == $_pid) {
            cli_set_process_title('php worker process');
            exit();
        } elseif ($_pid > 0) {
            $childPid[] = $_pid;
        }
    }

    while (true) {
        pcntl_signal_dispatch();
        sleep(1);
    }
}

function processCommunication()
{
    $pipeFile = __DIR__ . DIRECTORY_SEPARATOR . 'test.pipe';
    if (!file_exists($pipeFile)) {
        if (!posix_mkfifo($pipeFile, 0666)) {
            exit('create pipe error.' . PHP_EOL);
        }
    }
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('fork error' . PHP_EOL);
    } elseif (0 == $pid) {
        $file = fopen($pipeFile, 'w');
        fwrite($file, "hello world.");
        exit;
    } elseif ($pid > 0) {
        $file = fopen($pipeFile, 'r');
        $content = fread($file, 1024);
        echo $content . PHP_EOL;
        pcntl_wait($status);
    }
}

function processMessage()
{
    $key = ftok(__DIR__, 'a');
    $queue = msg_get_queue($key, 666);
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('fork error' . PHP_EOL);
    } elseif ($pid > 0) {
        msg_receive($queue, 0, $msgType, 1024, $message);
        echo $message . PHP_EOL;
        msg_remove_queue($queue);
        pcntl_wait($status);
    } elseif ($pid == 0) {
        msg_send($queue, 1, "hello world");
        exit;
    }
}

processMessage();