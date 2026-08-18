# NEWLA CLI & Core — Full Project Specification

คุณคือ Senior PHP Framework Engineer และ CLI Tool Developer

ให้สร้างโปรเจกต์ชื่อ **NEWLA** เป็น PHP developer toolkit/framework ขนาดเล็กสำหรับสร้างเว็บไซต์และ REST API แบบ production-ready โดยมีแนวคิดคล้าย Laravel ในด้าน architecture และ developer experience แต่ **ห้ามใช้ Laravel หรือพึ่งพา Laravel**

NEWLA ต้องเน้น:

- PHP Native + Composer
- Modular Architecture
- Reusable Packages
- CLI-first workflow
- Security by default
- Production-ready structure
- ใช้ซ้ำได้กับหลายโปรเจกต์
- รองรับ Linux
- รองรับ macOS
- รองรับ Windows
- รองรับ Termux / Android
- ทำงานได้กับ terminal ทั่วไป
- ไม่บังคับ IDE ใด ๆ
- ไม่บังคับ Docker
- ไม่บังคับ Node.js สำหรับการทำงานของ backend

---

## 1. เป้าหมายของ NEWLA

NEWLA ต้องช่วยให้ developer สร้างเว็บไซต์ใหม่โดยไม่ต้องเริ่มจากศูนย์

ตัวอย่าง:

```
newla create shop my-shop
cd my-shop
newla add auth
newla add security
newla add storage
newla add validator
newla add logger
newla dev
```

จากนั้น developer สามารถโฟกัสกับ:

- Frontend
- UI/UX
- Business Logic
- Database
- API
- ฟีเจอร์เฉพาะของลูกค้า

โดยไม่ต้องเขียนระบบพื้นฐานซ้ำทุกโปรเจกต์

---

## 2. ห้ามใช้ Laravel

ห้าม:

- require Laravel
- ใช้ Laravel Framework
- ใช้ Laravel Container
- ใช้ Laravel Router
- ใช้ Laravel Eloquent
- copy source code ของ Laravel

สามารถออกแบบ API และ architecture ให้ developer ใช้งานง่ายคล้าย framework สมัยใหม่ได้ แต่ implementation ต้องเป็นของ NEWLA เอง

---

## 3. Technology Stack

**Core:**

- PHP 8.2+
- Composer
- PSR standards ตามความเหมาะสม
- PDO สำหรับ database
- PHPUnit สำหรับ testing

**CLI:**

- PHP CLI
- Composer
- Symfony Console สามารถใช้ได้สำหรับ CLI หากเหมาะสม
- ต้องทำงานบน terminal มาตรฐาน

**Database:**

- MySQL / MariaDB
- PostgreSQL
- SQLite

**Optional:**

- Redis
- Cloudflare R2
- Amazon S3

ห้ามบังคับให้ทุกระบบต้องติดตั้ง dependency ขนาดใหญ่

---

## 4. NEWLA Architecture

```
Project
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Middleware/
│   ├── Requests/
│   └── Providers/
│
├── bootstrap/
│   └── app.php
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── cache.php
│   ├── storage.php
│   └── logging.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   ├── index.php
│   └── assets/
│
├── resources/
│   ├── views/
│   └── assets/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   ├── logs/
│   ├── cache/
│   └── uploads/
│
├── tests/
│
├── vendor/
│
├── .env
├── .env.example
├── composer.json
├── newla.json
└── README.md
```

`public/` ต้องเป็น document root

ห้ามให้ web server เข้าถึง:

```
.env
app/
config/
database/
storage/
vendor/
```

โดยตรง

---

## 5. Request Lifecycle

ออกแบบ request flow:

```
HTTP Request
     ↓
public/index.php
     ↓
Bootstrap
     ↓
Application
     ↓
Router
     ↓
Middleware
     ↓
Controller
     ↓
Service
     ↓
Model / Repository
     ↓
Database
     ↓
Response
```

ต้องอธิบายและ implement แต่ละขั้นตอน

---

## 6. NEWLA Core

