<?php
$servername = "localhost";
$username = "11-13";
$password = "123456";
$dbname = "11-13";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error){
    die("连接失败: ".$conn->connect_error);
}
?>