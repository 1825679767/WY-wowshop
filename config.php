<?php
// config.php

// auth数据库配置
$auth_config = [
    'auth_host' => 'localhost',         // 数据库主机
    'auth_dbname' => 'acore_auth',      // 数据库名称
    'auth_user' => 'root',              // 数据库用户名
    'auth_pass' => 'root',              // 数据库密码
    'auth_tableName' => 'account',      // 数据库表名
];

// characters数据库配置
$characters_config = [
    'characters_host' => 'localhost',         // 数据库主机
    'characters_dbname' => 'acore_characters',// 数据库名称
    'characters_user' => 'root',              // 数据库用户名
    'characters_pass' => 'root',              // 数据库密码
    'characters_tableName' => 'characters',   // 数据库表名
];

// SOAP配置
$soap_config = [
    'location' => 'http://localhost:7878/',
    'uri'      => 'urn:AC',
    'style'    => SOAP_RPC,
    'login'    => '1825679767',
    'password' => '1825679767'
];

// 商品分类
$categories = [
    'all' => '全部商品',
    'weapons' => '武器',
    'armor' => '护甲',
    'potions' => '药水',
    'mounts' => '坐骑',
    'pets' => '宠物'
];

return [
    'auth_database' => $auth_config,
    'characters_database' => $characters_config,
    'soap_config' => $soap_config,
    'categories' => $categories
];