สร้าง package: `newla/core`

Core ต้องประกอบด้วยอย่างน้อย:

- Router
- Request
- Response
- Middleware
- Container
- Config
- Environment
- Application
- Exception Handler
- Database abstraction

ตัวอย่าง:

```php
Route::get('/', [HomeController::class, 'index']);

Route::post('/login', [AuthController::class, 'login']);
```

รองรับ:

- GET
- POST
- PUT
- PATCH
- DELETE

รองรับ route parameters:

```
/users/{id}
/products/{id}
```

---

## 7. Controller

ตัวอย่าง:

```php
class UserController
{
    public function show(Request $request, int $id)
    {
        return Response::json([
            'user' => $id
        ]);
    }
}
```

รองรับ dependency injection ผ่าน container

---

## 8. Middleware

สร้างระบบ:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', ...);
});
```

ตัวอย่าง middleware:

- AuthMiddleware
- CorsMiddleware
- RateLimitMiddleware
- CsrfMiddleware

ต้องสามารถสร้าง middleware เองได้

CLI:

```
newla make:middleware AuthMiddleware
```

---

## 9. NEWLA Packages

ออกแบบ ecosystem:

- @newla/security
- @newla/validator
- @newla/logger
- @newla/image
- @newla/storage
- @newla/auth
- @newla/api

แต่ละ package ต้องเป็น modular package

ไม่ควรเอาทุกอย่างยัดเข้า Core

---

## 10. @newla/security

สร้างระบบ:

- CSRF
- Password hashing
- Password verification
- Secure random token
- Security headers
- Rate limiting
- Input security helpers
- Session security
- Authentication-related security utilities

ตัวอย่าง:

```php
Security::csrfToken();
Security::verifyCsrf($token);
Security::hashPassword($password);
Security::verifyPassword($password, $hash);
```

ห้าม implement cryptography เองถ้ามี PHP standard function ที่เหมาะสม

ใช้:

```php
password_hash()
password_verify()
random_bytes()
hash_hmac()
```

ตามความเหมาะสม

---

## 11. @newla/validator

สร้าง validation system:

```php
Validator::make($data, [
    'username' => 'required|string|min:4|max:20',
    'email' => 'required|email',
    'age' => 'integer|min:13'
]);
```

ต้องรองรับ:

- required
- nullable
- string
- integer
- numeric
- boolean
- email
- url
- min
- max
- length
- regex
- in
- not_in
- unique

สามารถสร้าง custom rule ได้

---

## 12. @newla/logger

รองรับ:

```php
Logger::debug();
Logger::info();
Logger::warning();
Logger::error();
Logger::critical();
```

Log channels:

- file
- stderr
- database (optional)
- webhook (optional)

รองรับ:

```
storage/logs/app.log
storage/logs/error.log
storage/logs/security.log
```

---

## 13. @newla/image

สร้างระบบ image processing:

```php
Image::resize();
Image::thumbnail();
Image::webp();
Image::optimize();
```

ต้องตรวจ:

- MIME type
- extension
- file size
- image dimensions

รองรับ output:

- JPEG
- PNG
- WebP
- AVIF ถ้าระบบรองรับ

ต้องป้องกัน malicious upload

---

## 14. @newla/storage

ออกแบบ abstraction:

```php
Storage::put($file, $path);
Storage::get($path);
Storage::delete($path);
Storage::exists($path);
Storage::url($path);
```

Drivers:

- local
- s3
- r2

สามารถเพิ่ม driver ในอนาคต

Configuration:

```
STORAGE_DRIVER=local
```

หรือ:

```
STORAGE_DRIVER=r2
```

---

## 15. @newla/auth

ระบบ:

- Register
- Login
- Logout
- Session
- Password hashing
- Password reset
- Email verification
- Remember me

ต้องสามารถใช้กับ database ที่ developer กำหนด

ห้ามบังคับ frontend

Backend ต้องสามารถใช้ได้กับ:

- HTML
- AJAX
- Fetch
- React
- Vue
- Next.js
- Mobile application

---

## 16. @newla/api

กำหนดมาตรฐาน JSON response:

**Success:**

```json
{
    "success": true,
    "data": {}
}
```

**Error:**

```json
{
    "success": false,
    "error": {
        "message": "Unauthorized",
        "code": "UNAUTHORIZED"
    }
}
```

รองรับ:

- HTTP status
- pagination
- validation errors
- API exceptions

---

## 17. Database

ใช้ PDO

รองรับ:

- MySQL
- MariaDB
- PostgreSQL
- SQLite

สร้าง Database abstraction ที่ไม่ผูกกับ ORM ขนาดใหญ่

ต้องมี:

- Connection
- Query
- Transaction
- Migration
- Seeder

CLI:

```
newla migrate
newla migrate:fresh
newla migrate:rollback
newla db:seed
```

ห้าม implement SQL query builder ที่ซับซ้อนเกินความจำเป็นใน MVP

---

## 18. CLI Commands

สร้างคำสั่ง:

```
newla
newla --version
newla help
```

**Project:**

```
newla create
newla init
newla info
newla doctor
```

**Packages:**

```
newla add
newla remove
newla update
newla list
```

**Development:**

```
newla dev
newla serve
newla test
```

**Code generation:**

```
newla make:controller UserController
newla make:model User
newla make:middleware AuthMiddleware
newla make:service PaymentService
newla make:migration create_users_table
```

**Database:**

```
newla migrate
newla migrate:rollback
newla migrate:fresh
newla db:seed
```

**Cache:**

```
newla cache:clear
```

**Production:**

```
newla build
newla doctor
```

---

## 19. CLI Cross Platform

NEWLA CLI ต้องทำงานบน:

- Linux
- Ubuntu
- Debian
- Arch
- Alpine
- macOS
- Windows
- Termux / Android

ห้าม hard-code path เช่น:

```
/home/user/
C:\Users\
```

ใช้ PHP functions:

```php
PHP_OS_FAMILY
DIRECTORY_SEPARATOR
getcwd()
```

และ filesystem abstraction ที่เหมาะสม

---

## 20. Termux Support

ต้องออกแบบให้ทำงานบน Termux โดยไม่ต้อง root

ตัวอย่าง installation:

```
pkg update
pkg install php
pkg install composer
```

จากนั้น:

```
composer global require newla/cli
```

หรือมี installer:

```
curl -fsSL https://newla.dev/install.sh | sh
```

แต่ installer ต้องตรวจ environment ก่อน เช่น:

```
Checking PHP...
Checking Composer...
Checking PATH...
Checking permissions...
```

ต้องมี fallback สำหรับระบบที่ไม่มี sudo

ห้าม assume ว่าผู้ใช้มี:

- sudo
- systemd
- Docker
- apt

---

## 21. Windows Support

รองรับ:

- PowerShell
- CMD
- Git Bash

หลีกเลี่ยง shell-specific commands ในตัว CLI

หากต้อง execute command ให้มี abstraction สำหรับ OS

---

## 22. Linux/macOS

รองรับ:

```
newla dev
```

โดยใช้ PHP built-in server:

```
php -S 127.0.0.1:8000 -t public
```

ห้ามให้ public server ชี้ไปที่ project root

---

## 23. Development Server

คำสั่ง:

```
newla dev
```

ควรแสดง:

```
NEWLA Development Server

