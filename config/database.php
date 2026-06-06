<?php

$database_host = 'localhost';

$database_username = 'root';

$database_password = '';

$database_name = 'secure_mobile_center';


$connection = new mysqli(
    $database_host,
    $database_username,
    $database_password,
    $database_name
);


if ($connection->connect_error) {
    die('Database Connection Failed');
}


$connection->set_charset('utf8mb4');