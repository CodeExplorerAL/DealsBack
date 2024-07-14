# Deals 優惠資訊網站 - 後端專案

## 版本
1.0.0

## 專案簡介
此專案為一個優惠資訊網站，開發時間為 2024 年 3 月至 2024 年 4 月，由5名成員共同合作開發。

## 技術堆疊

- **前端**：React
- **後端**：PHP (Laravel Framework)
- **數據庫**：MySQL (MariaDB)
- **版本控制**：GitHub
- **開發工具**：VS Code

## 主要職責：後端開發

- 採用 Laravel 框架實現 MVC 架構
- 運用 Laravel 的 Eloquent ORM 進行數據庫操作
- 實施 Migrations 和 Seeders
- 設計並實現 RESTful API
- 使用 Laravel 的 Task Scheduling 自動化重複性任務
- 集成郵件通知系統，提升用戶體驗和系統互動性

## 其他貢獻

- 協助前端開發，參與 React 組件設計和 API 集成
- 參與資料庫設計


## 環境要求
- PHP >= 8.1
- Node.js >= 14
- MySQL >= 5.7
- Composer
- npm

## 操作說明

### 一、將前/後端資料克隆於本地

#### 前端資料
```sh
git clone https://github.com/CodeExplorerAL/DealsFront.git
```

#### 後端資料
```sh
git clone https://github.com/CodeExplorerAL/DealsBack.git
```

### 二、添加資料庫資料

1. 創建資料庫 `Deals`：
    - 使用命令行工具（如 MySQL 客戶端）或資料庫管理工具（如 phpMyAdmin）創建資料庫：
    ```sql
    CREATE DATABASE Deals;
    ```

2. 匯入資料庫資料 `DealsBack/Deals.sql`：
    - 使用 MySQL 命令行工具：
    ```sh
    mysql -u your_username -p Deals < path/to/Deals.sql
    ```
    - 或使用 phpMyAdmin 上傳並匯入 `Deals.sql`。

### 三、開啟 Server

#### 前端

1. 進入前端資料夾：
    ```sh
    cd DealsFront
    ```

2. 安裝依賴包：
    ```sh
    npm install
    ```

3. 開啟前端 server：
    ```sh
    npm start
    ```

#### 後端

1. 進入後端資料夾：
    ```sh
    cd DealsBack
    ```

2. 安裝依賴包：
    ```sh
    composer install
    ```

3. 開啟後端 server：
    ```sh
    php artisan serve
    ```

### 四、瀏覽文章

1. 打開瀏覽器，訪問以下地址：
    ```sh
    http://localhost:3000/page1
    ```

> **注意：** 如有缺少相關 package，請根據提示安裝。
