<?php
session_start();
include 'db.php';

$upload_dir = 'uploads/';
$message = '';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['major'])) {
    $file_name = $_FILES['file']['name'];
    $major = $_POST['major'];

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
            $stmt = $conn->prepare("INSERT INTO files (filename, filepath, category) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $file_name, $upload_file, $major);
            if ($stmt->execute()) {
                $_SESSION['upload_message'] = "文件上传成功，获得100福币!";
                
                // 通过 session 中的用户 ID 更新福币
                $userId = $_SESSION['user_id'];  // 获取当前用户ID
                $updateCoins = "UPDATE users SET coins = coins + 100 WHERE id = ?";
                $updateStmt = $conn->prepare($updateCoins);
                $updateStmt->bind_param("i", $userId);
                $updateStmt->execute();
            } else {
                $_SESSION['upload_message'] = "文件上传失败，请重试!";
            }
        } else {
            $_SESSION['upload_message'] = "文件上传失败，请重试!";
        }
    } else {
        $_SESSION['upload_message'] = "文件上传失败，请重试！";
    }
    
    // 重定向到同一页面
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 获取并清除消息
if (isset($_SESSION['upload_message'])) {
    $message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']);
}

// 处理搜索查询
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// 处理专业选择
$selected_major = isset($_GET['major']) ? $_GET['major'] : '';

// 构建 WHERE 子句
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

// 分页设置
$items_per_page = 20; // 每页显示20个文件
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// 获取总记录数
$count_sql = "SELECT COUNT(*) as total FROM files $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (count($params) > 0) {
    $count_stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $items_per_page);

