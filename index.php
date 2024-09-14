<?php
// 在文件开头添加以下代码
session_start();
$loggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $loggedIn ? $_SESSION['username'] : '';

// 设置新的会话保存路径
$session_path = __DIR__ . '/tmp';
if (!file_exists($session_path)) {
    mkdir($session_path, 0777, true);
}
session_save_path($session_path);

// 启动会话
session_start();

$version = "1.0"; // 每次更新文件时增加这个版本号

// 加载配置
$config = require 'config.php';
$categories = $config['categories'];

// 获取当前选中的分类
$currentCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

// 包含 products.php 文件
include 'products.php';

// 根据当前分类筛选商品
if ($currentCategory != 'all') {
    $filteredProducts = array_filter($products, function($product) use ($currentCategory) {
        return $product['分类'] == $currentCategory;
    });
} else {
    $filteredProducts = $products;
}

?>

<!DOCTYPE html>
<html>
<head>
    <base href="/" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>魔兽世界商城</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo $version; ?>">
    <style>
    <?php echo file_get_contents('styles.css'); ?>
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <img src="/static/logo.png" alt="魔兽世界标志" class="logo" width="120" height="120">
            <h1>魔兽世界商城</h1>
            <span class="welcome-message" id="welcomeMessage">欢迎你</span>
            <button class="login-button" id="loginButton">登录</button>
            <button class="logout-button" id="logoutButton" style="display: none;">注销账号</button>
        </div>
    </header>

    <div class="login-modal" id="loginModal">
        <h2>游戏账号登录</h2>
        <input type="text" id="username" placeholder="账号">
        <input type="password" id="password" placeholder="密码">
        <button id="loginSubmit">登录</button>
    </div>

    <div class="buy-modal" id="buyModal">
        <h2>确认购买</h2>
        <p id="buyMessage"></p>
        <button id="confirmBuy">确认</button>
        <button id="cancelBuy">取消</button>
    </div>

    <div class="result-modal" id="resultModal">
        <h2 id="resultTitle"></h2>
        <p id="resultMessage"></p>
        <button id="closeResult">关闭</button>
    </div>

    <div class="character-select-modal" id="characterSelectModal">
        <h2>请选择一个角色</h2>
        <p id="characterSelectMessage">请先选择一个角色才能进行购买。</p>
        <button id="closeCharacterSelect">关闭</button>
    </div>

    <div class="container main-container">
        <aside class="sidebar">
            <div class="sidebar-content">
                <h2>商品分类</h2>
                <nav>
                    <ul id="categoryList">
                        <?php
                        foreach ($categories as $key => $value) {
                            $activeClass = ($key == $currentCategory) ? 'active' : '';
                            echo "<li><a href='?category=$key' class='$activeClass'>$value</a></li>";
                        }
                        ?>
                    </ul>
                </nav>
            </div>
            <div class="balance" id="balance">你的金币<br><span id="goldAmount">xxx</span></div>
        </aside>

        <main class="main-content">
            <div class="search-bar">
                <select id="characterSelect">
                    <option value="" disabled selected>请选择你的角色</option>
                </select>
                <div class="search-container">
                    <input type="text" placeholder="搜索商品...">
                    <button>搜索</button>
                </div>
            </div>
            
            <div class="products" id="products">
                <?php
                if (empty($filteredProducts)) {
                    echo "<p>暂无商品数据。</p>";
                } else {
                    foreach ($filteredProducts as $product) {
                        echo "
                        <div class='product' data-category='{$product['分类']}' data-itemid='{$product['商品ID']}' data-quantity='{$product['数量']}'>
                            <div class='product-image'>
                                <img src='{$product['图片']}' alt='{$product['名称']}' width='200' height='150'>
                            </div>
                            <div class='product-info'>
                                <h3 class='product-name'>{$product['名称']}</h3>
                                <p class='product-description'>{$product['描述']}</p>
                                <div class='product-details'>
                                    <p class='quantity'>数量: {$product['数量']}</p>
                                    <p class='price'>价格: {$product['价格']}金币</p>
                                    <button class='buy-button'>购买</button>
                                </div>
                            </div>
                        </div>
                        ";
                    }
                }
                ?>
            </div>
        </main>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 魔兽世界商城. 保留所有权利.</p>
        </div>
    </footer>

    <script src="scripts.js?v=<?php echo $version; ?>"></script>
    <script>
        // 在body结束标签前添加以下脚本
        var loggedIn = <?php echo json_encode($loggedIn); ?>;
        var username = <?php echo json_encode($username); ?>;
        if (loggedIn) {
            document.getElementById('welcomeMessage').textContent = '欢迎你 ' + username;
            document.getElementById('welcomeMessage').style.display = 'inline';
            document.getElementById('loginButton').style.display = 'none';
            document.getElementById('logoutButton').style.display = 'inline';
        }
    </script>
</body>
</html>