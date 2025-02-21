<?php
// 连接数据库
session_start(); // 启动会话

$conn = new mysqli('localhost', 'juanjuanfu', 'Skzj69707172@admin', '11-13');
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}


// 获取表单数据
$student_id = $_POST['student_id'];
$message = $_POST['message'];
$reply_to = empty($_POST['reply_to']) ? 'NULL' : $_POST['reply_to'];

// 插入留言
$sql = "INSERT INTO messages (student_id, message, reply_to,name,major,content,title) VALUES ('$student_id', '$message', $reply_to,'xxx','jsj','aaa','qqq')";
if ($conn->query($sql) === TRUE) {
    header("Location:view_message.php?id=" . $reply_to);
} else {
    echo "错误: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