Local: http://127.0.0.1:8000

PHP: 8.x
Environment: local

Press Ctrl+C to stop
```

สามารถเลือก port:

```
newla dev --port=8080
```

---

## 24. Environment

สร้าง:

```
.env
.env.example
```

ตัวอย่าง:

```
APP_NAME=NEWLA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=newla
DB_USERNAME=root
DB_PASSWORD=
```

ห้าม commit `.env`

สร้าง `.gitignore` ให้อัตโนมัติ

---

## 25. Production Deployment

ต้องเขียน documentation สำหรับ:

### Shared Hosting

รองรับ DirectAdmin/cPanel ที่สามารถกำหนด Document Root ได้

Document Root:

```
/public
```

หาก host ไม่รองรับ ให้มี fallback แต่ต้องอธิบาย security implications

### VPS

รองรับ:

- Ubuntu
- Nginx
- PHP-FPM
- MySQL

Architecture:

```
Internet
   ↓
Nginx
   ↓
public/
   ↓
NEWLA
   ↓
PHP-FPM
```

ต้องมี deployment documentation

### Apache

สร้าง `.htaccess` สำหรับ `public/`

รองรับ URL rewriting

### Termux

อธิบายวิธี:

```
newla dev
```

สำหรับ local development และวิธีเชื่อมต่อ Git repository

---

## 26. Git Workflow

สร้าง documentation:

```
git init
git add .
git commit -m "Initial project"
git remote add origin ...
git push
```

ห้าม commit:

```
.env
vendor/
storage/logs/
storage/cache/
```

---

## 27. Composer

กำหนด package structure ให้เป็นมาตรฐาน Composer

ตัวอย่าง:

```json
{
    "name": "newla/core",
    "type": "library",
    "autoload": {
        "psr-4": {
            "Newla\\": "src/"
        }
    }
}
```

ทุก package ต้องใช้ PSR-4

---

## 28. NEWLA Registry

ออกแบบให้ในอนาคตสามารถมี registry: `registry.newla.dev`

ตัวอย่าง:

```
newla search security
newla add security
newla update
```

แต่ MVP ไม่ต้องสร้าง registry จริง

ช่วงแรกให้ NEWLA ใช้ Composer เป็น backend package manager

---

## 29. Package Versioning

ใช้ Semantic Versioning: `MAJOR.MINOR.PATCH`

ตัวอย่าง:

```
1.0.0
1.1.0
1.1.1
2.0.0
```

ต้องอธิบาย backward compatibility

---

## 30. Configuration

สร้าง `newla.json`

ตัวอย่าง:

```json
{
    "name": "my-shop",
    "version": "1.0.0",
    "framework": "newla",
    "packages": {
        "security": "^1.0",
        "validator": "^1.0"
    }
}
```

CLI ใช้ไฟล์นี้ตรวจสอบ project state

---

## 31. Doctor

คำสั่ง:

```
newla doctor
```

ตรวจ:

- PHP
- Composer
- Extensions
- Environment
- Permissions
- Database
- Storage
- Configuration
- Packages

ตัวอย่าง:

```
NEWLA Doctor

