<?php

$socket = socket_create(AF_INET, SOCK_STREAM, 0);
socket_bind($socket, "127.0.0.1", 5000);
socket_listen($socket, 10);
