<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'areal_db';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Ошибка подключения к БД: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
