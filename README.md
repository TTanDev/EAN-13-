# 条码扫描仓库管理系统

一款基于 **PHP + MySQL** 开发的轻量级条形码扫描与商品仓库管理工具。专为移动端优化，用手机摄像头扫描商品条码即可快速录入或查询商品信息，适合小店铺、仓库、零售摊位等场景使用。

---

## 功能特性

### 扫码录入 (`index.php`)
- **实时条码识别**：调用手机后置摄像头，基于 [QuaggaJS](https://serratus.github.io/quaggaJS/) 实现 EAN-13 条码扫描
- **智能补光检测**：自动分析环境亮度，光线过暗时提示开启手电筒（需设备支持）
- **商品自动检索**：扫描后自动查询数据库，已存在则显示价格与图片，未录入则引导新增
- **查询 Loading 状态**：扫码后显示旋转加载动画，避免网络延迟时用户困惑
- **拍照上传**：支持调用相机拍摄商品照片，前端自动压缩（800px 宽度，JPEG 0.7 质量）
- **拍照本地预览**：选择图片后即时展示预览图，确认无误再提交
- **上传进度条**：直观展示图片上传进度
- **网络异常提示**：所有接口请求增加错误兜底，断网时明确弹窗提示
- **校验机制**：内置 EAN-13 校验码验证，减少误扫

### 商品管理 (`manage.php`)
- **分页列表**：展示所有商品，支持分页浏览，顶部显示总量与页码信息
- **图片点击放大**：表格缩略图支持点击全屏查看，方便核对商品细节
- **无刷新改价**：点击改价按钮，弹窗输入新价格，AJAX 实时更新
- **无刷新删除**：删除商品同时自动清理服务器上的图片文件
- **空状态引导**：无商品时展示图标与引导文案，一键跳转扫码录入
- **网络异常提示**：改价/删除操作增加错误兜底，断网时明确提示
- **一键返回**：快速回到扫码页面继续录入

---

## 快速开始

### 环境要求
- PHP >= 7.0
- MySQL >= 5.6
- Web 服务器（Nginx / Apache）
- HTTPS 环境（**必须**，摄像头 API 需要安全上下文）

### 安装步骤

1. **克隆项目**
   ```bash
   git clone https://github.com/TTanDev/EAN-13-.git
   cd barcode-warehouse-system
   ```

2. **导入数据库**
   ```bash
   mysql -u root -p < Database.sql
   ```
   或手动创建 `products` 表：
   ```sql
   CREATE TABLE `products` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `barcode` varchar(100) NOT NULL COMMENT '商品条码',
     `price` decimal(10,2) NOT NULL COMMENT '商品价格',
     `image` varchar(255) NOT NULL COMMENT '商品图片路径',
     `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     UNIQUE KEY `barcode` (`barcode`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

3. **配置数据库连接**
   编辑 `db.php`，填写你的数据库信息：
   ```php
   $host = 'localhost';
   $db   = 'your_database';
   $user = 'your_username';
   $pass = 'your_password';
   ```

4. **创建图片目录（如不存在会自动创建）**
   ```bash
   mkdir photos
   chmod 777 photos
   ```

5. **访问系统**
   将项目部署到 Web 服务器，通过浏览器访问 `index.php`。

---

## 项目结构

```
.
├── index.php              # 扫码录入首页
├── manage.php             # 商品管理后台
├── add_product.php        # 新增商品接口
├── api_check.php          # 条码查询接口
├── action_manage.php      # 改价 / 删除接口
├── db.php                 # 数据库连接配置
├── photos/                # 商品图片存储目录
└── Database.sql    # 数据库备份文件
```

---

## 安全提示

- **生产环境请务必修改 `db.php` 中的数据库密码**，避免使用默认或弱密码
- 建议将 `db.php` 加入 `.gitignore`，使用 `db.example.php` 作为配置模板
- 建议为 `photos/` 目录设置适当的访问权限，防止直接遍历
- 考虑为管理后台增加登录认证（当前版本为简化设计，未内置权限系统）

---

## 移动端适配

- 针对 iOS / Android 浏览器做了专项优化
- 禁用双指缩放，防止扫码界面误操作
- 触摸反馈与按钮点击优化
- viewport 固定缩放比例，确保 UI 一致性

---

## 技术栈

| 层级 | 技术 |
|------|------|
| 后端 | PHP (原生) + PDO + MySQL |
| 前端 | HTML5 + CSS3 + Vanilla JavaScript |
| 扫码引擎 | QuaggaJS |
| 数据交换 | JSON + Fetch API / XMLHttpRequest |

---

## 开源协议

[MIT](LICENSE)

---

> 如果你觉得这个项目对你有帮助，欢迎点个 Star！
