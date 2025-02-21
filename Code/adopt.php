<?php
// 连接数据库
$conn = new mysqli('localhost', 'juanjuanfu', 'Skzj69707172@admin', '11-13');
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 获取表单数据
$reply_to = intval($_POST['reply_to']);
$question_id = intval($_POST['question_id']);

// 查询被采纳的回复用户的 student_id
$reply_sql = "SELECT student_id FROM messages WHERE id = ?";
$reply_stmt = $conn->prepare($reply_sql);
$reply_stmt->bind_param("i", $reply_to);
$reply_stmt->execute();
$reply_result = $reply_stmt->get_result();
if ($reply_result->num_rows > 0) {
    $reply_row = $reply_result->fetch_assoc();
    $reply_student_id = $reply_row['student_id'];
} else {
    die("回复用户不存在。");
}
$reply_stmt->close();

// 查询提问用户的 student_id
$question_sql = "SELECT student_id FROM messages WHERE id = ?";
$question_stmt = $conn->prepare($question_sql);
$question_stmt->bind_param("i", $question_id);
$question_stmt->execute();
$question_result = $question_stmt->get_result();
if ($question_result->num_rows > 0) {
    $question_row = $question_result->fetch_assoc();
    $question_student_id = $question_row['student_id'];
} else {
    die("提问用户不存在。");
}
$question_stmt->close();

// 增加被采纳用户的 coins
$update_reply_sql = "UPDATE users SET coins = coins + 10 WHERE student_id = ?";
$update_reply_stmt = $conn->prepare($update_reply_sql);
$update_reply_stmt->bind_param("s", $reply_student_id);
if (!$update_reply_stmt->execute()) {
    die("更新被采纳用户的 coins 失败: " . $update_reply_stmt->error);
}
$update_reply_stmt->close();

// 减少提问用户的 coins
$update_question_sql = "UPDATE users SET coins = coins - 10 WHERE student_id = ?";
$update_question_stmt = $conn->prepare($update_question_sql);
$update_question_stmt->bind_param("s", $question_student_id);
if (!$update_question_stmt->execute()) {
    die("更新提问用户的 coins 失败: " . $update_question_stmt->error);
}
$update_question_stmt->close();

// 更新被采纳回复的 reply_to 字段为 NULL
$update_reply_to_sql = "UPDATE messages SET reply_to = NULL WHERE id = ?";
$update_reply_to_stmt = $conn->prepare($update_reply_to_sql);
$update_reply_to_stmt->bind_param("i", $reply_to);
if (!$update_reply_to_stmt->execute()) {
    die("更新 reply_to 字段失败: " . $update_reply_to_stmt->error);
}
$update_reply_to_stmt->close();

echo "采纳成功！";

$conn->close();
?>