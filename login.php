<?php
$session_path = __DIR__ . '/tmp';
if (!file_exists($session_path)) {
    mkdir($session_path, 0777, true);
}
session_save_path($session_path);
session_start(); // 开启会话

// 加载数据库配置
$config = require 'config.php';

// 从配置文件中读取auth数据库配置
$auth_host = $config['auth_database']['auth_host'];
$auth_dbname = $config['auth_database']['auth_dbname'];
$auth_user = $config['auth_database']['auth_user'];
$auth_pass = $config['auth_database']['auth_pass'];
$auth_tableName = $config['auth_database']['auth_tableName'];

// 从配置文件中读取characters数据库配置
$characters_host = $config['characters_database']['characters_host'];
$characters_dbname = $config['characters_database']['characters_dbname'];
$characters_user = $config['characters_database']['characters_user'];
$characters_pass = $config['characters_database']['characters_pass'];
$characters_tableName = $config['characters_database']['characters_tableName'];

try {
    $auth_pdo = new PDO("mysql:host=$auth_host;dbname=$auth_dbname", $auth_user, $auth_pass);
    $auth_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Auth database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Auth database connection error']);
    exit;
}

try {
    $characters_pdo = new PDO("mysql:host=$characters_host;dbname=$characters_dbname", $characters_user, $characters_pass);
    $characters_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Characters database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Characters database connection error']);
    exit;
}

// 获取JSON输入数据
header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

// 从auth数据库中获取salt, verifier 和 JinBi (金币)
$stmt = $auth_pdo->prepare("SELECT id, salt, verifier, JinBi FROM $auth_tableName WHERE username = :username");
$stmt->execute(['username' => $username]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// 在文件开头添加：
error_log("Login attempt for username: " . $username);

if ($userData) {
    // 从数据库中获取salt和verifier
    $id = $userData['id'];
    $salt = $userData['salt'];
    $verifier = $userData['verifier'];
    $JinBi = $userData['JinBi'];  // 数据库中的金币字段是JinBi

    // 调用VerifySRP6Login函数进行验证
    if (VerifySRP6Login($username, $password, $salt, $verifier)) {
        // 登录成功，设置会话变量
        $_SESSION['username'] = $username;
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $id;  // 添加这行，保存用户ID

        // 从characters数据库中获取角色名字
        $stmt = $characters_pdo->prepare("SELECT name FROM $characters_tableName WHERE account = :id");
        $stmt->execute(['id' => $id]);
        $characters = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 返回成功信息、金币数量和角色名字
        echo json_encode(['success' => true, 'gold' => $JinBi, 'characters' => $characters]);
        
        // 在处理结果的地方添加：
        error_log("Login successful for username: " . $username);
    } else {
        // 登录失败，返回错误信息
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        
        // 在处理结果的地方添加：
        error_log("Login failed for username: " . $username . " - Invalid password");
    }
} else {
    // 用户不存在，返回错误信息
    echo json_encode(['success' => false, 'message' => 'User not found']);
    
    // 在处理结果的地方添加：
    error_log("Login failed for username: " . $username . " - User not found");
}

// 验证函数
function VerifySRP6Login($username, $password, $salt, $verifier) {
    // 使用用户名和密码重新计算verifier
    $checkVerifier = CalculateSRP6Verifier($username, $password, $salt);
    // 比较重新计算的verifier与数据库中的verifier
    return ($verifier === $checkVerifier);
}

// 计算SRP6 Verifier函数
function CalculateSRP6Verifier($username, $password, $salt) {
    // 算法常量
    $g = gmp_init(7);
    $N = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);
    
    // 第一次哈希计算
    $h1 = sha1(strtoupper($username . ':' . $password), TRUE);
    
    // 第二次哈希计算
    $h2 = sha1($salt.$h1, TRUE);
    
    // 转换为整数 (little-endian)
    $h2 = gmp_import($h2, 1, GMP_LSW_FIRST);
    
    // 计算 g^h2 mod N
    $verifier = gmp_powm($g, $h2, $N);
    
    // 转换回字节数组 (little-endian)
    $verifier = gmp_export($verifier, 1, GMP_LSW_FIRST);
    
    // 填充到32字节（注意little-endian）
    $verifier = str_pad($verifier, 32, chr(0), STR_PAD_RIGHT);
    
    // 返回verifier
    return $verifier;
}
?>
