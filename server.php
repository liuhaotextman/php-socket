<?php

function basicSocket()
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!socket_bind($socket, '127.0.0.1', 5000)) {
        throw new AssertionError('listen port error, port:' . 5000);
    }

    if (socket_listen($socket, 4) == false) {
        throw new AssertionError('listen port error, port:' . 5000);
    }

    while (true) {
        $acceptResource = socket_accept($socket);
        if ($acceptResource !== false) {
            $string = socket_read($acceptResource, 1024);

            if ($string != false) {
                $returnClient = 'server receive is : ' . $string . PHP_EOL;
                socket_write($acceptResource, $returnClient, strlen($returnClient));
            } else {
                echo 'socket_read is fail';
            }

            socket_close($acceptResource);
        }
    }
}

function oneClientSocket()
{
    $host = '0.0.0.0';
    $port = 9999;
    $listenSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($listenSocket, $host, $port);

    socket_listen($listenSocket);
    while (true) {
        $connectionSocket = socket_accept($listenSocket);
        $msg = "hello world \r\n";
        socket_write($connectionSocket, $msg, strlen($msg));
        socket_close($connectionSocket);
    }
    //socket_close($listenSocket);
}

function manyClientSocket()
{
    $host = '0.0.0.0';
    $port = 9999;

    $listenSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($listenSocket, $host, $port);
    socket_listen($listenSocket);

    while (true) {
        $connectionSocket = socket_accept($listenSocket);
        $pid = pcntl_fork();
        if (0 == $pid) {
            $msg = "hello world\r\n";
            socket_write($connectionSocket, $msg, strlen($msg));
            echo time() . ': a new client' . PHP_EOL;
            sleep(5);
            socket_close($connectionSocket);
            exit;
        }
    }
    //socket_close($listenSocket);
}

function repeatClientSocket()
{
    $host = '0.0.0.0';
    $port = 9999;
    $listenSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($listenSocket, $host, $port);
    socket_listen($listenSocket);
    cli_set_process_title('php server master process');
    for ($i = 1; $i <= 10; $i++) {
        $pid = pcntl_fork();
        if (0 == $pid) {
            cli_set_process_title('php server worker process');
            while (true) {
                $connSocket = socket_accept($listenSocket);
                $msg = "hello world\r\n";
                socket_write($connSocket, $msg, strlen($msg));
                socket_close($connSocket);
            }
        }
    }

    while (true) {
        sleep(1);
    }

    //socket_close($listenSocket);
}

repeatClientSocket();