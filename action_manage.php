<?php
require 'db.php';
header('Content-Type: application/json'); // 声明返回JSON格式

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];

    if ($action == 'delete') {
        // 先查出图片路径，用于删除物理文件
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            // 删除数据库记录
            $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($delStmt->execute([$id])) {
                // 删除硬盘上的图片文件
                if (file_exists($product['image'])) {
                    unlink($product['image']); 
                }
                echo json_encode(['success' => true, 'message' => '删除成功！']);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => '删除失败或商品不存在']);
    } elseif ($action == 'edit' && isset($_GET['price'])) {
        $newPrice = $_GET['price'];
        $stmt = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
        if ($stmt->execute([$newPrice, $id])) {
            echo json_encode(['success' => true, 'message' => '价格修改成功！', 'newPrice' => $newPrice]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => '价格修改失败']);
    } else {
        echo json_encode(['success' => false, 'message' => '参数不完整']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '缺少必要参数']);
}
?>