✓ PHP 8.3
✓ Composer
✓ PDO
✓ OpenSSL
✓ JSON
✓ Project structure
✓ .env

⚠ Database connection failed

1 warning
```

---

## 32. Security Requirements

ตั้ง security เป็น default

ต้อง:

- Escape output
- Prepared statements
- Password hashing
- CSRF
- Secure session settings
- File upload validation
- Rate limiting
- Security headers
- Secrets จาก environment
- ไม่แสดง stack trace ใน production
- ป้องกัน directory listing
- ป้องกัน access `.env`
- ป้องกัน access source files

ห้ามเก็บ password แบบ plaintext

ห้ามประกอบ SQL ด้วย string interpolation เมื่อรับ input จากผู้ใช้

---

## 33. Testing

ใช้ PHPUnit

ต้องมี:

```
tests/
├── Unit/
└── Feature/
```

Test:

- Router
- Request
- Response
- Validator
- Security
- Logger
- Storage
- Database
- CLI commands

คำสั่ง:

```
newla test
```

---

## 34. Documentation

สร้าง documentation ครบ:

```
README.md

docs/
├── installation.md
├── quickstart.md
├── architecture.md
├── cli.md
├── packages.md
├── routing.md
├── controllers.md
├── middleware.md
├── database.md
├── authentication.md
├── security.md
├── storage.md
├── deployment.md
├── termux.md
├── windows.md
├── linux.md
└── contributing.md
```

Documentation ต้องมี command ตัวอย่างจริง

---

## 35. Installation Experience

ต้องรองรับ:

```
newla --version
```

ตัวอย่าง:

```
NEWLA CLI 1.0.0
PHP 8.3
Platform: Linux
```

สร้าง project:

```
newla create my-project
```

ต้องแสดง progress:

```
Creating project...

