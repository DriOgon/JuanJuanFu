<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>白蓝色主题页面</title>
    <link rel="stylesheet" href="chat_styles.css"> <!-- 引用外部 CSS 文件 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- 引入 jQuery -->
    <script src="chat_script.js"></script> <!-- 引用外部 JavaScript 文件 -->
</head>
<body>

<header>
    <h1>欢迎来到我的页面</h1>
</header>

<nav>
    <a href="#">首页</a>
    <a href="#">关于我们</a>
    <a href="#">服务</a>
    <a href="#">联系</a>
</nav>

<div class="content">
    <h2>内容区域</h2>
    <p>这是一个简单的白蓝色主题页面示例。您可以在这里添加更多内容。</p>
    
    <!-- 文本显示部分 -->
    <div class="text-display" id="textDisplay">
        <h3>文本显示部分</h3>
        <p>这里是一些示例文本，您可以在这里展示任何内容。</p>
    </div>

    <!-- 输入框和按钮部分 -->
    <div class="input-section">
        <input type="text" id="userInput" placeholder="请输入内容..." />
        <button id="submitButton">提交</button>
    </div>
</div>

<footer>
    <p>&copy; 2023 我的公司</p>
</footer>

</body>
</html>