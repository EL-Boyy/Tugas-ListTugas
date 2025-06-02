<?php
$host = 'localhost';
$db = 'crud_app';
$user = 'root'; // default user
$pass = ''; // default password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>