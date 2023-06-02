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