<?php
// 包含数据库连接文件
include 'db.php';  // 假设 db.php 包含数据库连接配置
$upload_dir = 'uploads/';
// 获取用户ID（假设用户ID存储在会话中）
session_start();
$userId = $_SESSION['student_id'] ?? null;  // 从会话中获取用户ID
// 初始化变量
$userBalance = 100.00;   // 默认余额

if ($userId) {
    // 查询用户信息（姓名和余额）
    $query = "SELECT coins FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);  // 使用预处理语句，防止SQL注入
    $stmt->bind_param("i", $userId);  // 绑定参数
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $userBalance = $row['coins'];  // 获取余额
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>卷卷福商城</title>
    <style>
       body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f6f7f8;
        }
        header {
            background-color: #1f292c;
            color: white;
            padding:1px;
        }
        .t1{
            margin-left: 100px; 
            margin-top: 15px;
            font-size: 20px;
        }
        header .t2{
            width:200px;
            margin-top: 10px;
            font-size: large;
        }
        header .nav {
            display: flex;
            margin-top: -22px;
            margin-left: 70px;
            font-size: small;
            gap: 10px;
        }
        header .nav a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        header .nav a:hover {
            text-decoration: underline;
        }
        header .balance-info{
            margin-left: 1500px;
            margin-top: -35px;
        }
        .search-container {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        .search-container input {
            padding: 8px;
            font-size: 16px;
            width: 300px;
            border: none;
            border-radius: 5px;
            margin-right: 10px;
            border-style:solid;
            border-color:#007BFF;
        }
        .search-container button {
            padding: 8px 15px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #0056b3;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
            justify-content: center;
            width: 80%;  
            margin: 0 auto;
        }
        .product {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            padding: 15px;
            width: 280px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .product img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product h3 {
            font-size: 18px;
            margin: 10px 0;
        }
        .product p {
            color: #888;
            margin: 10px 0;
        }
        .product button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .product button:hover {
            background-color: #0056b3;
        }
        footer {
            text-align: center;
            background-color: #333;
            color: white;
            padding: 10px;
            margin-top: 20px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 100px;
        }

        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-between;
        }

        .modal-buttons button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-buttons .cancel {
            background-color: #f44336;
        }

        .modal-buttons .cancel:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <header>
        <div class="t1">
            <span>卷卷福商城</span>
            <ul style="list-style: none;" class="nav">
                <li class="separate">|</li>
                <li><a href="resource.php">资源中心</a></li>
                <li class="separate">|</li>
                <li><a href="me.php">个人中心</a></li>
            </ul>
            <div class="balance-info">
                <p class="t2">您的余额: <span id="balance"><?php echo number_format($userBalance, 2); ?></span> 福币</p>
            </div>
        </div>
    </header>

    <div class="search-container">
        <input type="text" id="searchBox" placeholder="搜索商品...">
        <button onclick="searchProduct()">搜索</button>
    </div>

    <div class="container" id="productContainer">
        <!-- 商品列表 -->
         <!-- 商品列表 -->
        <div class="product" data-id="1">
            <a href="https://smms.app/image/xcY1f3FsBeyDUIg" target="_blank"><img src="https://s2.loli.net/2024/11/28/xcY1f3FsBeyDUIg.jpg" ></a>
            <h3>钢笔</h3>
            <p>1500.00福币</p>
            <p>库存: <span class="stock">50</span> | 已售: <span class="sold">10</span></p>
            <button onclick="confirmPurchase(9, 1)">兑换</button>
        </div>
        <div class="product" data-id="2">
            <a href="https://smms.app/image/qM75hgtEdyJ1Qv3" target="_blank"><img src="https://s2.loli.net/2024/11/28/qM75hgtEdyJ1Qv3.jpg" ></a>
            <h3>便利贴</h3>
            <p>1000.00福币</p>
            <p>库存: <span class="stock">30</span> | 已售: <span class="sold">50</span></p>
            <button onclick="confirmPurchase(5, 2)">兑换</button>
        </div>
        <div class="product" data-id="3">
            <a href="https://smms.app/image/9IVMlGmT8ahiAgS" target="_blank"><img src="https://s2.loli.net/2024/11/28/9IVMlGmT8ahiAgS.jpg" ></a>
            <h3>试卷收纳盒</h3>
            <p>6000.00福币</p>
            <p>库存: <span class="stock">10</span> | 已售: <span class="sold">10</span></p>
            <button onclick="confirmPurchase(60, 3)">兑换</button>
        </div>
        <div class="product" data-id="4">
            <a href="https://smms.app/image/ouBisFOMpEkRN8I" target="_blank"><img src="https://s2.loli.net/2024/11/28/ouBisFOMpEkRN8I.jpg" ></a>
            <h3>笔记本</h3>
            <p>1500.00福币</p>
            <p>库存: <span class="stock">40</span> | 已售: <span class="sold">20</span></p>
            <button onclick="confirmPurchase(15, 4)">兑换</button>
        </div>
        <div class="product" data-id="5">
            <a href="https://smms.app/image/fzvOhTosdVNGeZu" target="_blank"><img src="https://s2.loli.net/2024/11/28/fzvOhTosdVNGeZu.jpg" ></a>
            <h3>笔袋</h3>
            <p>1000.00福币</p>
            <p>库存: <span class="stock">5</span> | 已售: <span class="sold">30</span></p>
            <button onclick="confirmPurchase(10, 5)">兑换</button>
        </div>
        <div class="product" data-id="6">
            <a href="https://smms.app/image/fCoYBRTX83vsSdi" target="_blank"><img src="https://s2.loli.net/2024/11/28/fCoYBRTX83vsSdi.jpg" ></a>
            <h3>读书小夜灯</h3>
            <p>4000.00福币</p>
            <p>库存: <span class="stock">70</span> | 已售: <span class="sold">4</span></p>
            <button onclick="confirmPurchase(85, 6)">兑换</button>
        </div>
        <div class="product" data-id="7">
            <a href="https://smms.app/image/tjXNPmHEichMI98" target="_blank"><img src="https://s2.loli.net/2024/11/28/tjXNPmHEichMI98.jpg" ></a>
            <h3>特色徽章</h3>
            <p>500.00福币</p>
            <p>库存: <span class="stock">80</span> | 已售: <span class="sold">50</span></p>
            <button onclick="confirmPurchase(5, 7)">兑换</button>
        </div>
        <div class="product" data-id="8">
            <a href="https://smms.app/image/zrJBMGgZFI9oWKP" target="_blank"><img src="https://s2.loli.net/2024/11/28/zrJBMGgZFI9oWKP.jpg" ></a>
            <h3>抱枕</h3>
            <p>2000.00福币</p>
            <p>库存: <span class="stock">90</span> | 已售: <span class="sold">1</span></p>
            <button onclick="confirmPurchase(20, 8)">兑换</button>
        </div>
        <div class="product" data-id="9">
            <a href="https://smms.app/image/s7Fw3CtYflcjSK2" target="_blank"><img src="https://s2.loli.net/2024/11/28/s7Fw3CtYflcjSK2.jpg" ></a>
            <h3>鼠标垫</h3>
            <p>1000.00福币</p>
            <p>库存: <span class="stock">100</span> | 已售: <span class="sold">10</span></p>
            <button onclick="confirmPurchase(10, 9)">兑换</button>
        </div>
        <div class="product" data-id="10">
            <a href="https://smms.app/image/zrJBMGgZFI9oWKP" target="_blank"><img src="https://s2.loli.net/2024/11/28/zrJBMGgZFI9oWKP.jpg" ></a>
            <h3>毛毯</h3>
            <p>3000.00福币</p>
            <p>库存: <span class="stock">110</span> | 已售: <span class="sold">2</span></p>
            <button onclick="confirmPurchase(55, 10)">兑换</button>
        </div>
        <div class="product" data-id="11">
            <a href="https://smms.app/image/jwo81uH3txdiRZy" target="_blank"><img src="https://s2.loli.net/2024/11/28/jwo81uH3txdiRZy.jpg" ></a>
            <h3>蓝牙耳机</h3>
            <p>5000.00福币</p>
            <p>库存: <span class="stock">35</span> | 已售: <span class="sold">1</span></p>
            <button onclick="confirmPurchase(200, 11)">兑换</button>
        </div>
        <div class="product" data-id="12">
            <a href="https://smms.app/image/WuiQqkDLwj2csgZ" target="_blank"><img src="https://s2.loli.net/2024/11/28/WuiQqkDLwj2csgZ.jpg" ></a>
            <h3>书包</h3>
            <p>3000.00福币</p>
            <p>库存: <span class="stock">15</span> | 已售: <span class="sold">0</span></p>
            <button onclick="confirmPurchase(150, 12)">兑换</button>
    
    </div>
    </div>

    <footer>
        <p>版权所有 &copy; 2024 卷卷福商城</p>
    </footer>

   <!-- 购买确认弹窗 -->
   <div id="myModal" class="modal">
    <div class="modal-content">
        <h2>确认兑换此商品吗？</h2>
        <div class="modal-buttons">
            <button onclick="purchaseProduct()">确认</button>
            <button class="cancel" onclick="closeModal('myModal')">取消</button>
        </div>
    </div>
</div>

<!-- 购买成功弹窗 -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h2>购买成功！</h2>
        <p>您的余额剩余: <span id="successBalance"></span> 福币</p>
    </div>
</div>

<!-- 余额不足弹窗 -->
<div id="insufficientBalanceModal" class="modal">
    <div class="modal-content">
        <h2>余额不足！</h2>
        <p>您的余额不足，无法购买此商品。</p>
    </div>
</div>

    <script>
        let userBalance = <?php echo $userBalance; ?>;  // 从PHP传递余额
        let currentPrice = 0;
        let currentProductId = null;

        // 更新显示余额
        function updateBalance() {
            document.getElementById('balance').innerText = userBalance.toFixed(2);
        }

        // 显示弹窗
        function openModal(modalId, message) {
            const modal = document.getElementById(modalId);
            if (message) {
                modal.querySelector('.modal-content h2').innerText = message;
            }
            modal.style.display = 'block';
        }

        // 关闭弹窗
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }

        // 确认购买
        function confirmPurchase(price, productId) {
            currentPrice = price; // 设置当前商品价格
            currentProductId = productId; // 设置当前商品ID
            openModal("myModal", "确认兑换此商品吗？"); // 显示确认弹窗
        }

        // 处理商品购买
        function purchaseProduct() {
            if (userBalance >= currentPrice) {
                userBalance -= currentPrice; // 扣除福币
                updateBalance(); // 更新余额显示

                // 更新商品的库存和已售数量
                let product = document.querySelector(`.product[data-id="${currentProductId}"]`);
                let stockElement = product.querySelector('.stock');
                let soldElement = product.querySelector('.sold');
                let stock = parseInt(stockElement.innerText);
                let sold = parseInt(soldElement.innerText);

                // 如果库存大于0，进行购买
                if (stock > 0) {
                    stock--; // 库存减1
                    sold++; // 已售增加1
                    stockElement.innerText = stock;
                    soldElement.innerText = sold;
                }

                // 关闭原先的确认弹窗
                closeModal('myModal');

                // 显示购买成功弹窗
                document.getElementById('successBalance').innerText = userBalance.toFixed(2);
                openModal('successModal', '购买成功！');

                // 两秒后关闭成功弹窗
                setTimeout(() => {
                    closeModal('successModal');
                }, 2000);
            } else {
                // 如果余额不足，提示用户余额不足，并关闭弹窗
                closeModal('myModal');
                openModal('insufficientBalanceModal');

                setTimeout(() => {
                    closeModal('insufficientBalanceModal');
                }, 2000);
            }
        }

        // 搜索商品功能
        function searchProduct() {
            let searchTerm = document.getElementById('searchBox').value.toLowerCase();
            let products = document.querySelectorAll('.product');

            products.forEach(product => {
                let productName = product.querySelector('h3').innerText.toLowerCase();
                if (productName.includes(searchTerm)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        // 初始化余额
        updateBalance();
    </script>
</body>
</html>
