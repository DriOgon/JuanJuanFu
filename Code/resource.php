<?php
include 'db.php'; // 确保你已经正确配置了数据库连接

$upload_dir = 'uploads/';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['major'])) {
    $file_name = $_FILES['file']['name'];
    $major = $_POST['major'];

    // 如果文件名是 GBK 编码，转换为 UTF-8
    if (mb_detect_encoding($file_name, 'UTF-8', true) === false) {
        $file_name = mb_convert_encoding($file_name, 'UTF-8', 'GBK');
    }

    $upload_file = $upload_dir . $file_name;

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

    if (in_array($_FILES['file']['type'], $allowed_types)) {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
            // 将文件信息插入数据库
            $stmt = $conn->prepare("INSERT INTO files (filename, filepath, category) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $file_name, $upload_file, $major);
            if ($stmt->execute()) {
                echo "文件上传成功！";
            } else {
                echo "文件信息插入数据库失败！";
            }
        } else {
            echo "文件上传失败！";
        }
    } else {
        echo "不允许的文件类型！";
    }
}

// 处理搜索请求
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = trim($_POST['search']);
}

// 处理分类筛选
$selected_major = isset($_GET['major']) ? $_GET['major'] : '';
$where_clause = "";
$params = [];

if (!empty($selected_major) && $selected_major !== 'all') {
    $where_clause = "WHERE category = ?";
    $params[] = $selected_major;
}

if (!empty($search_query)) {
    if (!empty($where_clause)) {
        $where_clause .= " AND filename LIKE CONCAT('%', ?, '%')";
    } else {
        $where_clause = "WHERE filename LIKE CONCAT('%', ?, '%')";
    }
    $params[] = $search_query;
}

$sql = "SELECT filename, filepath FROM files $where_clause ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>资源中心</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #E6F2FF;
        color: #333;
        padding: 20px;
    }
    .container {
        display: flex;
    }
	.sidebar ul {
	    max-height: 650px; /* 设置固定的高度 */
	    overflow-y: auto; /* 启用纵向滚动条 */
	}
    .sidebar, .right-sidebar {
        width: 20%;
        background-color: #FFFFFF;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .sidebar {
        margin-right: 20px;
    }
    .right-sidebar {
        margin-left: 20px;
    }

    .sidebar h3, .right-sidebar h3 {
        color: #007BFF;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .sidebar ul li a, .right-sidebar ul li a {
        color: #007BFF;
        text-decoration: none;
    }

    .sidebar ul li a:hover, .right-sidebar ul li a:hover {
        text-decoration: underline;
    }

    .sidebar h3 a, .right-sidebar h3 a {
        color: #007BFF;
        text-decoration: none;
    }

    .sidebar h3 a:hover, .right-sidebar h3 a:hover {
        text-decoration: underline;
    }

    .main-content {
        width: 60%;
        background-color: #FFFFFF;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    input[type="text"], input[type="file"] {
        margin: 5px 0;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        width: 90%;
    }

    input[type="submit"] {
        background-color: #007BFF;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #0056b3;
    }

    .file-list li {
        display: block;
        padding: 8px 15px;
        background-color: #E6F2FF;
        border-radius: 5px;
        border: 1px solid #007BFF;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    .file-list li:hover {
        background-color: #B3D7FF;
    }

    .file-list a {
        text-decoration: none;
        color: #007BFF;
        font-weight: bold;
        font-size: 16px;
    }

    .file-list a:hover {
        text-decoration: underline;
    }

    .sidebar ul li, .right-sidebar ul li {
        display: list-item;
        padding: 8px 15px;
        background-color: #E6F2FF;
        border-radius: 5px;
        margin-top: 10px;
        transition: background-color 0.3s ease;
        list-style-type: none; 
        position: relative; 
    }

    .sidebar ul li::before, .right-sidebar ul li::before {
        content: '\2022'; 
        color: #007BFF; 
        font-size: 20px;
        position: absolute;
        left: -20px;
        top: 50%;
        transform: translateY(-50%);
    }

    .sidebar ul li a, .right-sidebar ul li a {
        color: #007BFF;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
    }

    .sidebar ul li a:hover, .right-sidebar ul li a:hover {
        text-decoration: underline;
    }

    h2 {
        color: #007BFF;
        font-family: 'Roboto', sans-serif;
    }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h3><a href="/chat.php">Chat AI&nbsp;&nbsp;&nbsp;&nbsp;<-</a></h3>
        <h3><a href="/me.php">个人中心 <-</a></h3>
        <h3><a href="/community.php">福卷卷社区 <-</a></h3>
        <h3>所有类别</h3>
        <ul>
            <li><a href="?major=all">全部</a></li>
            <li><a href="?major=经济管理学">经济管理学</a></li>
            <li><a href="?major=外交政治学">外交政治学</a></li>
            <li><a href="?major=国际新闻与传播">国际新闻与传播</a></li>
            <li><a href="?major=马克思主义理论">马克思主义理论</a></li>
            <li><a href="?major=计算机科学与技术">计算机科学与技术</a></li>
            <li><a href="?major=电气工程及其自动化">电气工程及其自动化</a></li>
            <li><a href="?major=化学测量学与技术">化学测量学与技术</a></li>
            <li><a href="?major=自然地理与资源环境">自然地理与资源环境</a></li>
            <li><a href="?major=CET4/6">CET4/6</a></li>
            <li><a href="?major=教材PDF/PPT">教材PDF/PPT</a></li>
            <li><a href="?major=机器人竞赛论文">机器人竞赛论文</a></li>
            <li><a href="?major=SRTP本科生研究资料">SRTP本科生研究资料</a></li>
			<li><a href="?major=大学物理学">大学物理学</a></li>
			<li><a href="?major=土木工程">土木工程</a></li>
            <li>......</li>
        </ul>
    </div>

    <div class="main-content">
    <h2>资源中心</h2>
    <ul class="file-list">
        <?php
        foreach ($files as $file) {
            echo "<li><a href='$file[filepath]' target='_blank'>$file[filename]</a></li>";
        }
        ?>
    </ul>
</div>

<div class="right-sidebar">
        <h3>上传资源领取福币！</h3> 
        <form action="" method="post" enctype="multipart/form-data">
    <label for="file" style="color:#007BFF;">选择文件:</label>
            <input type="file" name="file" id="file" required>
            <br>
            <label for="major">选择类别:</label>
            <select name="major" id="major" required>
                <option value="经济管理学">经济管理学</option>
                <option value="外交政治学">外交政治学</option>
                <option value="国际新闻与传播">国际新闻与传播</option>
                <option value="马克思主义理论">马克思主义理论</option>
                <option value="计算机科学与技术">计算机科学与技术</option>
                <option value="电气工程及其自动化">电气工程及其自动化</option> 
                <option value="化学测量学与技术">化学测量学与技术</option>
                <option value="自然地理与资源环境">自然地理与资源环境</option>
				<option value="CET4/6">CET4/6</option>
				<option value="教材PDF/PPT">教材PDF/PPT</option>
				<option value="机器人竞赛论文">机器人竞赛论文</option>
				<option value="SRTP本科生研究资料">SRTP本科生研究资料</option>
				<option value="大学物理学">大学物理学</option>
				<option value="土木工程">土木工程</option>
            </select>
            <br>
            <input type="submit" value="上传">
        </form>
        <br><br><br>

        <form action="" method="post">
            <label for="search" style="color:#007BFF;">搜索文件名:</label>
            <input type="text" name="search" id="search" value="<?php echo $search_query; ?>">
            <input type="submit" value="搜索">
        </form>
        <br>
        
        
    </div>
</div>
</body>
</html>