<?php
session_start(); // 启动会话
include 'db.php';
// 从数据库获取用户信息
$student_id = $_SESSION['student_id'];

// 处理头像上传请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('文件上传失败'); window.location.href='me.php';</script>";
        exit;
    }

    // 验证文件类型
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
        echo "<script>alert('只允许上传 JPG、PNG 或 GIF 格式的图片'); window.location.href='me.php';</script>";
        exit;
    }

    // 设置上传目录
    $upload_dir = './uploads/avatars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 生成唯一文件名
    $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $file_name = $_SESSION['student_id'] . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // 移动上传的文件
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
        // 更新据库中的头像路径
        $avatar_url = $file_path;
        $student_id = $_SESSION['student_id'];
        
        $sql = "UPDATE users SET avatar = ? WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $avatar_url, $student_id);
        
        if ($stmt->execute()) {
            echo "<script>window.location.href='me.php';</script>";
        } else {
            echo "<script>alert('数据库更新失败'); window.location.href='me.php';</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('文件保存失败'); window.location.href='me.php';</script>";
    }
    
    $conn->close();
    exit;
}

// 处理个人简介更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_intro'])) {
    $introduction = trim($_POST['introduction']);
    $student_id = $_SESSION['student_id'];
    
    $sql = "UPDATE users SET introduction = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $introduction, $student_id);
    
    if ($stmt->execute()) {
        header("Location: me.php");
    } else {
        echo "<script>alert('更新失败'); window.location.href='me.php';</script>";
    }
    exit;
}

// 直接使用 query() 执行 SQL 查询
$sql = "SELECT name, major, student_id, coins, introduction, avatar FROM users WHERE student_id = '$student_id'";
$result = $conn->query($sql);
if (!$result) {
    die("查询失败: " . $conn->error);
}
$userData = $result->fetch_assoc();

// 获取用户的收藏、下载、关注和浏览历史
$collectionsSql = "SELECT * FROM collections WHERE student_id = '$student_id'";
$collectionsResult = $conn->query($collectionsSql);

$downloadsSql = "SELECT * FROM downloads WHERE student_id = '$student_id'";
$downloadsResult = $conn->query($downloadsSql);

$followsSql = "SELECT * FROM follows WHERE student_id = '$student_id'";
$followsResult = $conn->query($followsSql);

$historySql = "SELECT * FROM history WHERE student_id = '$student_id'";
$historyResult = $conn->query($historySql);

