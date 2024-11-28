<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id=? AND password=?");
    $stmt->bind_param("ss", $student_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['student_id'] = $student_id;
        header("Location: resource.php");
    } else {
        $error_message = "登录失败，学号或密码错误。";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>卷卷福</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
  <meta name="description" content="particles.js is a lightweight JavaScript library for creating particles.">
  <meta name="author" content="Vincent Garreau" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" media="screen" href="css/style1.css">
  <link rel="stylesheet" type="text/css" href="css/reset1.css"/>
</head>
<body>

<div id="particles-js">
    <div class="login">
        <div class="login-top">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;卷卷福
            <br>
            让每一次点击 都充满意义
        </div>
        <form method="POST" action="">
            <div class="login-center clearfix">
                <!-- <div class="login-center-img"><img src="img/name.png" /></div> -->
                <div class="login-center-input">
                    <input type="text" name="student_id" placeholder="学号" required />
                    <!-- <div class="login-center-input-text">用户名</div> -->
                </div>
            </div>
            <div class="login-center clearfix">
                <!-- <div class="login-center-img"><img src="img/password.png" /></div> -->
                <div class="login-center-input">
                    <input type="password" name="password" placeholder="密码" required />
                    <!-- <div class="login-center-input-text">密码</div> -->
                </div>
            </div>
            <div class="login-button" onclick="this.closest('form').submit();">
                登录
            </div>
            <div class="login-footer">
                <p><a href="register.php">没有账户?点我注册</a></p>
                <p>忘记密码请联系管理员: <a href="mailto:admin@Flucky.com">admin@Flucky.com</a></p>
            </div>
        </form>
    </div>
    <div class="sk-rotating-plane"></div>
</div>

<script src="js/particles.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>
