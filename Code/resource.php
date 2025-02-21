<?php
// 添加允许的文件类型
$allowed_types = [
    'image/jpeg',     // .jpg, .jpeg
    'image/png',      // .png
    'application/pdf', // .pdf
    'application/zip', // .zip
    'application/x-zip', // 另一种 zip mime 类型
    'application/x-zip-compressed', // 另一种 zip mime 类型
    'application/msword', // .doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/vnd.ms-powerpoint', // .ppt
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
    'text/plain'      // .txt
];

session_start();
include 'db.php';

$upload_dir = 'uploads/';
$message = '';
$student_id = $_SESSION['student_id'];
// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['major'])) {
    // 设置JSON响应头
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
    // 检查文件类型
    if (!in_array($_FILES['file']['type'], $allowed_types)) {
        $response['message'] = "不支持的文件类型：" . $_FILES['file']['type'] . "。允许的类型：JPG, PNG, PDF, ZIP, DOC, DOCX, PPT, PPTX, TXT";
        echo json_encode($response);
        exit;
    }
    
    $file_name = $_FILES['file']['name'];
    $major = $_POST['major'];
    $student_id = $_SESSION['student_id'];
    
    // 简单的文件处理
    $upload_file = $upload_dir . time() . '_' . $file_name;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
        // 将文件信息插入到pending表中，待审核
        $stmt = $conn->prepare("INSERT INTO pending (filename, filepath, category, student_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $file_name, $upload_file, $major, $student_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "文件上传成功，审核后发放福币。";
        } else {
            $response['message'] = "数据库更新失败";
        }
    } else {
        $response['message'] = "文件上传失败";
    }
    
    echo json_encode($response);
    exit;
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
        <img src="img/banner.jpg" alt="banner" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- 原有的容器 -->
    <div class="container">
        <div class="sidebar">
            <h3><a href="/chat.php">Chat AI&nbsp;&nbsp;&nbsp;&nbsp;<-</a></h3>
            <h3><a href="me.php">个人中心 <-</a></h3>
            <h3><a href="/community.php">卷卷福社区 <-</a></h3>
            <h3><a href="/shopping.php">卷卷福商城 <-</a></h3>
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
                <img src="img/coin.png" alt="福币" style="width: 100px; height: 100px; margin-left: 5px;">
            </h3>
            <form id="uploadForm" enctype="multipart/form-data">
                <label for="file" style="color:#007BFF;">选择文件:</label>
                <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx,.ppt,.pptx,.txt" required>
                <div style="font-size: 12px; color: #666; margin: 5px 0;">
                    支持的文件类型：JPG, PNG, PDF, ZIP, DOC, DOCX, PPT, PPTX, TXT<br>
                    最大文件大小：50MB
                </div>
                <br>
                <label for="major">选择类别:</label>
                <select name="major" id="major" required>
                    <option value="高等数学">高等数学</option>
                    <option value="线性代数">线性代数</option>
                    <option value="大学英语">大学英语</option>
                    <option value="大学物理">大学物理</option>
                    <option value="概率论与数理统计">概率论与数理统计</option>
                    <option value="计算机基础408">计算机基础408</option>
                    <option value="计算机专业">计算机专业</option>
                    <option value="材料专业">材料专业</option>
                    <option value="电子专业">电子专业</option>
                    <option value="土木专业">土木专业</option>
                    <option value="建筑专业">建筑专业</option>
                    <option value="化学专业">化学专业</option>
                    <option value="经管专业">经管专业</option>
                    <option value="外国语专业">外国语专业</option>
                    <option value="教育专业">教育专业</option>
                    <option value="医学专业">医学专业</option>
                    <option value="近代史纲">近代史纲</option>
                    <option value="思修">思修</option>
                    <option value="毛概">毛概</option>
                    <option value="马原">马原</option>
                    <option value="其他">其他</option>


                    <option value="CET4/6">CET4/6</option>
                    <option value="教材PDF/PPT">教材PDF/PPT</option>
                    <option value="机器人竞赛论文">机器人竞赛论文</option>
                    <option value="SRTP本科生研究资料">SRTP   科生研究资料</option>
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
                <button type="submit" class="edit-btn">上传</button>
            </form>
            <div id="uploadStatus" style="margin-top: 10px; display: none;"></div>
            <!-- 修改公告栏部分 -->
            <div class="announcement-board" style="margin-top: 20px; border: 1px solid #007BFF; border-radius: 5px;">
                <div class="announcement-item" style="background-color: #87CEEB; padding: 10px; border-bottom: 1px solid #007BFF; text-align: center;">
                    公告栏
                </div>
                <div class="announcement-item" style="padding: 10px; border-bottom: 1px solid #007BFF; cursor: pointer; text-align: center;" onclick="showModal('上传资源须知')">
                    上传资源须知
                </div>
                <div class="announcement-item" style="padding: 10px; border-bottom: 1px solid #007BFF; cursor: pointer; text-align: center;" onclick="showModal('关于我们')">
                    关于我们
                </div>
                <div class="announcement-item" style="padding: 10px; cursor: pointer; text-align: center;" onclick="showModal('使用说明')">
                    使用说明
                </div>
            </div>
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
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle"></h3>
            <div id="modalText"></div>
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
            {href: "?major=高等数学", text: "高等数学"},
            {href: "?major=线性代数", text: "线性代数"},
            {href: "?major=大学英语", text: "大学英语"},
            {href: "?major=大学物理", text: "大学物理"},
            {href: "?major=概率论与数理统计", text: "概率论与数理统计"},
            {href: "?major=计算机基础408", text: "计算机基础408"},
            {href: "?major=计算机专业", text: "计算机专业"},
            {href: "?major=材料专业", text: "材料专业"},
            {href: "?major=电子专业", text: "电子专业"},
            {href: "?major=土木专业", text: "土木专业"},
            {href: "?major=建筑专业", text: "建筑专业"},
            {href: "?major=化学专业", text: "化学专业"},
            {href: "?major=经管专业", text: "经管专业"},
            {href: "?major=外国语专业", text: "外国语专业"},
            {href: "?major=教育专业", text: "教育专业"},
            {href: "?major=医学专业", text: "医学专业"},
            {href: "?major=近代史纲", text: "近代史纲"},
            {href: "?major=思修", text: "思修"},
            {href: "?major=毛概", text: "毛概"},
            {href: "?major=马原", text: "马原"},
            {href: "?major=其他", text: "其他"}
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
    document.head.appendChild(style);

    function showModal(type) {
        const modal = document.getElementById('announcementModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalText = document.getElementById('modalText');
        
        modalTitle.textContent = type;
        
        // 根据不同类型显示不同内容
        switch(type) {
            case '公告栏':
                modalText.innerHTML = `
                    <p>1. 欢迎使用资源中心！</p>
                    <p>2. 上传资源可获得100福币奖励</p>
                    <p>3. 资源下载完全免费</p>
                `;
                break;
            case '上传资源须知':
                modalText.innerHTML = `
                    <p>1. 请确保上传的资源内容合法</p>
                    <p>2. 支持的文件格式：PDF、Word、PPT、图片等</p>
                    <p>3. 单个文件大小不超过50MB</p>
                    <p>4. 请选择正确的资源分类</p>
                `;
                break;
            case '关于我们':
                modalText.innerHTML = `
                    <p>我们是一个致力于知识共享的学习平台</p>
                    <p>希望通过资源共享促进学习交流</p>
                    <p>欢迎加入我们的学习社区</p>
                `;
                break;
            case '使用说明':
                modalText.innerHTML = `
                    <p>1. 点击左侧菜单选择专业类</p>
                    <p>2. 使用搜索框快速查找资源</p>
                    <p>3. 点击文件名即可下载资源</p>
                    <p>4. 上传资源后自动获得福币奖励</p>
                `;
                break;
        }
        
        modal.style.display = 'block';
    }

    function closeModal() {
        document.getElementById('announcementModal').style.display = 'none';
    }

    // 点击模态框外部关闭
    window.onclick = function(event) {
        const modal = document.getElementById('announcementModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const statusDiv = document.getElementById('uploadStatus');
        const submitBtn = this.querySelector('button[type="submit"]');
        
        // 显示上传状态
        statusDiv.style.display = 'block';
        statusDiv.innerHTML = '文件上传中...';
        submitBtn.disabled = true;
        
        fetch('resource.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            statusDiv.innerHTML = data.message;
            if (data.success) {
                statusDiv.style.color = 'green';
                // 清空表单
                document.getElementById('uploadForm').reset();
                // 延迟刷新页面以显示新上传的文件
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                statusDiv.style.color = 'red';
            }
        })
        .catch(error => {
            statusDiv.style.color = 'red';
            statusDiv.innerHTML = '上传失败，请重试';
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.disabled = false;
        });
    });
    </script>

</body>

</html>