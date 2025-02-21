<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $major = mysqli_real_escape_string($conn, $_POST['major']);
    if (empty($student_id) || empty($name) || empty($password) || empty($email) || empty($major)) {
        echo "<script>alert('所有字段均为必填项！');</script>";
    } 
    elseif (!preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || strlen($password) < 6) {
        echo "<script>alert('密码必须包含大小写字母且长度至少为六位！');</script>";
    } 
    else {
        if (isset($student_id)) {
            $stmt = $conn->prepare("INSERT INTO users (student_id, name, password, email, major) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $student_id, $name, $password, $email, $major);
            
            if ($stmt->execute()) {
                echo "<script>alert('注册成功!'); window.location.href = 'login.php';</script>";
            } else {
                echo "<script>alert('注册失败，请重试!');</script>";
            }
            $stmt->close();
        }
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
    <link rel="stylesheet" media="screen" href="css/style2.css">
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
                <!-- 学号 -->
                <div class="login-center-input">
                    <input type="text" name="student_id" placeholder="学号" required />
                </div>
            </div>

            <div class="login-center clearfix">
                <!-- 密码 -->
                <div class="login-center-input">
                    <input type="password" name="password" placeholder="密码" required />
                </div>
            </div>

            <div class="login-center clearfix">
                <!-- 姓名 -->
                <div class="login-center-input">
                    <input type="text" name="name" placeholder="姓名" required />
                </div>
            </div>

            <div class="login-center clearfix">
                <!-- 专业 -->
                <div class="login-center-input">
                    <input type="text" name="major" placeholder="专业" required />
                </div>
            </div>

            <div class="login-center clearfix">
                <!-- 邮箱 -->
                <div class="login-center-input">
                    <input type="email" name="email" placeholder="邮箱" required />
                </div>
            </div>

            <div class="login-button" onclick="this.closest('form').submit();">
                注册
            </div>
            <div class="login-footer">
                <p><a href="login.php">已有账户?点我登录</a></p>
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
