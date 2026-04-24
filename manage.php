<?php
require 'db.php';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>商品管理</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 15px; background: #f4f4f9; touch-action: manipulation; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap:10px;}
        .btn { padding: 8px 15px; background: #007bff; color: white; border-radius: 5px; text-decoration: none; border:none; cursor:pointer; transition: all 0.15s; }
        .btn:active { opacity: 0.8; transform: scale(0.96); }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: black; margin-right: 6px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #f8f9fa; }
        img { max-width: 60px; height: auto; border-radius: 4px; }
        .pagination { margin-top: 20px; text-align: center; }
        .pagination a { padding: 8px 12px; margin: 0 3px; border: 1px solid #ddd; text-decoration: none; background: white; color: #333;}
        .pagination a.active { background: #007bff; color: white; border-color: #007bff; }

        /* 自定义 UI 弹窗样式 */
        .ui-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .ui-modal-box { background: white; width: 85%; max-width: 320px; border-radius: 10px; padding: 20px; box-sizing: border-box; box-shadow: 0 4px 15px rgba(0,0,0,0.2); animation: popIn 0.3s ease; }
        .ui-modal-title { margin: 0 0 10px 0; font-size: 18px; color: #333; }
        .ui-modal-content { font-size: 15px; color: #666; margin-bottom: 15px; line-height: 1.5; }
        .ui-modal-input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; display: none; font-size: 16px; margin-bottom: 15px; }
        .ui-modal-btns { display: flex; justify-content: flex-end; gap: 10px; }
        .ui-btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .ui-btn-cancel { background: #e0e0e0; color: #333; display: none; }
        .ui-btn-confirm { background: #007bff; color: white; }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<div class="header">
    <h2>商品管理</h2>
    <a href="index.php" class="btn">返回扫码</a>
</div>

<div style="text-align:center; color:#666; font-size:14px; margin-bottom:10px;">
    共 <?= $totalRows ?> 条，第 <?= $page ?> / <?= max(1, $totalPages) ?> 页
</div>
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>图片</th>
                <th>条码</th>
                <th>价格 (元)</th>
                <th>录入时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($products)): ?>
                <tr><td colspan="6" style="padding:40px 0; color:#999;">
                    <div style="font-size:48px; margin-bottom:10px;">?</div>
                    <div style="font-size:16px;">暂无商品数据</div>
                    <div style="font-size:13px; margin-top:5px;"><a href="index.php" style="color:#007bff;">去扫码录入</a></div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($products as $row): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="图" onclick="openLightbox(this.src)" style="cursor:pointer;"></td>
                    <td><?= htmlspecialchars($row['barcode']) ?></td>
                    <td id="price-<?= $row['id'] ?>">?<?= htmlspecialchars($row['price']) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-warning" onclick="editPrice(<?= $row['id'] ?>, '<?= $row['price'] ?>')">改价</button>
                        <button class="btn btn-danger" onclick="deleteProduct(<?= $row['id'] ?>)">删除</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">上一页</a>
    <?php endif; ?>

    <?php 
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    for ($i = $start; $i <= $end; $i++): 
    ?>
        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">下一页</a>
    <?php endif; ?>
</div>

<div id="ui-modal" class="ui-modal-overlay">
    <div class="ui-modal-box">
        <h3 id="ui-title" class="ui-modal-title">提示</h3>
        <div id="ui-content" class="ui-modal-content"></div>
        <input type="number" step="0.01" id="ui-input" class="ui-modal-input">
        <div class="ui-modal-btns">
            <button id="ui-cancel" class="ui-btn ui-btn-cancel">取消</button>
            <button id="ui-confirm" class="ui-btn ui-btn-confirm">确定</button>
        </div>
    </div>
</div>

<script>
    // --- 彻底禁止 iOS 双指放大和缩放手势 ---
    document.addEventListener('gesturestart', function (e) { e.preventDefault(); });
    document.addEventListener('touchstart', function(e) { if (e.touches.length > 1) { e.preventDefault(); } }, { passive: false });

    // --- 自定义 UI 弹窗核心逻辑 ---
    function customModal(type, title, message, defaultValue = '', callback = null) {
        const modal = document.getElementById('ui-modal');
        const titleEl = document.getElementById('ui-title');
        const contentEl = document.getElementById('ui-content');
        const inputEl = document.getElementById('ui-input');
        const cancelBtn = document.getElementById('ui-cancel');
        const confirmBtn = document.getElementById('ui-confirm');

        titleEl.innerText = title;
        contentEl.innerText = message;
        inputEl.value = defaultValue;
        
        // 样式重置
        inputEl.style.display = (type === 'prompt') ? 'block' : 'none';
        cancelBtn.style.display = (type === 'alert') ? 'none' : 'block';
        modal.style.display = 'flex';

        // 移除旧事件，防止重复触发
        confirmBtn.replaceWith(confirmBtn.cloneNode(true));
        cancelBtn.replaceWith(cancelBtn.cloneNode(true));
        const newConfirmBtn = document.getElementById('ui-confirm');
        const newCancelBtn = document.getElementById('ui-cancel');

        newConfirmBtn.onclick = function() {
            modal.style.display = 'none';
            if (callback) callback(type === 'prompt' ? inputEl.value : true);
        };

        newCancelBtn.onclick = function() {
            modal.style.display = 'none';
            if (callback) callback(false);
        };
    }

    // --- 业务：无刷新删除商品 ---
    function deleteProduct(id) {
        customModal('confirm', '删除确认', '确定要删除这个商品吗？(会同时删除图片)', '', function(res) {
            if (res) {
                fetch('action_manage.php?action=delete&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // 无刷新移除 DOM 元素
                        const row = document.getElementById('row-' + id);
                        if(row) row.remove();
                        customModal('alert', '成功', '商品已删除');
                    } else {
                        customModal('alert', '错误', data.message);
                    }
                })
                .catch(() => {
                    customModal('alert', '网络错误', '请求失败，请检查网络连接。');
                });
            }
        });
    }

    // --- 业务：无刷新修改价格 ---
    function editPrice(id, oldPrice) {
        customModal('prompt', '修改价格', '请输入新的价格 (元):', oldPrice, function(newPrice) {
            if (newPrice !== false && newPrice.trim() !== "" && !isNaN(newPrice)) {
                fetch('action_manage.php?action=edit&id=' + id + '&price=' + newPrice)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // 无刷新更新 DOM 中的价格文本
                        document.getElementById('price-' + id).innerText = '?' + data.newPrice;
                        // 更新按钮上绑定的旧价格
                        const btn = document.querySelector(`#row-${id} .btn-warning`);
                        btn.setAttribute('onclick', `editPrice(${id}, '${data.newPrice}')`);
                        customModal('alert', '成功', '价格修改成功！');
                    } else {
                        customModal('alert', '错误', data.message);
                    }
                })
                .catch(() => {
                    customModal('alert', '网络错误', '请求失败，请检查网络连接。');
                });
            } else if (newPrice !== false) {
                customModal('alert', '错误', '请输入有效的数字！');
            }
        });
    }
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').style.display = 'flex';
    }
</script>

<div id="lightbox" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center;">
    <img id="lightbox-img" src="" style="max-width:90%; max-height:90%; border-radius:8px;" onclick="event.stopPropagation()">
</div>

</body>
</html>