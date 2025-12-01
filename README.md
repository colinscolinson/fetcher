# Data Fetcher

Автоматизированный тестовый сервис для импорта данных (Продажи, Заказы, Склады, Доходы) через их API в удаленную базу данных.
---

## 📋 Требования

- PHP 8.0+  
- Composer  
- MySQL (или MariaDB)  
- Git  
- nginx / Apache (для продакшена)

---

## 🚀 Установка и запуск

### 1️⃣ Клонируем проект

```bash
git clone https://github.com/colinscolinson/fetcher.git
cd fetcher

### 2️⃣ Устанавливаем зависимости
composer install --no-interaction --prefer-dist

### 3️⃣ Настраиваем .env.example
cp .env.example .env

### 3️⃣ Настраиваем .env.example
cp .env.example .env

API_HOST=http://Айпи_из_документации:6969
API_KEY=Ключ_из_документации
API_LIMIT=500

DB_CONNECTION=mysql
DB_HOST=sql8.freesqldatabase.com
DB_PORT=3306
DB_DATABASE=Имя базы данных
DB_USERNAME=Имя пользователя
DB_PASSWORD=Пароль

composer audit


### 🗄️ Настройка базы данных
php artisan key:generate
php artisan migrate --force

php artisan wb:import --from=2025-11-01 --to=2025-11-05
