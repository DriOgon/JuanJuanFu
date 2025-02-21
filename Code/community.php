<?php
session_start(); // 启动会话

// 检查用户是否登录
if (!isset($_SESSION['student_id'])) {
    echo "请先登录。";
    exit();
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $reward = intval($_POST['reward']);

    // 获取用户信息
    $student_id = $_SESSION['student_id'];
    $name = $_SESSION['name'];
    $major = $_SESSION['major'];

    // 连接数据库
    $conn = new mysqli('localhost', 'juanjuanfu', 'Skzj69707172@admin', '11-13');
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }

    // 插入数据到 messages 表
    $stmt = $conn->prepare("INSERT INTO messages (student_id, name, major, content, title, reward) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("预处理语句失败: " . $conn->error);
    }

    $stmt->bind_param("sssssi", $student_id, $name, $major, $content, $title, $reward);

    if ($stmt->execute()) {
        echo "<script>alert('咨询发布成功！');window.location.href='index.php';</script>";
    } else {
        echo "错误: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>社区</title>
<meta name="keywords" content="个人博客,杨青个人博客,个人博客模板,杨青" />
<meta name="description" content="杨青个人博客，是一个站在web前端设计之路的女程序员个人网站，提供个人博客模板免费资源下载的个人原创网站。" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="css/base.css" rel="stylesheet">
<link href="css/index.css" rel="stylesheet">
<link href="css/m.css" rel="stylesheet">

<!--[if lt IE 9]>
<script src="js/modernizr.js"></script>
<![endif]-->
<script>
window.onload = function ()
{
	var oH2 = document.getElementsByTagName("h2")[0];
	var oUl = document.getElementsByTagName("ul")[0];
	oH2.onclick = function ()
	{
		var style = oUl.style;
		style.display = style.display == "block" ? "none" : "block";
		oH2.className = style.display == "block" ? "open" : ""
	}
}
</script>
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
	  
	  
	  <h2 style="font-size: 48px; margin-top: 40px;">所有问题</h2>
		  <ul id="questions">
			  <script>
				  window.onload = function () {
					  // 发送 AJAX 请求到 get_questions.php
					  fetch('get_messages.php')
						  .then(response => response.json()) // 解析响应为 JSON
						  .then(data => {
							  // 获取问题列表容器（确保 id="questions"）
							  var questionsContainer = document.getElementById('questions');
				  
							  // 清空容器中的现有内容
							  questionsContainer.innerHTML = '';
				  
							  // 检查是否有数据返回
							  if (data.length > 0) {
                                  // 遍历返回的数据数组
                                  data.forEach(function(question) {
                                      // 检查 reply_to 是否为 null
                                      if (question.reply_to === null) { // 只显示 reply_to 为 null 的留言
                                          // 创建 <li> 元素
                                          var li = document.createElement('li');

                                          // 创建博客标题部分
                                          var blogTitle = document.createElement('h3');
                                          blogTitle.className = 'blogtitle';
                                          var aTitle = document.createElement('a');
                                          aTitle.href = '/';
                                          aTitle.textContent = question.title; // 显示问题标题
                                          blogTitle.appendChild(aTitle);
                                          li.appendChild(blogTitle);

                                          // 创建博客信息部分
                                          var blogInfo = document.createElement('div');
                                          blogInfo.className = 'bloginfo';
                                          var p = document.createElement('p');
                                          p.textContent = question.content; // 显示问题内容
                                          blogInfo.appendChild(p);
                                          li.appendChild(blogInfo);

                                          // 创建作者信息部分
                                          var autor = document.createElement('div');
                                          autor.className = 'autor';
                                          var lm = document.createElement('span');
                                          lm.className = 'lm';
                                          var aLm = document.createElement('a');
                                          aLm.href = '/';
                                          aLm.textContent = question.student_id; // 显示用户 ID
                                          lm.appendChild(aLm);
                                          autor.appendChild(lm);

                                          var dtime = document.createElement('span');
                                          dtime.className = 'dtime';
                                          dtime.textContent = question.create_time; // 显示创建时间
                                          autor.appendChild(dtime);

                                          var viewnum = document.createElement('span');
                                          viewnum.className = 'viewnum';
                                          viewnum.innerHTML = '悬赏福币数（<a href="/">' + question.reward + '</a>）'; // 显示悬赏福币数
                                          autor.appendChild(viewnum);

                                          var readmore = document.createElement('span');
                                          readmore.className = 'readmore';
                                          var aReadmore = document.createElement('a');
                                          aReadmore.href = 'view_message.php?id=' + question.id; // 传递问题 ID
                                          aReadmore.textContent = '查看问题';
                                          readmore.appendChild(aReadmore);
                                          autor.appendChild(readmore);

                                          li.appendChild(autor);

                                          // 将创建好的 <li> 添加到问题列表容器中
                          questionsContainer.appendChild(li);
                                      }
                                  });
							  } else {
								  // 如果没有数据，显示暂无问题
								  questionsContainer.innerHTML = '<p style="font-size: 24px;">暂无问题。</p>';
							  }
						  })
						  .catch(error => {
							  console.error('Error:', error);
						  });
				  };
				  </script>
		  </ul>
	</div>
  
  
  
  <div class="sidebar">
      <div class="form-container">
          <h2 style="font-size: 72px; margin-bottom: 30px;">发布咨询</h2>
          <form action="submit_message.php" method="post">
              <label for="title" style="font-size: 24px;">问题标题:</label><br>
              <input type="text" id="title" name="title" placeholder="请输入问题标题" required style="font-size: 24px; padding: 12px;"><br><br>
  
              <label for="content" style="font-size: 24px;">问题内容:</label><br>
              <textarea id="content" name="content" placeholder="请输入问题内容" required style="font-size: 24px; padding: 12px;"></textarea><br><br>
  
              <label for="reward" style="font-size: 24px;">悬赏福币数:</label><br>
              <input type="number" id="reward" name="reward" min="1" placeholder="请输入正整数" required style="font-size: 24px; padding: 12px;"><br><br>
  
              <input type="submit" value="提交问题" style="font-size: 24px; padding: 10px 20px;">
          </form>
      </div>
  </div>
  
  
</article>



<div class="blank"></div>

<script src="js/nav.js"></script>
</body>
</html>
