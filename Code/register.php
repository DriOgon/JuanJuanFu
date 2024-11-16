<?php
include 'db.php';
$success_message = '';
$error_message = '';

function isValidPassword($password) {
    return preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{6,}$/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    if (!isValidPassword($password)) {
        $error_message = "密码需要6位，且包含字母和数字";
    } else {
        if (isset($student_id)) {
            $sql = "INSERT INTO users (student_id, name, password, email) 
                    VALUES ('$student_id', '$name', '$password', '$email')";
            if ($conn->query($sql) === TRUE) {
                $success_message = "注册成功!";
            } else {
                $error_message = "注册失败，请重试!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>卷卷福-注册</title>
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
        input[type="email"],
        input[type="password"],
        input[type="file"] {
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
        .modal {
            display: none; 
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4); 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 300px;
            text-align: center;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>卷卷福</h1>
</div>

<form method="post" enctype="multipart/form-data">
    <h2>注册账户</h2>
    <label for="student_id">学号:</label>
    <input type="text" name="student_id" required>
    
    <label for="name">姓名:</label>
    <input type="text" name="name" required>
    
    <label for="password">密码:</label>
    <input type="password" name="password" required>
    
    <label for="email">邮箱:</label>
    <input type="email" name="email" required>
    
    <input type="submit" value="注册" name="submit">
</form>

<p>已有账户？<a href="login.php">点击登录</a></p>

<div id="successModal" class="modal" style="<?php echo $success_message ? 'display: block;' : ''; ?>">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('successModal').style.display='none'">&times;</span>
        <p><?php echo $success_message; ?></p>
    </div>
</div>

<div id="errorModal" class="modal" style="<?php echo $error_message ? 'display: block;' : ''; ?>">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('errorModal').style.display='none'">&times;</span>
        <p><?php echo $error_message; ?></p>
    </div>
</div>

<script>
    window.onclick = function(event) {
        var modal = document.getElementById('successModal');
        if (event.target == modal) {
            modal.style.display = "none";
            window.location.href = "login.php";  
        }

        var errorModal = document.getElementById('errorModal');
        if (event.target == errorModal) {
            errorModal.style.display = "none";
        }
    }

    <?php if ($success_message) { ?>
        setTimeout(function() {
            window.location.href = "login.php";
        }, 2000);  
    <?php } elseif ($error_message) { ?>
        setTimeout(function() {
            window.location.href = "register.php"; 
        }, 2000);  
    <?php } ?>
</script>

</body>
</html>