✓ Directory
✓ Composer
✓ Core
✓ Configuration
✓ Environment
✓ Routes
✓ Public directory
✓ Storage
✓ Gitignore

Project created successfully.

Next:

cd my-project
newla dev
```

---

## 36. Code Quality

ต้อง:

- PSR-4
- PSR-12
- Strict typing
- PHPDoc ในจุดสำคัญ
- SOLID
- Dependency Injection
- Interface-based design
- ไม่ใช้ global state โดยไม่จำเป็น
- Error handling ที่ชัดเจน

หลีกเลี่ยง:

- God classes
- giant helper files
- duplicate code
- hard-coded paths
- hard-coded credentials

---

## 37. MVP Priority

อย่าสร้างทุกอย่างพร้อมกัน

ลำดับ:

**Phase 1**
- NEWLA CLI
- NEWLA Core
- Project Generator
- Router
- Request
- Response
- Config
- Environment
- Composer

**Phase 2**
- Database
- Migration
- Middleware
- Controller
- CLI generators
- Testing

**Phase 3**
- Security
- Validator
- Logger
- Storage
- Image

**Phase 4**
- Auth
- API
- Caching
- Queue

**Phase 5**
- Registry
- Package discovery
- Cloud services

---

## 38. Important Design Rule

NEWLA ต้องไม่พยายามเป็น Laravel clone

เป้าหมายคือ:

```
Laravel-like Developer Experience
+
Native PHP
+
Small Core
+
Modular Packages
+
CLI
+
Easy Deployment
+
Termux Support
```

ต้องให้ developer เข้าใจ code ได้ง่าย

หาก framework ทำอะไรซับซ้อนเกินความจำเป็น ให้เลือก implementation ที่เรียบง่ายกว่า

---

## 39. Final Deliverables

สร้าง repository ที่พร้อมใช้งานจริง โดยประกอบด้วย:

```
newla/
├── packages/
│   ├── core/
│   ├── security/
│   ├── validator/
│   ├── logger/
│   ├── image/
│   ├── storage/
│   ├── auth/
│   └── api/
│
├── cli/
├── examples/
├── tests/
├── docs/
├── composer.json
├── README.md
├── LICENSE
└── CONTRIBUTING.md
```

ต้องมี working MVP ก่อน แล้วค่อยเพิ่ม package อื่น

ห้ามสร้าง mock implementation ที่ดูเหมือนทำงานแต่ใช้งานจริงไม่ได้

ทุก command ที่ระบุใน documentation ต้องตรงกับ implementation จริง

ทุก package ต้องมี:

- README
- composer.json
- src/
- tests/

และสามารถติดตั้งผ่าน Composer ได้

---

## 40. สิ่งที่ต้องแสดงหลังสร้างเสร็จ

แสดง:

1. Architecture ทั้งหมด
2. File structure
3. Installation
4. CLI commands ทั้งหมด
5. ตัวอย่างสร้าง project
6. ตัวอย่างสร้าง controller
7. ตัวอย่างสร้าง model
8. ตัวอย่าง route
9. ตัวอย่าง middleware
10. ตัวอย่าง migration
11. ตัวอย่าง package installation
12. วิธีใช้บน Termux
13. วิธีใช้บน Linux
14. วิธีใช้บน macOS
15. วิธีใช้บน Windows
16. วิธี deploy DirectAdmin/cPanel
17. วิธี deploy Nginx + PHP-FPM
18. วิธีใช้ Apache
19. วิธี Git deploy
20. วิธีเขียน NEWLA Package ของตัวเอง

---

ก่อนเขียน code ให้สรุป architecture และ dependency graph ก่อน จากนั้น implement ตาม Phase 1 → Phase 2 → Phase 3 โดยต้องให้แต่ละ phase สามารถรันและทดสอบได้จริง
