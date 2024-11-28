<?php
$servername = "localhost";
$username = "juanjuanfu";
$password = "Skzj69707172@admin";
$dbname = "11-13";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error){
    die("连接失败: ".$conn->connect_error);
}
?>