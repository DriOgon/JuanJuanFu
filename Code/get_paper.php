<?php
include 'db.php';

// 设置响应头为 JSON
header('Content-Type: application/json');

// 查询所有问题
$sql = "SELECT id,major, content, reward, created_at FROM paper ORDER BY created_at DESC";
$result = $conn->query($sql);

$questions = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $questions[] = array(
            'id' => $row['id'],
            'major' => $row['major'],
            'content' => $row['content'],
            'reward' => $row['reward'],
            'created_at' => $row['created_at']
        );
    }
}

echo json_encode($questions);

$conn->close();
?>