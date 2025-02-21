<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>回复留言</title>
    <!-- 引入TinyMCE富文本编辑器 -->
    <script src="D:\phpstudy_pro\WWW\tinymce\js\tinymce\tinymce.min.js" referrerpolicy="origin"></script>
    <script>
  tinymce.init({
    selector: '#message',
    language: 'zh_CN'
  });
</script>


</head>
<body>
    <h1>回复留言</h1>
    
    <form action="submit.php" method="POST">
        <label for="nickname">昵称:</label>
        <input type="text" name="nickname" id="nickname" required><br><br>

        <label for="message">回复内容:</label><br>
        <textarea id="message" name="message"></textarea><br><br>

        <input type="hidden" name="reply_to" value="<?php echo $_GET['id']; ?>">
        <input type="submit" value="提交回复">
    </form>
</body>
</html>
