<?php
// 加载数据库配置
$config = require 'config.php';

// 从配置文件中读取auth数据库配置
$auth_host = $config['auth_database']['auth_host'];
$auth_dbname = $config['auth_database']['auth_dbname'];
$auth_user = $config['auth_database']['auth_user'];
$auth_pass = $config['auth_database']['auth_pass'];
$auth_tableName = $config['auth_database']['auth_tableName'];

try {
    $auth_pdo = new PDO("mysql:host=$auth_host;dbname=$auth_dbname", $auth_user, $auth_pass);
    $auth_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Auth database connection failed: " . $e->getMessage());
    exit;
}

// 从数据库中获取商品信息
$stmt = $auth_pdo->prepare("SELECT * FROM 商品信息");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>