<?php
session_start();
include 'db.php';

if (isset($_GET['approve'])) { // 审核通过，获取提交的id
    $file_id = $_GET['approve'];

    // 1. 获取待审核文件的相关数据
    $sql = "SELECT * FROM pending WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $file_id); // 绑定参数
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // 获取相关字段
            $filename = $row['filename'];
            $filepath = $row['filepath'];
            $category = $row['category'];
            $student_id = $row['student_id'];

            // 2. 将数据插入到 files 表
            $insert_sql = "INSERT INTO files (filename, filepath, category, student_id) VALUES (?, ?, ?, ?)";
            if ($insert_stmt = $conn->prepare($insert_sql)) {
                $insert_stmt->bind_param("ssss", $filename, $filepath, $category, $student_id);
                if ($insert_stmt->execute()) {
                    echo "<script>alert('文件已成功插入到 files 表');</script>";
                } else {
                    echo "<script>alert('插入文件到 files 表失败: " . $insert_stmt->error . "');</script>";
                }
            }

            // 3. 更新用户的福币
            $updateCoins = "UPDATE users SET coins = coins + 100 WHERE student_id = ?";
            if ($updateStmt = $conn->prepare($updateCoins)) {
                $updateStmt->bind_param("s", $student_id);
                if ($updateStmt->execute()) {
                    echo "<script>alert('用户福币更新成功');</script>";
                } else {
                    echo "<script>alert('更新福币失败: " . $updateStmt->error . "');</script>";
                }
            }

            // 4. 删除已审核的文件
            $delete_sql = "DELETE FROM pending WHERE id = ?";
            if ($delete_stmt = $conn->prepare($delete_sql)) {
                $delete_stmt->bind_param("s", $file_id);
                if ($delete_stmt->execute()) {
                    echo "<script>alert('文件已成功删除');</script>";
                } else {
                    echo "<script>alert('删除文件失败: " . $delete_stmt->error . "');</script>";
                }
            }

            // 重定向回当前页面，以便更新文件列表
            echo "<script>window.location.href = 'admin.php';</script>";
            exit;
        } else {
            echo "<script>alert('文件不存在或已被删除');</script>";
        }
    } else {
        echo "<script>alert('数据库查询准备失败');</script>";
    }
}



// 获取待审核文件列表
$sql = "SELECT * FROM pending";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员审核界面</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #E6F2FF;
            color: #333;
            padding: 20px;
        }
        .main-content {
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #007BFF;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #007BFF;
            text-align: left;
        }
        th {
            background-color: #E6F2FF;
        }
        img {
            width: 100px;
            height: 100px;
        }
        .download-link {
            color: #007BFF;
            text-decoration: none;
        }
        .download-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>待审核文件列表</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>文件名</th>
            <th>文件分类</th>
            <th>上传者学号</th>
            <th>上传时间</th>
            <th>文件详情</th>
            <th>操作</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $filename = $row['filename'];  
                $filepath = $row['filepath'];

                // 构建完整的文件下载链接
                $download_link = $filepath;

                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$filename}</td>
                        <td>{$row['category']}</td>
                        <td>{$row['student_id']}</td>
                        <td>{$row['uploaded_at']}</td>
                        <td><a href='{$download_link}' class='download-link' download>点击下载</a></td>
                        <td><a href='?approve={$row['id']}'>审核通过</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>没有待审核的文件</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
