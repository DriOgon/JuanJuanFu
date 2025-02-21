<?php
// 启用错误报告（开发阶段使用，生产环境建议关闭）
session_start(); // 启动会话

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// 处理问题提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取并验证用户输入
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $reward = isset($_POST['reward']) ? intval($_POST['reward']) : 0; // 如果没有提供，默认为0

    // 验证标题和内容是否为空
    if ($title === '' || $content === '') {
        echo "标题和内容不能为空。";
        exit();
    }

    $student_id = $_SESSION['student_id'];
  

    // 准备 SQL 语句
    $stmt = $conn->prepare("INSERT INTO messages (student_id, title, content, reward,reply_to,name,major) VALUES (?, ?, ?, ?,NULL,'xxx','jsj')");
    if (!$stmt) {
        // 输出数据库错误信息
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    // 绑定参数
    // 参数类型说明：
    // "i" - integer
    // "s" - string
    // "siis" 对应 user_id (int), title (string), content (string), reward (int)
    $stmt->bind_param("issi", $student_id, $title, $content, $reward);

    // 执行 SQL 语句
    if ($stmt->execute()) {
        // 重定向回首页
        header("Location: community.php");
        exit();
    } else {
        // 输出执行错误信息
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    // 关闭语句
    $stmt->close();
} else {
    echo "无效的请求方式。";
}

$conn->close();
?>