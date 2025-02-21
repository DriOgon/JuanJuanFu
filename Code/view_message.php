<?php
include 'db.php';
session_start(); // 启动会话

// 获取问题ID
$id = intval($_GET['id']);
$x = $id;

// 查询问题
$sql = "SELECT id, student_id, title, content, reward, create_time, message FROM messages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->bind_param("i", $x);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "问题不存在。";
    exit();
}

$question = $result->fetch_assoc();



$conn->close();
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($question['title']); ?> - 社区</title>
    <meta name="keywords" content="社区,问题咨询,杨青" />
    <meta name="description" content="社区问题咨询平台，用户可以提出问题并悬赏福币数。" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/base.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    <link href="css/m.css" rel="stylesheet">
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
</header>

<article>
  <div class="blogs">
    <div class="view-question">
      <h2><?php echo htmlspecialchars($question['title']); ?></h2>
      <div class="bloginfo">
          <p><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
      </div>
      <div class="autor">
      <span class="lm"><a href="/" title="CSS3|Html5" target="_blank" class="classname"><?php echo htmlspecialchars($question['student_id']); ?></a></span>
          <span class="dtime"><?php echo $question['create_time']; ?></span>
          <span class="viewnum">悬赏福币数（<a href="/"><?php echo htmlspecialchars($question['reward']); ?></a>）</span>
      </div>

      
    </div>
  </div>
</article>



<h1>留言板</h1>
    
    <!-- 留言表单 -->
    <form action="submit.php" method="POST">
        <label for="student_id">昵称:</label>
        <input type="text" name="student_id" id="student_id" required><br><br>

        <label for="message">留言:</label><br>
        <textarea id="message" name="message"></textarea><br><br>

        <input type="hidden" name="reply_to" id="reply_to" value=''>
        <input type="submit" value="提交留言">
    </form>

    <script>
    // 获取当前页面 URL 中的 id 参数
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');

    // 如果存在 id 参数，设置 reply_to 的值
        if (id) {
            document.getElementById('reply_to').value = id;
        }
    </script>

    <h2>留言列表</h2>
    <div id="messages">
        <?php
        // 连接数据库
        $conn = new mysqli('localhost', 'juanjuanfu', 'Skzj69707172@admin', '11-13');
        if ($conn->connect_error) {
            die("连接失败: " . $conn->connect_error);
        }

        // 获取所有 reply_to 等于 $x 的留言
        $sql = "SELECT * FROM messages WHERE reply_to = ? ORDER BY create_time DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("预处理语句失败: " . $conn->error);
        }
        $stmt->bind_param("i", $x); // 绑定参数 $x
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div style='border: 1px solid #000; margin-bottom: 10px; padding: 10px;'>";
                echo "<p><strong>" . htmlspecialchars($row['student_id']) . ":</strong></p>";
                echo "<p>" . nl2br(htmlspecialchars($row['message'])) . "</p>";
                echo "<p>回复于: " . $row['create_time'] . "</p>";

                // 添加“采纳”按钮
                echo "<form action='adopt.php' method='post' style='display:inline;'>";
                echo "<input type='hidden' name='reply_to' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<input type='hidden' name='question_id' value='" . htmlspecialchars($x) . "'>";
                echo "<input type='submit' value='采纳'>";
                echo "</form>";

                echo "</div>";
            }
                
                // 显示回复内容
                $reply_sql = "SELECT * FROM messages WHERE reply_to = " . $row['id'] . " ORDER BY create_time ASC";
                $reply_result = $conn->query($reply_sql);
                if ($reply_result->num_rows > 0) {
                    while($reply_row = $reply_result->fetch_assoc()) {
                        echo "<div style='margin-left: 20px; border: 1px dashed #000; padding: 10px;'>";
                        echo "<p><strong>" . htmlspecialchars($reply_row['student_id']) . ":</strong></p>";
                        echo "<p>" . $reply_row['message'] . "</p>";
                        echo "<p>回复于: " . $reply_row['create_time'] . "</p>";
                        echo "</div>";
                    }
                }

                echo "</div>";
            }
         else {
            echo "还没有留言.";
        }

        $conn->close();
        ?>
    </div>


<script src="js/nav.js"></script>
</body>
</html>