// 不需要关闭语句，只需要关闭连接
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人主页</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Microsoft YaHei", sans-serif;
            background: #e6f3ff;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* 左侧导航栏样式 */
        .sidebar {
            width: 200px;
            background: #ffffff;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
        }

        .logo {
            padding: 0 20px;
            margin-bottom: 30px;
            color: #1e90ff;
            font-size: 20px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            padding: 12px 20px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s;
        }

        .nav-item:hover {
            background: #f0f7ff;
            color: #1e90ff;
        }

        .nav-item.active {
            background: #f0f7ff;
            color: #1e90ff;
            font-weight: bold;
        }

        /* 主要内容区域样式 */
        .main-content {
            margin-left: 200px;
            flex: 1;
            padding: 20px;
        }

        /* 顶部横幅图片 */
        .banner {
            width: 100%;
            height: 200px;
            overflow: hidden;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(30,144,255,0.2);
        }

        .banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* 个人信息区域 */
        .profile-container {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(30,144,255,0.2);
        }

        .profile-left {
            text-align: center;
        }

        .avatar {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            border: 3px solid #f0f7ff;
            border-radius: 50%;
        }

        .profile-info {
            text-align: left;
            padding-top: 10px;
        }
        .profile-intro {
            text-align: left;
        }
        .info-item {
            margin-bottom: 20px;
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }

        .info-item span {
            color: #1e90ff;
            margin-left: 10px;
        }

        .coin-icon {
            font-size: 18px;
            margin-right: 5px;
        }

        /* 各个区域的通用样式 */
        .section-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(30,144,255,0.2);
            margin-top: 20px;
            min-height: 200px;
            display: none;
        }

        .section-title {
            font-size: 18px;
            color: #1e90ff;
            margin-bottom: 15px;
            font-weight: bold;
            border-bottom: 2px solid #f0f7ff;
            padding-bottom: 10px;
        }

        .empty-content {
            text-align: center;
            color: #999;
            padding: 40px 0;
            font-size: 14px;
        }
        .self-intro {
            margin-top: 30px;
            padding: 20px;
            background: #ffffff;  /* 纯白色背景 */
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(30,144,255,0.2);  /* 添加蓝色阴影 */
        }

        .intro-title {
            font-size: 16px;
            color: #1e90ff;  /* 修改为蓝色 */
            margin-bottom: 10px;
            font-weight: bold;
        }

        .intro-content {
            color: #666;
            line-height: 1.8;
            font-size: 14px;
            padding: 0 10px;
            text-align: justify;  
            width: 100%;        
            word-break: break-all; 
            white-space: normal; 
        }

        /* 添加编辑相关样式 */
        .edit-btn {
            margin-top: 10px;
            padding: 5px 10px;
            background: #1e90ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .edit-btn:hover {
            background: #1873cc;
        }

        .intro-content {
            cursor: pointer;
            padding: 10px;
            border-radius: 4px;
        }

        .intro-content:hover {
            background: #f0f7ff;
        }

        .intro-editing {
            border: 1px solid #1e90ff;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 左侧导航栏 -->
        <div class="sidebar">
            <div class="logo">个人主页</div>
            <ul class="nav-menu">
                <li class="nav-item active">个人资料</li>
                <li class="nav-item">我的收藏</li>
                <li class="nav-item">我的下载</li>
                <li class="nav-item">我的关注</li>
                <li class="nav-item">浏览历史</li>
            </ul>
        </div>

        <!-- 主要内容区域 -->
        <div class="main-content">
            <!-- 顶部横幅 -->
            <div class="banner">
                <img src="img/banner.jpg" alt="banner">
            </div>

            <!-- 个人信息区域 -->
            <div class="profile-container">
                <div class="profile-left">
                    <img src="<?php echo htmlspecialchars($userData['avatar']); ?>" alt="头像" class="avatar">
                    <button onclick="editAvatar()" class="edit-btn">修改头像</button>
                </div>
                <div class="profile-info">
                    <div class="info-item">姓名：<span><?php echo htmlspecialchars($userData['name']); ?></span></div>
                    <div class="info-item">专业：<span><?php echo htmlspecialchars($userData['major']); ?></span></div>
                    <div class="info-item">学号：<span><?php echo htmlspecialchars($userData['student_id']); ?></span></div>
                    <div class="info-item">💰&nbsp;&nbsp;：<span><?php echo htmlspecialchars($userData['coins']); ?></span></div>
                </div>
            </div>
            <div class="profile-container" id="profile-intro" style="margin-top: 20px; display: block;">
                <div class="section-title">个人简介</div>
                <div class="intro-content" id="introText">
                    <?php echo htmlspecialchars($userData['introduction'] ?: '点击添加个人简介'); ?>
                </div>
                <button onclick="showEditForm()" class="edit-btn">编辑简介</button>
                
                <div id="editForm" style="display: none;">
                    <form method="POST" action="me.php">
                        <textarea name="introduction" id="introTextarea" style="width: 100%; min-height: 100px; margin: 10px 0; padding: 8px;"><?php echo htmlspecialchars($userData['introduction']); ?></textarea>
                        <button type="submit" name="update_intro" class="edit-btn">保存</button>
                        <button type="button" onclick="cancelEdit()" class="edit-btn" style="background: #999;">取消</button>
                    </form>
                </div>
            </div>
          
            <!-- 我的收藏 -->
            <div class="section-container" id="collection">
                <div class="section-title">我的收藏</div>
                <?php if ($collectionsResult->num_rows > 0): ?>
                    <?php while ($collection = $collectionsResult->fetch_assoc()): ?>
                        <div class="info-item"><?php echo htmlspecialchars($collection['item_name']); ?></div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-content">暂无收藏内容</div>
                <?php endif; ?>
            </div>
            <!-- 我的下载 -->
            <div class="section-container" id="download">
                <div class="section-title">我的下载</div>
                <?php if ($downloadsResult->num_rows > 0): ?>
                    <?php while ($download = $downloadsResult->fetch_assoc()): ?>
                        <div class="info-item"><?php echo htmlspecialchars($download['file_name']); ?></div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-content">暂无下载内容</div>
                <?php endif; ?>
            </div>

            <!-- 我关注 -->
            <div class="section-container" id="follow">
                <div class="section-title">我的关注</div>
                <?php if ($followsResult->num_rows > 0): ?>
                    <?php while ($follow = $followsResult->fetch_assoc()): ?>
                        <div class="info-item">
                            <a href="user.php?id=<?php echo htmlspecialchars($follow['followed_id']); ?>">
                                <?php echo htmlspecialchars($follow['followed_name']); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-content">暂无关注内容</div>
                <?php endif; ?>
            </div>

            <!-- 浏览历史 -->
            <div class="section-container" id="history">
                <div class="section-title">浏览历史</div>
                <?php if ($historyResult->num_rows > 0): ?>
                    <?php while ($history = $historyResult->fetch_assoc()): ?>
                        <div class="info-item">
                            <a href="resource.php?id=<?php echo htmlspecialchars($history['resource_id']); ?>">
                                <?php echo htmlspecialchars($history['item_name']); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-content">暂无浏览历史</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // 编辑头像功能
        function editAvatar() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.enctype = 'multipart/form-data';
            
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'avatar';
            input.accept = 'image/jpeg,image/png,image/gif';
            
            form.appendChild(input);
            document.body.appendChild(form);
            
            input.onchange = function() {
                if (input.files.length > 0) {
                    form.submit();
                }
            };
            
            input.click();
        }

        // 编辑个人简介功能
        function showEditForm() {
            document.getElementById('introText').style.display = 'none';
            document.getElementById('editForm').style.display = 'block';
        }

        function cancelEdit() {
            document.getElementById('introText').style.display = 'block';
            document.getElementById('editForm').style.display = 'none';
        }

        function saveIntro() {
            const intro = document.getElementById('introTextarea').value;
            
            const formData = new FormData();
            formData.append('introduction', intro);
            formData.append('action', 'update_intro');
            
            fetch('me.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('introText').textContent = intro;
                    cancelEdit();
                } else {
                    alert(data.message || '更新失败，请重试');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('更新失败，请重试');
            });
        }

        // 导航菜单切换功能
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                // 移除所有活动状态
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                // 添加当前项的活动状态
                this.classList.add('active');
                
                // 隐藏所有内容区域、个人资料区域和横幅
                document.querySelectorAll('.section-container').forEach(section => {
                    section.style.display = 'none';
                });
                document.querySelector('.profile-container').style.display = 'none';
                document.getElementById('profile-intro').style.display = 'none';
                document.querySelector('.banner').style.display = 'none';  // 隐藏横幅
                
                // 显示对应的内容区域
                const text = this.textContent.trim();
                if (text === '个人资料') {
                    document.querySelector('.banner').style.display = 'block';  // 显示横幅
                    document.querySelector('.profile-container').style.display = 'grid';
                    document.getElementById('profile-intro').style.display = 'block';
                } else if (text === '我的收藏') {
                    document.getElementById('collection').style.display = 'block';
                } else if (text === '我的下载') {
                    document.getElementById('download').style.display = 'block';
                } else if (text === '我的关注') {
                    document.getElementById('follow').style.display = 'block';
                } else if (text === '浏览历史') {
                    document.getElementById('history').style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>