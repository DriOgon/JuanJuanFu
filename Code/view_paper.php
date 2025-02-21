<?php
include 'db.php'; // 确保你已经正确配置了数据库连接

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 启动会话
session_start();

// 获取问题ID
$id = intval($_GET['id']);

// 查询问题
$sql = "SELECT id, user_id, major, content, reward, created_at FROM paper WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "问题不存在。";
    exit();
}

$question = $result->fetch_assoc();

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // 检查文件是否上传成功
    if ($file['error'] === UPLOAD_ERR_OK) {
        // 允许的文件类型
        $allowed_types = [
            'image/jpeg', 
            'image/png',  
            'application/pdf', 
            'application/zip',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain'
        ];

        // 检查文件类型
        if (in_array($file['type'], $allowed_types)) {
            // 处理文件名（防止中文乱码）
            $file_name = basename($file['name']);
            // 使用 iconv 进行编码转换
            $file_name = iconv('GBK', 'UTF-8//IGNORE', $file_name);
            
            $file_name = $file['name']; // 或者使用其他处理方式
            

            // 生成唯一的文件名
            $unique_name = uniqid() . '-' . $file_name;
            $upload_file = 'helps/' . $unique_name;
            $user_id = $_SESSION['student_id'];

            // 移动上传的文件到目标目录
            if (move_uploaded_file($file['tmp_name'], $upload_file)) {
                // 获取 user_id
                if (isset($_SESSION['student_id'])) {
                    $user_id = $_SESSION['student_id'];
                } else {
                    // 如果用户未登录，可以设置为 1，或者根据需求处理
                    $user_id= 1;
                }

                // 插入文件信息到数据库
                
                $file_sql = "INSERT INTO files2 (user_id, filename, filepath, question_id) VALUES (?, ?, ?, ?)";
                $file_stmt = $conn->prepare($file_sql);
                if (!$file_stmt) {
                    die("文件插入预处理语句失败: " . $conn->error);
                }
                $file_stmt->bind_param("issi", $user_id, $unique_name, $upload_file, $id);
                if ($file_stmt->execute()) {
                    echo "<script>alert('文件上传成功！');</script>";
                } else {
                    echo "文件信息保存失败: " . $file_stmt->error;
                }
                $file_stmt->close();
            } else {
                echo "文件上传失败。";
            }

        } else {
            echo "不允许的文件类型。";
        }
    } else {
        echo "文件上传失败，错误代码：" . $file['error'];
    }
}

// 查询文件
$file_sql = "SELECT id, filename, filepath, created_at FROM files2 WHERE question_id = ?";
$file_stmt = $conn->prepare($file_sql);
$file_stmt->bind_param("i", $id);
$file_stmt->execute();
$file_result = $file_stmt->get_result();

$files = array();
while ($file = $file_result->fetch_assoc()) {
    $files[] = $file;
}

$conn->close();
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($question['title']); ?> - 社区</title>
    <meta name="keywords" content="社区,问题咨询,试卷求助,杨青" />
    <meta name="description" content="社区问题咨询和试卷求助平台，用户可以提出问题或试卷求助并悬赏福币数。" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/base.css" rel="stylesheet">
    <link href="css/view_question.css" rel="stylesheet">
</head>
<body>
<header>
  <div class="tophead">
    <div class="logo"><a href="resource.php">社区</a></div>
    <div id="mnav">
      <h2><span class="navicon"></span></h2>
      <ul>
        <li><a href="community.php">问题咨询</a></li>
        <li><a href="paper.php">试卷求助</a></li>
        <li><a href="ranking.html">排行榜</a></li>
     
      </ul>
    </div>
    <nav class="topnav" id="topnav">
      <ul>
        <li><a href="community.php">问题咨询</a></li>
        <li><a href="paper.php">试卷求助</a></li>
        <li><a href="ranking.html">排行榜</a></li>
       
      </ul>
    </nav>
  </div>

  <div class="blogs">
    <div class="view-question">
        <h2 style="font-size: 300%;"><?php echo htmlspecialchars($question['major']); ?></h2>
        <div class="bloginfo">
            <p style="font-size: 300%;"><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
        </div>
        <div class="autor">
        <span class="lm"><a href="/" title="CSS3|Html5" target="_blank" class="classname">用户x</a></span>
            <span class="dtime"><?php echo $question['created_at']; ?></span>
            <span class="viewnum">悬赏福币数（<a href="/"><?php echo htmlspecialchars($question['reward']); ?></a>）</span>
        </div>
    </div>
</div>

  <div class="form-container">
    
    <form action="view_paper.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
        <label for="file" style="font-size: 24px;">上传文件:</label><br>
        <input type="file" id="file" name="file" required style="font-size: 24px; padding: 12px;"><br><br>
        <input type="submit" value="上传文件" style="font-size: 24px; padding: 10px 20px;">
    </form>
  </div>

  <div class="files">
      <h3>已上传的文件</h3>
      <ul>
          <?php if (count($files) > 0): ?>
              <?php foreach ($files as $file): ?>
                  <li>
                      <a href="<?php echo $file['filepath']; ?>" download><?php echo htmlspecialchars($file['filename']); ?></a>
                      <span class="file-date"><?php echo $file['created_at']; ?></span>
                  </li>
              <?php endforeach; ?>
          <?php else: ?>
              <li>暂无文件上传。</li>
          <?php endif; ?>
      </ul>
  </div>
</article>

<script src="js/nav.js"></script>
</body>
</html>