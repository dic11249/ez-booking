# ez-booking  

ez-booking 是一個基於 Laravel 框架的線上預訂系統，旨在提供使用者簡單方便的預訂體驗。  

## 功能特性  

- **使用者註冊與登入**：  
  提供使用者註冊帳號並安全登入的功能。  
- **預訂管理**：  
  使用者可以查看、建立、編輯及取消預訂。  
- **管理者後台**：  
  管理者可以管理使用者、查看所有預訂紀錄並進行操作。  

## 安裝步驟  
1. **複製專案** 
```bash
git clone https://github.com/dic11249/ez-booking.git
```
2. **安裝依賴套件**
```bash
composer install
npm install
npm run build
```
3. **環境設定**
```bash
cp .env.example .env

php artisan key:generate
```
4. **資料庫遷移**
```bash
php artisan migrate
```

## 資料庫設計
[dbdiagram.io](https://dbdiagram.io/d/673cc04ee9daa85aca006d5d)