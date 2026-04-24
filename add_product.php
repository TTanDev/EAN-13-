<?php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcode = $_POST['barcode'];
    $price = $_POST['price'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 因为前端压缩统一成了 jpg，后缀直接写 .jpg
        $newFileName = $uploadDir . $barcode . '_' . time() . '.jpg';

        if (move_uploaded_file($_FILES['image']['tmp_name'], $newFileName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (barcode, price, image) VALUES (?, ?, ?)");
                $stmt->execute([$barcode, $price, $newFileName]);
                
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => '数据库错误: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '图片保存失败']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '未接收到图片']);
    }
}
?>