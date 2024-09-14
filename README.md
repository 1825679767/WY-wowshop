# 魔兽世界商城

这是一个基于 PHP 和 MySQL 的魔兽世界商城项目，用户可以通过该商城购买游戏内的虚拟物品。项目包含用户使用游戏账号登录、商品展示、购买等功能。

## 目录结构

- `index.php`：主页面，展示商品列表和分类。
- `config.php`：配置文件，包含数据库和 SOAP 配置。
- `products.php`：从数据库中获取商品信息。
- `buy.php`：处理购买请求。
- `login.php`：处理用户登录请求。
- `logout.php`：处理用户注销请求。
- `styles.css`：样式文件。
- `scripts.js`：前端脚本文件。
- `auth.sql`：数据库初始化脚本。
- `.htaccess`：服务器配置文件。

## 功能

1. **用户登录**：用户可以使用游戏账号登录商城。
2. **商品展示**：根据分类展示不同的商品。
3. **商品购买**：用户可以选择角色并购买商品，购买后通过 SOAP 接口将物品发送到游戏内角色邮箱。

## 安装与使用

### 1. 数据库配置

首先，确保你已经安装了服务端并且启动了。然后在 A 库运行 `auth.sql` 脚本来初始化数据库表和数据。

### 2. 配置文件

编辑 `config.php` 文件，设置数据库和 SOAP 配置。
```md

```php
<?php
// config.php
// auth 数据库配置
$auth_config = [
    'auth_host' => 'localhost', // 数据库主机
    'auth_dbname' => 'acore_auth', // 数据库名称
    'auth_user' => 'root', // 数据库用户名
    'auth_pass' => 'root', // 数据库密码
    'auth_tableName' => 'account', // 数据库表名
];

// characters 数据库配置
$characters_config = [
    'characters_host' => 'localhost', // 数据库主机
    'characters_dbname' => 'acore_characters', // 数据库名称
    'characters_user' => 'root', // 数据库用户名
    'characters_pass' => 'root', // 数据库密码
    'characters_tableName' => 'characters', // 数据库表名
];

// SOAP 配置
$soap_config = [
    'location' => 'http://localhost:7878/',
    'uri' => 'urn:AC',
    'style' => SOAP_RPC,
    'login' => '游戏GM账号',
    'password' => '游戏GM密码'
];
```

### 3. 启动服务器

确保你的服务器支持 PHP，并将项目文件放置在服务器的根目录下。然后启动服务器。

### 4. 访问商城

在浏览器中访问 `http://localhost/index.php`，你将看到商城的主页面。

### 5. 用户登录

点击登录按钮，输入游戏账号和密码进行登录。登录成功后，你将看到欢迎信息和金币余额。

### 6. 购买商品

选择一个角色，点击商品的购买按钮，确认购买后，物品将通过邮件发送到游戏内角色的邮箱。

### 7. 修改商品信息

要修改商品信息，可以直接在 A 库的“商品信息”表格里面增加修改。

## 注意事项

- 确保你的服务器支持 PHP 和 MySQL。
- 确保配置文件中的数据库和 SOAP 配置正确。
- 确保数据库中有足够的商品数据。
- 要运行这个网站，PHP 环境需要开启以下扩展：
  1. PDO 和 PDO_MySQL：用于数据库连接和操作。
  2. SOAP：用于与游戏服务器进行通信。
  3. JSON：用于处理 JSON 数据。
  4. mbstring：用于多字节字符串处理。
  5. gmp：用于大数运算，特别是在 SRP6 验证中。

## 贡献

欢迎提交问题和贡献代码。如果你有任何建议或改进，请提交 Pull Request。

## 许可证

本项目采用 MIT 许可证。详细信息请参阅 LICENSE 文件。
