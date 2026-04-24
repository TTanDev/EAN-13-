<?php
require 'db.php';
header('Content-Type: application/json');

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch();

    if ($product) {
        echo json_encode(['exists' => true, 'product' => $product]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['error' => 'No barcode provided']);
}
?>