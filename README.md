# BookARoom

好想工作室會議室預約系統

## 開發環境需求

* PHP v8.2
* Laravel v11
* MySQL
* Filament v3
* Docker
* Ngrok

## 安裝

1. 取得專案原始碼
2. 安裝相依套件
   ```bash
   ./vendor/bin/sail composer install
   ```
3. 啟動
   ```bash
   ./vendor/bin/sail up -d
   ```
4. 複製環境變數範本並更新：
   ```bash
   cp .env.example .env
   ```
5. 執行資料庫遷移與種子：
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

## 貢獻

依以下流程：

1. fork 此專案
2. 建立功能分支
3. 提交修改
4. 推送到遠端
5. 建立 PR

## 授權

本專案採用 MIT License。
