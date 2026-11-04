# Buổi 04 — Cài Laravel 12 và Git

Dự án học Laravel 12 với MySQL. Phiên bản đã kiểm tra: PHP 8.2.12, Laravel Framework 12.70.2. Dự án có thêm Sanctum và các bài tập khách hàng, sản phẩm, đơn hàng.

## 1. Cài đặt và dependency lock

Yêu cầu PHP 8.2 trở lên đáp ứng composer.lock, Composer 2, Git và MySQL (hoặc MariaDB của XAMPP). Bật các extension Laravel yêu cầu, đặc biệt pdo_mysql.

Tạo dự án mới ở thư mục cha với tên chưa tồn tại:

```powershell
composer create-project laravel/laravel my-app "^12.0" --no-interaction
cd my-app
```

Với mã nguồn hiện có, chạy trong thư mục dự án:

```powershell
composer install --no-interaction
composer check-platform-reqs
composer show --direct
php artisan --version --no-interaction
```

Framework phải là 12.x. Composer quản lý thư viện PHP; Artisan là CLI Laravel. composer install cài phiên bản trong composer.lock; composer update giải lại phiên bản và sửa lock. Đưa composer.json và composer.lock vào Git.

## 2. Cấu hình MySQL

Bật MySQL trong XAMPP. Tạo database bằng phpMyAdmin hoặc MySQL client:

```sql
CREATE DATABASE IF NOT EXISTS laravel_api
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Chỉ sao chép mẫu nếu chưa có .env:

```powershell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

Sửa .env theo tài khoản MySQL trên máy:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=
```

root và mật khẩu trống là mẫu cho XAMPP local. Nếu dùng tài khoản hoặc mật khẩu khác, chỉ điền thông tin thật vào .env.

Khi tạo .env mới, sinh khóa ứng dụng một lần:

```powershell
php artisan key:generate --no-interaction
```

Không sinh lại khóa khi đã có dữ liệu mã hóa. Sau khi sửa cấu hình:

```powershell
php artisan config:clear --no-interaction
php artisan migrate --no-interaction
php artisan migrate:status --no-interaction
```

Migration mặc định tạo bảng người dùng, session, cache và job. Lệnh migrate cũng chạy migration bài tập hiện có, bỏ qua các migration đã chạy. Không dùng migrate:fresh trên database cần giữ dữ liệu.

## 3. Chạy ứng dụng

```powershell
php artisan serve --host=127.0.0.1 --port=8000 --no-interaction
```

Giữ terminal chạy, mở http://127.0.0.1:8000. Kiểm tra ở terminal PowerShell khác:

```powershell
(Invoke-WebRequest http://127.0.0.1:8000 -UseBasicParsing).StatusCode
(Invoke-WebRequest http://127.0.0.1:8000/up -UseBasicParsing).StatusCode
php artisan route:list --except-vendor --no-interaction
php artisan test --compact tests/Feature/ExampleTest.php
```

Cả hai HTTP status phải là 200. /up kiểm tra ứng dụng khởi động; migrate:status kiểm tra kết nối MySQL. Test trang chủ dùng SQLite trong bộ nhớ theo phpunit.xml, không thay thế kiểm tra MySQL.

Nếu dùng Vite hoặc thiếu manifest, cài Node.js đáp ứng Vite trong package.json, rồi chạy:

```powershell
npm install
npm run build
```

Giữ package-lock.json trong Git sau lần cài đầu; lần sau dùng npm ci. Khi phát triển giao diện, có thể chạy npm run dev trong terminal riêng cùng server Laravel. Dừng server bằng Ctrl+C.

## 4. Git và thông tin bí mật

Khởi tạo một lần nếu chưa có repository:

```powershell
git init -b main
git check-ignore .env
git add .
git diff --cached --stat
git diff --cached
```

Kiểm tra diff: không đưa .env, vendor/, node_modules/, log hoặc database SQLite vào Git. .env.example chỉ chứa giá trị mẫu; khóa ứng dụng, mật khẩu và khóa dịch vụ để trống. Không thêm database dump chứa dữ liệu thật.

```powershell
git ls-files -- .env ".env.*"
git status --short
git commit -m "Initialize Laravel 12 learning project"
```

Lệnh git ls-files trên chỉ nên hiển thị .env.example. Nếu Git yêu cầu danh tính, cấu hình user.name và user.email của bạn trước khi commit. Nếu bí mật đã lọt vào commit, cần thu hồi/đổi bí mật đó; .gitignore không xóa lịch sử.

## 5. Cấu trúc và vòng đời request

- app/: model, controller và logic ứng dụng.
- routes/: route; resources/: Blade, CSS và JavaScript.
- bootstrap/app.php: bootstrap, routing, middleware và exception; bootstrap/providers.php: danh sách provider.
- config/: cấu hình; database/: migration, factory, seeder; tests/: kiểm thử.
- public/index.php: điểm vào HTTP; document root Apache phải trỏ vào public/.
- storage/: log, session và cache; vendor/: thư viện Composer.
- .env: cấu hình riêng và bí mật, ngoài Git. .env.example: mẫu chia sẻ không có bí mật. Trong mã ứng dụng dùng config() để đọc cấu hình, hỗ trợ cache cấu hình.

Request tổng quan: trình duyệt → public/index.php → bootstrap và service provider → middleware → router → controller/closure → response → middleware → trình duyệt.

## 6. Tiêu chí hoàn thành

- Framework là 12.x.
- Trang chủ trả HTTP 200.
- MySQL kết nối được, migration hiển thị Ran.
- Git theo dõi mã nguồn, lock và cấu hình mẫu; không có mật khẩu/token thật.
