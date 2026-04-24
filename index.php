<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>商品扫码系统</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 15px; background: #f4f4f9; touch-action: manipulation; box-sizing: border-box; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-shrink: 0; }
        .header h2 { margin: 0; font-size: 22px; color: #333; font-weight: 700; }
        
        .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; box-sizing:border-box; text-align: center; transition: all 0.2s; font-size: 14px;}
        .btn:active { opacity: 0.8; transform: scale(0.98); }
        
        .camera-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 0; width: 100%; }
        
        #reader { width: 100%; height: 60vh; display: none; position: relative; overflow: hidden; border-radius: 12px; background: #000; margin-bottom: 15px; box-shadow: 0 6px 15px rgba(0,0,0,0.3); flex-shrink: 0; }
        #reader video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; }
        #reader canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; }

        .embedded-btn { position: absolute; z-index: 10; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(0,0,0,0.6); border: none; color: white; border-radius: 25px; padding: 10px 18px; cursor: pointer; transition: opacity 0.3s ease, background 0.2s; opacity: 0; font-size: 14px; font-weight: bold;}
        .embedded-btn:active { background: rgba(0,0,0,0.9); }
        .embedded-btn span.icon { font-size: 18px; margin-top: -2px; }

        /* 手电筒按钮位置 - 底部居中 */
        #torch-btn { bottom: 15px; left: 50%; transform: translateX(-50%); }
        #flashlight-container { display: none; }

        .result-box { display: none; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 500px; margin: 0 auto; text-align: center; overflow-y: auto; max-height: 100%; }
        .result-box img { max-width: 100%; height: auto; margin-top: 10px; border-radius: 5px; max-height: 180px; object-fit: contain; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 14px; color: #666;}
        .form-group input { width: 100%; padding: 12px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; background: #fafafa;}
        
        .progress-container { width: 100%; background-color: #e0e0e0; border-radius: 5px; margin-top: 15px; display: none; overflow:hidden;}
        .progress-bar { width: 0%; height: 20px; background-color: #28a745; text-align: center; line-height: 20px; color: white; font-size: 12px; transition: width 0.3s; }

        /* UI 弹窗样式 (Alert) */
        .ui-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .ui-modal-box { background: white; width: 85%; max-width: 320px; border-radius: 12px; padding: 25px; box-sizing: border-box; box-shadow: 0 8px 30px rgba(0,0,0,0.3); animation: popIn 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28); text-align: left;}
        .ui-modal-title { margin: 0 0 10px 0; font-size: 18px; color: #333; }
        .ui-modal-content { font-size: 15px; color: #666; margin-bottom: 25px; line-height: 1.5; }
        .ui-modal-btns { display: flex; justify-content: flex-end; }
        .ui-btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; background: #007bff; color: white; font-weight: bold;}
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* 顶部免打扰气泡提示框 (Toast) */
        .toast-message {
            position: fixed;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(40, 167, 69, 0.95);
            color: white;
            padding: 10px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: top 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 10000;
            pointer-events: none;
        }
        .toast-message.show {
            top: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>扫码录入</h2>
    <a href="manage.php" class="btn">商品管理</a>
</div>

<div class="camera-wrapper">
    <div style="text-align: center; margin-bottom: 15px; width: 100%;">
        <button id="start-btn" class="btn" style="font-size: 16px; padding: 15px 30px; width: 100%; max-width: 300px; border-radius: 25px;">? 打开相机扫描</button>
        <p id="hint-text" style="font-size: 12px; color: #888; margin-top: 10px; display: none;">请将手机拉远至 15 厘米处</p>
    </div>

    <div id="reader">
        <div id="flashlight-container">
            <button id="torch-btn" class="embedded-btn">
                <span class="icon">?</span>
                <span id="torch-text">打开手电筒</span>
            </button>
        </div>
    </div>

    <div id="result-container" class="result-box">
        <h3 id="barcode-display"></h3>
        <div id="loading-indicator" style="display:none; margin: 20px 0;">
            <div style="display:inline-block; width:24px; height:24px; border:3px solid #f3f3f3; border-top:3px solid #007bff; border-radius:50%; animation: spin 1s linear infinite;"></div>
            <span style="margin-left:10px; color:#666; vertical-align:middle;">正在查询商品...</span>
        </div>
        
        <div id="product-info" style="display: none;">
            <h2 style="color: #28a745; margin-bottom: 5px;">找到商品了</h2>
            <p style="font-size: 18px; margin: 0 0 15px 0;">价格: ?<span id="product-price" style="font-weight: bold;"></span></p>
            <img id="product-img" src="" alt="商品图片" onerror="this.style.display='none'; document.getElementById('img-error-tip').style.display='block';" style="max-height:180px; object-fit:contain;">
            <div id="img-error-tip" style="display:none; padding:20px; background:#f8f9fa; border-radius:5px; color:#999; font-size:14px;">图片无法加载</div>
            <button onclick="location.reload()" class="btn" style="margin-top: 15px; width: 100%;">继续扫码</button>
        </div>

        <div id="add-form" style="display: none;">
            <h2 style="color: #dc3545; margin-bottom: 5px;">未录入该商品</h2>
            <p style="color: #666; margin: 0 0 15px 0;">请完善信息</p>
            <form id="upload-form">
                <input type="hidden" id="input-barcode">
                
                <div class="form-group">
                    <label>商品价格 (元):</label>
                    <input type="number" step="0.01" id="input-price" inputmode="decimal" required placeholder="例如: 12.50">
                </div>
                
                <div class="form-group">
                    <label>拍照上传商品图片:</label>
                    <input type="file" id="input-image" accept="image/*" capture="environment" required>
                    <img id="image-preview" style="display:none; max-width:100%; max-height:150px; margin-top:10px; border-radius:5px; object-fit:contain;" alt="预览">
                </div>
                
                <button type="submit" class="btn" id="submit-btn" style="width: 100%; background: #28a745; border-radius: 8px;">保存并上传</button>

                <div class="progress-container" id="progress-container">
                    <div class="progress-bar" id="progress-bar">0%</div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="ui-modal" class="ui-modal-overlay">
    <div class="ui-modal-box">
        <h3 id="ui-title" class="ui-modal-title">提示</h3>
        <div id="ui-content" class="ui-modal-content"></div>
        <div class="ui-modal-btns">
            <button id="ui-confirm" class="ui-btn">确定</button>
        </div>
    </div>
</div>

<div id="toast" class="toast-message"></div>

<script>
    document.addEventListener('gesturestart', function (e) { e.preventDefault(); });
    document.addEventListener('touchstart', function(e) { if (e.touches.length > 1) { e.preventDefault(); } }, { passive: false });

    function customAlert(title, message, callback = null) {
        const modal = document.getElementById('ui-modal');
        document.getElementById('ui-title').innerText = title;
        document.getElementById('ui-content').innerText = message;
        modal.style.display = 'flex';
        
        const confirmBtn = document.getElementById('ui-confirm');
        confirmBtn.replaceWith(confirmBtn.cloneNode(true));
        document.getElementById('ui-confirm').onclick = function() {
            modal.style.display = 'none';
            if (callback) callback();
        };
    }

    let toastTimeout;
    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.innerText = message;
        toast.classList.add('show');
        
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 2000); 
    }

    let compressedBlob = null;
    let isTorchOn = false; 
    let brightnessInterval = null; 

    function isValidEAN13(barcode) {
        if (!/^\d{13}$/.test(barcode)) return false;
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += parseInt(barcode.charAt(i)) * (i % 2 === 0 ? 1 : 3);
        }
        let checkDigit = 10 - (sum % 10);
        if (checkDigit === 10) checkDigit = 0;
        return checkDigit === parseInt(barcode.charAt(12));
    }

    function analyzeBrightness() {
        const video = document.querySelector('#reader video');
        if (!video || video.readyState !== 4 || isTorchOn) return; 

        const canvas = document.createElement('canvas');
        canvas.width = 64;
        canvas.height = 64;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        try {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            let sum = 0;
            for (let i = 0; i < imageData.length; i += 4) {
                sum += (0.299 * imageData[i] + 0.587 * imageData[i+1] + 0.114 * imageData[i+2]);
            }
            const avgLuma = sum / (canvas.width * canvas.height);
            
            const torchBtn = document.getElementById('torch-btn');
            const torchContainer = document.getElementById('flashlight-container');
            if (avgLuma < 60) {
                torchContainer.style.display = 'block';
                setTimeout(() => torchBtn.style.opacity = '1', 10);
            } else {
                torchBtn.style.opacity = '0';
                setTimeout(() => torchContainer.style.display = 'none', 500);
            }
        } catch (e) {}
    }

    window.addEventListener("beforeunload", function () {
        if (brightnessInterval) clearInterval(brightnessInterval);
        Quagga.stop();
    });

    function startScanningWithCamera() {
        Quagga.init({
            inputStream : {
                name : "Live",
                type : "LiveStream",
                target: document.querySelector('#reader'),
                constraints: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "environment", // 锁定后置主摄
                    advanced: [{ focusMode: "continuous" }]
                }
            },
            locator: { patchSize: "medium", halfSample: true },
            numOfWorkers: navigator.hardwareConcurrency || 4,
            decoder : { readers : ["ean_reader"] },
            locate: true 
        }, function(err) {
            if (err) {
                customAlert("启动失败", "无法访问相机，请检查权限。");
                stopEverything();
                return;
            }
            Quagga.start();
            
            const track = Quagga.CameraAccess.getActiveTrack();

            setTimeout(() => {
                if (track && typeof track.getCapabilities === 'function') {
                    const capabilities = track.getCapabilities();
                    
                    if (capabilities.torch) {
                        if (brightnessInterval) clearInterval(brightnessInterval);
                        brightnessInterval = setInterval(analyzeBrightness, 800);
                        
                        const torchBtn = document.getElementById('torch-btn');
                        const torchText = document.getElementById('torch-text');
                        
                        torchBtn.onclick = async function() {
                            isTorchOn = !isTorchOn;
                            try {
                                await track.applyConstraints({ advanced: [{ torch: isTorchOn }] });
                                torchBtn.style.background = isTorchOn ? 'rgba(255, 193, 7, 0.9)' : 'rgba(0,0,0,0.6)';
                                torchText.innerText = isTorchOn ? '关闭手电筒' : '打开手电筒';
                            } catch (err) { isTorchOn = false; }
                        };
                    }
                }
            }, 1000);
        });
    }

    function stopEverything() {
        if (brightnessInterval) clearInterval(brightnessInterval);
        Quagga.stop();
        isTorchOn = false; 
        
        document.getElementById('flashlight-container').style.display = 'none';
        document.getElementById('torch-btn').style.opacity = '0';
        document.getElementById('torch-btn').style.background = 'rgba(0,0,0,0.6)'; 
        document.getElementById('torch-text').innerText = '打开手电筒'; 
        
        document.getElementById('start-btn').style.display = 'inline-block';
        document.getElementById('hint-text').style.display = 'none';
        document.getElementById('reader').style.display = 'none';
    }

    document.getElementById('start-btn').addEventListener('click', function() {
        this.style.display = 'none';
        document.getElementById('hint-text').style.display = 'block';
        document.getElementById('reader').style.display = 'block';
        document.getElementById('result-container').style.display = 'none';
        
        startScanningWithCamera(); 
    });

    Quagga.onDetected(function(result) {
        let code = result.codeResult.code;
        if (isValidEAN13(code)) {
            stopEverything();
            
            document.getElementById('result-container').style.display = 'block';
            document.getElementById('barcode-display').innerText = "条码: " + code;
            document.getElementById('loading-indicator').style.display = 'block';
            document.getElementById('product-info').style.display = 'none';
            document.getElementById('add-form').style.display = 'none';

            fetch('api_check.php?barcode=' + encodeURIComponent(code))
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading-indicator').style.display = 'none';
                    if(data.exists) {
                        document.getElementById('product-info').style.display = 'block';
                        document.getElementById('add-form').style.display = 'none';
                        document.getElementById('product-price').innerText = data.product.price;
                        document.getElementById('product-img').src = data.product.image;
                        document.getElementById('product-img').style.display = 'inline-block';
                        document.getElementById('img-error-tip').style.display = 'none';
                    } else {
                        document.getElementById('product-info').style.display = 'none';
                        document.getElementById('add-form').style.display = 'block';
                        document.getElementById('input-barcode').value = code;
                    }
                })
                .catch(() => {
                    document.getElementById('loading-indicator').style.display = 'none';
                    customAlert('网络错误', '查询失败，请检查网络连接后重试。');
                });
        }
    });

    document.getElementById('input-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            const previewImg = document.getElementById('image-preview');
            previewImg.src = event.target.result;
            previewImg.style.display = 'block';
            
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const MAX_WIDTH = 800;
                let width = img.width;
                let height = img.height;

                if (width > MAX_WIDTH) {
                    height = height * (MAX_WIDTH / width);
                    width = MAX_WIDTH;
                }
                
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(function(blob) { compressedBlob = blob; }, 'image/jpeg', 0.7);
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(file);
    });

    document.getElementById('upload-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!compressedBlob) {
            customAlert('提示', '请等待图片压缩完成或重新选择图片！');
            return;
        }

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = '正在上传...';

        const formData = new FormData();
        formData.append('barcode', document.getElementById('input-barcode').value);
        formData.append('price', document.getElementById('input-price').value);
        formData.append('image', compressedBlob, 'product.jpg'); 

        document.getElementById('progress-container').style.display = 'block';
        const progressBar = document.getElementById('progress-bar');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_product.php', true);
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressBar.innerText = percentComplete + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                if(res.success) {
                    showToast('录入成功');
                    setTimeout(() => window.location.reload(), 1000); // 录入成功使用 Toast 提升连贯感
                } else {
                    customAlert('错误', '录入失败: ' + res.message);
                    submitBtn.disabled = false;
                    submitBtn.innerText = '保存并上传';
                }
            } else {
                customAlert('网络错误', '请求服务器失败，请重试');
                submitBtn.disabled = false;
            }
        };
        xhr.send(formData);
    });
</script>

</body>
</html>
