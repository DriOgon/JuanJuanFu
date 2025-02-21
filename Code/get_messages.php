<?php
include 'db.php';

// 设置响应头为 JSON
header('Content-Type: application/json; charset=utf-8');

// 查询所有留言
$sql = "SELECT id, student_id, title, content, reward,reply_to , create_time FROM messages ORDER BY create_time DESC";
$result = $conn->query($sql);

$questions = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $questions[] = array(
            'id' => $row['id'],
            'student_id' => $row['student_id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'reward' => $row['reward'],
            'reply_to' => $row['reply_to'],
            'create_time' => $row['create_time']
        );
    }
    echo json_encode($questions);
} else {
    echo json_encode(array("status" => "error", "message" => "没有找到留言。"));
}

$conn->close();
?>