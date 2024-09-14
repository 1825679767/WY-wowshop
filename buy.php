<?php
$session_path = __DIR__ . '/tmp';
if (!file_exists($session_path)) {
    mkdir($session_path, 0777, true);
}
session_save_path($session_path);
session_start(); // 开启会话

header('Content-Type: application/json; charset=utf-8');

// 加载数据库配置
$config = require 'config.php';

// 从配置文件中读取auth数据库配置
$auth_host = $config['auth_database']['auth_host'];
$auth_dbname = $config['auth_database']['auth_dbname'];
$auth_user = $config['auth_database']['auth_user'];
$auth_pass = $config['auth_database']['auth_pass'];
$auth_tableName = $config['auth_database']['auth_tableName'];

// 验证表名合法性
if (!preg_match('/^[a-zA-Z0-9_]+$/', $auth_tableName)) {
    echo json_encode(['success' => false, 'message' => '无效的表名']);
    exit;
}

// 从配置文件中读取SOAP配置
$soap_config = $config['soap_config'];

try {
    $auth_pdo = new PDO("mysql:host=$auth_host;dbname=$auth_dbname", $auth_user, $auth_pass);
    $auth_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Auth database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Auth database connection error']);
    exit;
}

// 获取JSON输入数据并验证
$data = json_decode(file_get_contents('php://input'), true);

if (is_null($data)) {
    echo json_encode(['success' => false, 'message' => '无效的JSON输入']);
    exit;
}

$itemID = filter_var($data['itemID'], FILTER_SANITIZE_NUMBER_INT);
$productPrice = filter_var($data['productPrice'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$characterName = filter_var($data['characterName'], FILTER_SANITIZE_STRING);
$quantity = filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT);
$itemName = filter_var($data['itemName'], FILTER_SANITIZE_STRING); // 添加这行

// 检查用户是否登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => '用户未登录']);
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// 从auth数据库中获取用户金币
$stmt = $auth_pdo->prepare("SELECT JinBi FROM $auth_tableName WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    $currentGold = $userData['JinBi'];

    if ($currentGold >= $productPrice) {
        $auth_pdo->beginTransaction();
        try {
            // 扣除金币
            $newGold = $currentGold - $productPrice;
            $stmt = $auth_pdo->prepare("UPDATE $auth_tableName SET JinBi = :newGold WHERE id = :user_id");
            $stmt->execute(['newGold' => $newGold, 'user_id' => $user_id]);

            // 使用SOAP发货
            $conn = new SoapClient(NULL, $soap_config);
            $mailsSent = 0;
            $remainingQuantity = $quantity;
            $soapResponses = [];

            while ($remainingQuantity > 0) {
                // 每次最多发送12个物品
                $sendQuantity = min($remainingQuantity, 12);
                
                // 构建正确的SOAP命令
                $command = sprintf(
                    '.send items %s "%s" "%s" %d:%d',
                    $characterName,  // 玩家名称
                    '商城',          // 邮件主题
                    '感谢购买',      // 邮件正文
                    $itemID,         // 物品ID
                    $sendQuantity    // 发送的物品数量
                );

                // 执行SOAP请求
                try {
                    $result = $conn->executeCommand(new SoapParam($command, 'command'));
                    $soapResponses[] = $result;

                    // 检查SOAP响应是否包含"Mail sent"
                    if (strpos($result, 'Mail sent') !== false) {
                        $mailsSent++;
                        $remainingQuantity -= $sendQuantity;
                    } else {
                        throw new Exception("SOAP response does not contain 'Mail sent'");
                    }
                } catch (Exception $e) {
                    // 如果发货失败，回滚金币扣除并退出
                    $stmt = $auth_pdo->prepare("UPDATE $auth_tableName SET JinBi = :oldGold WHERE id = :user_id");
                    $stmt->execute(['oldGold' => $currentGold, 'user_id' => $user_id]);
                    echo json_encode([
                        'success' => false, 
                        'message' => '购买失败，物品发送出错: ' . $e->getMessage(),
                        'soapResponses' => $soapResponses,
                        'debug' => [
                            'command' => $command,
                            'result' => $result ?? null,
                            'error' => $e->getMessage()
                        ]
                    ]);
                    exit;
                }
            }

            // 记录购买日志
            $stmt = $auth_pdo->prepare("INSERT INTO web购买记录 (账号, 角色名字, 商品ID, 商品名字, 花费金币, 购买时间) VALUES (:username, :character_name, :item_id, :item_name, :gold_spent, NOW())");
            $stmt->execute([
                'username' => $username,
                'character_name' => $characterName,
                'item_id' => $itemID,
                'item_name' => $itemName, // 修改这行
                'gold_spent' => $productPrice
            ]);

            // 发送成功
            $auth_pdo->commit();
            echo json_encode([
                'success' => true, 
                'message' => "购买成功，物品已通过 {$mailsSent} 封邮件发送", 
                'newGold' => $newGold
            ]);

        } catch (Exception $e) {
            $auth_pdo->rollBack();
            echo json_encode([
                'success' => false, 
                'message' => '购买失败: ' . $e->getMessage(),
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '金币不足，购买失败']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '用户不存在']);
}
?>