// 修改主查询，添加 LIMIT 和 OFFSET
$sql = "SELECT filename, filepath FROM files $where_clause ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param(str_repeat("s", count($params) - 2) . "ii", ...$params);
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
        /* 添加背景图片设置 */
        .sidebar {
            background: url('part1.jpg') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            /* 背景固定 */
        }

        .main-content {
            background: url('part2.gif') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            /* 背景固定 */
        }

       .right-sidebar {
            background: url('part3.gif') right center no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: calc(1000% + 1000px);
            width: 20%;
            background-color: #FFFFFF;
            border-radius: 0;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
            margin-left: 20px;
        }


        body {
            font-family: 'Roboto', sans-serif;
            background-color: #E6F2FF;
            color: #333;
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
            padding: 0;
        }

        .sidebar ul {
            max-height: 650px;
            overflow-y: auto;
        }

        .sidebar,
        .right-sidebar {
            width: 20%;
            background-color: #FFFFFF;
            border-radius: 0;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
            margin: 0;
        }

        .sidebar {
            margin-right: 20px;
        }

        .right-sidebar {
            margin-left: 20px;
        }

        .sidebar h3,
        .right-sidebar h3 {
            color: #007BFF;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .sidebar ul li a,
        .right-sidebar ul li a {
            color: #007BFF;
            text-decoration: none;
        }

        .sidebar ul li a:hover,
        .right-sidebar ul li a:hover {
            text-decoration: underline;
        }

        .sidebar h3 a,
        .right-sidebar h3 a {
            color: #007BFF;
            text-decoration: none;
        }

        .sidebar h3 a:hover,
        .right-sidebar h3 a:hover {
            text-decoration: underline;
        }

        .main-content {
            width: 60%;
            background-color: #FFFFFF;
            border-radius: 0;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
            margin: 0;
        }

        input[type="text"],
        input[type="file"] {
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

        .sidebar ul li,
        .right-sidebar ul li {
            display: list-item;
            padding: 8px 15px;
            background-color: #E6F2FF;
            border-radius: 5px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
            list-style-type: none;
            position: relative;
        }

        .sidebar ul li::before,
        .right-sidebar ul li::before {
            content: '\2022';
            color: #007BFF;
            font-size: 20px;
            position: absolute;
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .sidebar ul li a,
        .right-sidebar ul li a {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }

        .sidebar ul li a:hover,
        .right-sidebar ul li a:hover {
            text-decoration: underline;
        }

        h2 {
            color: #007BFF;
            font-family: 'Roboto', sans-serif;
        }

        .dropdown {
            position: relative;
            margin: 0;
            padding: 0;
        }

        .dropdown-btn {
            cursor: pointer;
            padding: 0;
            background-color: transparent;
            border-radius: 0;
            border: none;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
            text-align: left;
            margin: 20px 0 0 0;
        }

        .dropdown-btn:hover {
            background-color: transparent;
        }

        .dropdown-content {
            margin: 0;
            padding: 0;
        }

        .dropdown-content.show {
            margin-bottom: 2px;
        }

        .dropdown-content li {
            margin: 2px 0;
            padding: 5px 15px;
        }

        .dropdown:last-child .dropdown-content.show {
            margin-bottom: 0;
        }

        .dropdown-btn h3 {
            margin: 0 0 15px 0;
            padding: 0;
            color: #007BFF;
            font-size: 18px;
        }

        .dropdown:last-child .dropdown-btn h3 {
            margin-bottom: 5px;
        }

        .sidebar h3 {
            margin-bottom: 15px;
        }

        .sidebar h3:last-of-type {
            margin-bottom: 5px;
        }

        .dropdown + .dropdown {
            margin-top: 0;
        }

        .dropdown:first-of-type {
            margin-top: 0;
        }

        .sidebar, .main-content, .right-sidebar {
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>

<body>
    <!-- 修改图片块的样式 -->
    <div style="width: 80%; height: 200px; margin: 0 auto 20px auto; overflow: hidden; display: flex; justify-content: center; align-items: center;">
        <img src="../img/banner.jpg" alt="banner" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- 原有的容器 -->
    <div class="container">
        <div class="sidebar">
            <h3><a href="/chat.php">Chat AI&nbsp;&nbsp;&nbsp;&nbsp;<-</a></h3>
            <h3><a href="me.php">个人中心 <-</a></h3>
            <h3><a href="/community.php">卷卷福社区 <-</a></h3>
            
            <div class="dropdown">
                <button class="dropdown-btn" onclick="toggleDropdown('majors')">
                    <h3>涵盖所有专业 ▼</h3>
                </button>
            </div>

            <div class="dropdown">
                <button class="dropdown-btn" onclick="toggleDropdown('resources')">
                    <h3>海量学习资源 ▼</h3>
                </button>
            </div>
        </div>

        <div class="main-content">
            <h2>当前资源中心</h2>
            
            <div class="search-box" style="margin-bottom: 20px;">
                <form action="" method="get" style="display: flex; gap: 10px;">
                    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>" 
                           style="flex: 1; padding: 10px; border: 2px solid #007BFF; border-radius: 5px;" 
                           placeholder="搜索文件名...">
                    <?php if (!empty($selected_major)): ?>
                        <input type="hidden" name="major" value="<?php echo htmlspecialchars($selected_major); ?>">
                    <?php endif; ?>
                    <input type="submit" value="搜索" 
                           style="background-color: #007BFF; color: white; border: none; 
                                  padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                </form>
            </div>

            <ul class="file-list">
                <?php foreach ($files as $file): ?>
                    <li><a href="<?php echo $file['filepath']; ?>" target="_blank"><?php echo $file['filename']; ?></a></li>
                <?php endforeach; ?>
            </ul>

            <!-- 分页导航 -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination" style="margin-top: 20px; text-align: center;">
                <?php 
                // 构建基础URL参数数组
                $url_params = [];
                if (!empty($search_query)) {
                    $url_params['search'] = $search_query;
                }
                if (!empty($selected_major)) {
                    $url_params['major'] = $selected_major;
                }
                
                // 生成基础查询字符串
                $query_string = !empty($url_params) ? '&' . http_build_query($url_params) : '';
                
                // 首页和上一页
                if ($current_page > 1): ?>
                    <a href="?page=1<?php echo $query_string; ?>" style="text-decoration: none; color: #007BFF; margin: 0 5px;">首页</a>
                    <a href="?page=<?php echo ($current_page - 1) . $query_string; ?>" style="text-decoration: none; color: #007BFF; margin: 0 5px;">上一页</a>
                <?php endif;

                // 页码
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);

                for ($i = $start_page; $i <= $end_page; $i++): 
                    $style = "text-decoration: none; margin: 0 5px; padding: 2px 6px;";
                    $style .= ($i == $current_page) ? " background-color: #007BFF; color: #FFFFFF;" : " color: #007BFF;";
                ?>
                    <a href="?page=<?php echo $i . $query_string; ?>" style="<?php echo $style; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;

                // 下一页和末页
                if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo ($current_page + 1) . $query_string; ?>" style="text-decoration: none; color: #007BFF; margin: 0 5px;">下一页</a>
                    <a href="?page=<?php echo $total_pages . $query_string; ?>" style="text-decoration: none; color: #007BFF; margin: 0 5px;">末页</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="right-sidebar">
            <h3 style="display: flex; align-items: center;">
                上传资源领取福币！
                <img src="coin.png" alt="福币" style="width: 100px; height: 100px; margin-left: 5px;">
            </h3>
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
                    <option value="人工智能与机器学习">人工智能与机器学习</option>
                    <option value="数据科学与大数据">数据科学与大数据</option>
                    <option value="网络安全与防护">网络安全与防护</option>
                    <option value="电子与通信工程">电子与通信工程</option>
                    <option value="计算机图形学与视觉">计算机图形学与视觉</option>
                    <option value="机器人技术与自动化">机器人技术与自动化</option>
                    <option value="CET4/6">CET4/6</option>
                    <option value="教材PDF/PPT">教材PDF/PPT</option>
                    <option value="机器人竞赛论文">机器人竞赛论文</option>
                    <option value="SRTP本科生研究资料">SRTP本科生研究资料</option>
                    <option value="人工智能课程资料">人工智能课程资料</option>
                    <option value="Python编程教材">Python编程教材</option>
                    <option value="数据结构与算法">数据结构与算法</option>
                    <option value="嵌入式系统资料">嵌入式系统资料</option>
                    <option value="计算机网络教程">计算机网络教程</option>
                    <option value="操作系统讲义">操作系统讲义</option>
                    <option value="机器学习经典文献">机器学习经典文献</option>
                    <option value="数据库系统教材">数据库系统教材</option>
                    <option value="前端开发学习资料">前端开发学习资料</option>
                    <option value="网络安全研究文献">网络安全研究文献</option>

                </select>
                <br>
                <input type="submit" value="上传">
            </form>
        </div>
    </div>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
            padding-top: 100px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border-radius: 10px;
        }

        .modal .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .modal .close:hover,
        .modal .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
        <script>
        window.onload = function() {
            <?php if (!empty($message)) { ?>
                var modal = document.getElementById("myModal");
                var modalContent = document.getElementById("modalContent");
                var closeBtn = document.getElementById("closeBtn");

                modalContent.textContent = "<?php echo addslashes($message); ?>";
                modalContent.classList.add("error");

                if ("<?php echo addslashes($message); ?>".includes("成功")) {
                    modalContent.classList.remove("error");
                    modalContent.classList.add("success");
                }

                modal.style.display = "block";

                closeBtn.onclick = function() {
                    modal.style.display = "none";
                }

                window.onclick = function(event) {
                    if (event.target === modal) {
                        modal.style.display = "none";
                    }
                }
            <?php } ?>
        };

        // 防止表单重复提交
        document.querySelector('form').onsubmit = function() {
            // 禁用提交按钮
            this.querySelector('input[type="submit"]').disabled = true;
            return true;
        };
        </script>
    <div id="myModal" class="modal">
        <div class="modal-content" id="modalContent">
            <span id="closeBtn" class="close">&times;</span>
        </div>
    </div>

    <script>
    function toggleDropdown(id) {
        event.preventDefault();
        
        const button = event.currentTarget;
        const h3Element = button.querySelector('h3');
        const baseText = h3Element.textContent.replace(/[▼▲]/, '').trim();
        const existingDropdown = document.getElementById(id);
        
        // 如果已存在下拉菜单，则移除它并清除状态
        if (existingDropdown) {
            existingDropdown.remove();
            h3Element.textContent = `${baseText} ▼`;
            localStorage.removeItem('openDropdown');
            return;
        }
        
        // 存储当前打开  菜单ID
        localStorage.setItem('openDropdown', id);
        
        const ul = document.createElement('ul');
        ul.id = id;
        ul.className = 'dropdown-content show';
        
        const menuItems = id === 'majors' ? [
            {href: "?major=all", text: "全部"},
            {href: "?major=经济管理学", text: "经济管理学"},
            {href: "?major=外交政治学", text: "外交政治学"},
            {href: "?major=国际新闻与传播", text: "国际新闻与传播"},
            {href: "?major=马克思主义理论", text: "马克思主义理论"},
            {href: "?major=计算机科学与技术", text: "计算机科学与技术"},
            {href: "?major=电气工程及其自动化", text: "电气工程及其自动化"},
            {href: "?major=化学测量与技术", text: "化学测量学与技术"},
            {href: "?major=自然地理与资源环境", text: "自然地理与资源环境"},
            {href: "?major=人工智能与机器学习", text: "人工智能与机器学习"},
            {href: "?major=数据科学与大数据", text: "数据科学与大数据"},
            {href: "?major=网络安全与防护", text: "网络安全与防护"},
            {href: "?major=电子与通信工程", text: "电子与通信工程"},
            {href: "?major=计算机图形学与视觉", text: "计算机图形学与视觉"},
            {href: "?major=机器人技术与自动化", text: "机器人技术与自动化"}
        ] : [
            {href: "?major=CET4/6", text: "CET4/6"},
            {href: "?major=教材PDF/PPT", text: "教材PDF/PPT"},
            {href: "?major=机器人竞赛论文", text: "机器人竞赛论文"},
            {href: "?major=SRTP本科生研究资料", text: "SRTP本科生研究资料"},
            {href: "?major=人工智能课程资料", text: "人工智能课程资料"},
            {href: "?major=Python编程教材", text: "Python编程教材"},
            {href: "?major=数据结构与算法", text: "数据结构与算法"},
            {href: "?major=嵌入式系统资料", text: "嵌入式系统资料"},
            {href: "?major=计算机网络教程", text: "计算机网络教程"},
            {href: "?major=操作系统讲义", text: "操作系统讲义"},
            {href: "?major=机器学习经典文献", text: "机器学习经典文献"},
            {href: "?major=数据库系统教材", text: "数据库系统教材"},
            {href: "?major=前端开发学习资料", text: "前端开发学习资料"},
            {href: "?major=网络安全研究文献", text: "网络安全研究文献"}
        ];
        
        menuItems.forEach(item => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = item.href;
            a.textContent = item.text;
            
            // 使用 localStorage 保持菜单状态
            a.addEventListener('click', function(e) {
                const currentDropdown = localStorage.getItem('openDropdown');
                if (currentDropdown) {
                    setTimeout(() => {
                        const dropdownToKeep = document.getElementById(currentDropdown);
                        if (dropdownToKeep) {
                            dropdownToKeep.style.display = 'block';
                        }
                    }, 0);
                }
            });
            
            li.appendChild(a);
            ul.appendChild(li);
        });
        
        // 关闭其他打开的菜单
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            if (dropdown.id !== id) {
                const otherButton = dropdown.previousElementSibling;
                const otherH3 = otherButton.querySelector('h3');
                const otherText = otherH3.textContent.replace(/[▼▲]/, '').trim();
                otherH3.textContent = `${otherText} ▼`;
                dropdown.remove();
            }
        });
        
        button.parentNode.insertBefore(ul, button.nextSibling);
        h3Element.textContent = `${baseText} ▲`;
    }

    // 页面加载时恢复菜单状态
    document.addEventListener('DOMContentLoaded', function() {
        const openDropdownId = localStorage.getItem('openDropdown');
        if (openDropdownId) {
            const button = document.querySelector(`[onclick="toggleDropdown('${openDropdownId}')"]`);
            if (button) {
                button.click();
            }
        }
        
        // 处理页面加载后的链接点击
        window.addEventListener('load', function() {
            const currentDropdown = localStorage.getItem('openDropdown');
            if (currentDropdown) {
                const dropdownElement = document.getElementById(currentDropdown);
                if (dropdownElement) {
                    dropdownElement.style.display = 'block';
                }
            }
        });
    });

    // 添加CSS样式确保菜单显示
    const style = document.createElement('style');
    style.textContent = `
        .dropdown-content.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    `;
    document.head.appendChild(style);
    </script>

</body>

</html>