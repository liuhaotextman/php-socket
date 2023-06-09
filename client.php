<?php

function clientSend()
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 6, 'usec' => 0]);

    if (!socket_connect($socket, '127.0.0.1', 5000)) {
        echo 'connect fail message:'.socket_strerror(socket_last_error());
    } else {
        $message = 'i love socket';
        $message = mb_convert_encoding($message, 'GBK', 'UTF-8');
        if (!socket_write($socket, $message, strlen($message))) {
            echo 'fail to write'.socket_strerror(socket_last_error());
        } else {
            echo 'client write success' . PHP_EOL;
            while ($callback = socket_read($socket, 1024)) {
                echo 'server return message is :' . PHP_EOL . $callback;
            }
        }
    }
    socket_close($socket);
}

function forks()
{
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('fork error');
    } elseif ($pid > 0) {
        exit('parent process.');
    }

    if (!posix_setsid()) {
        exit( ' set sid error. ' );
    }

    $pid = pcntl_fork();
    if ($pid < 0) {
        exit(' fork error');
    } elseif ($pid > 0) {
        exit(' parent process ');
    }

    for ($i = 1; $i <= 100; $i++) {
        sleep(1);
        file_put_contents('daemon.log', $i, FILE_APPEND);
    }
}

forks();

