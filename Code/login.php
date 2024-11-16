<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE student_id='$student_id' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['student_id'] = $student_id;
        header("Location: resource.php");
    } else {
        $error_message = "登录失败，学号或密码错误。";
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #E6F2FF;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 2.5em;
            color: #007BFF;
            margin: 0;
        }
        form {
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #007BFF;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: #FFFFFF;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%; 
            margin-top: 10px; 
                }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        p {
            text-align: center;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>卷卷福</h1>
</div>

<form method="post">
    <h2>登录账户</h2>
    <label for="student_id">学号:</label>
    <input type="text" name="student_id" required>
    
    <label for="password">密码:</label>
    <input type="password" name="password" required>
    
    <input type="submit" value="登录">
    
    <p class="error"><?php echo isset($error_message) ? $error_message : ''; ?></p>
    <p><a href="register.php">没有账户?点我注册</a></p>
</form>

<p>忘记密码请联系管理员: <a href="mailto:admin@Flucky.com">admin@Flucky.com</a></p>

</body>
</html>
