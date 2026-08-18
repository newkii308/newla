# สถาปัตยกรรมและวงจรการทำงาน (Architecture & Request Lifecycle)

NEWLA ออกแบบด้วยสถาปัตยกรรมแบบ Layered Architecture ที่เรียบง่าย ทำงานตรงไปตรงมา และมีประสิทธิภาพสูง

```text
HTTP Request
     ↓
public/index.php (Front Controller & Document Root)
     ↓
Bootstrap (bootstrap/app.php - โหลด Environment, Config และ Autoload)
     ↓
Application Container (IoC Container สำหรับจัดการ Dependency Injection)
     ↓
Router (จับคู่วิถี URL และอ่าน Route Parameters)
     ↓
Middleware Pipeline (กรอง Request ผ่าน CSRF, CORS, Rate Limit, Auth)
     ↓
Controller Action (เรียกฟังก์ชันและทำ Method Injection ผ่าน Container)
     ↓
Domain Service / Business Logic
     ↓
Active Record Model / Query Builder
     ↓
Database (ติดต่อฐานข้อมูลผ่าน PDO ป้องกัน SQL Injection)
     ↓
HTTP Response (ส่งข้อมูลกลับในรูปแบบ HTML, JSON, Redirect, หรือ Download)
```

## โครงสร้างโฟลเดอร์ของโปรเจกต์ (Directory Structure)

```text
โฟลเดอร์หลักของโปรเจกต์
├── app/
│   ├── Controllers/       # คลาส Controller สำหรับรับและตอบกลับ Request
│   ├── Models/            # คลาส Model สำหรับเชื่อมโยงตารางฐานข้อมูล (Active Record)
│   ├── Services/          # คลาสจัดการ Logic ทางธุรกิจของระบบ
│   ├── Middleware/        # คลาส Middleware กรองการเข้าถึง
│   ├── Requests/          # คลาส Form Request และ Validation
│   └── Providers/         # คลาส Service Provider ลงทะเบียนเซอร์วิส
│
├── bootstrap/
│   └── app.php            # จุดเริ่มต้นโหลดระบบและตั้งค่า Application Instance
│
├── config/
│   ├── app.php            # การตั้งค่าแอปพลิเคชัน (ชื่อ, โซนเวลา, Debug)
│   ├── database.php       # การตั้งค่าการเชื่อมต่อฐานข้อมูล (SQLite, MySQL, PostgreSQL)
│   ├── storage.php        # การตั้งค่า Disk จัดเก็บไฟล์ (Local, S3, Cloudflare R2)
│   └── logging.php        # การตั้งค่าช่องทางบันทึก Log
│
├── database/
│   ├── migrations/        # ไฟล์ Migration สำหรับสร้างและแก้ไขตารางฐานข้อมูล
│   └── seeders/           # ไฟล์ Seeder สำหรับใส่ข้อมูลเริ่มต้น
│
├── public/
│   ├── index.php          # ทางเข้าหลักของเว็บ (Document Root)
│   ├── .htaccess          # กฎ URL Rewriting สำหรับ Apache
│   └── assets/            # ไฟล์ Assets สาธารณะ (CSS, JavaScript, รูปภาพ)
│
├── resources/
│   ├── views/             # ไฟล์ View Template (PHP Native View Engine)
│   └── assets/            # ซอร์สโค้ด Assets ต้นฉบับ
│
├── routes/
│   ├── web.php            # เส้นทางสำหรับหน้าเว็บทั่วไป (Browser Routes)
│   └── api.php            # เส้นทางสำหรับ REST API (เข้าถึงผ่าน prefix /api อัตโนมัติ)
│
├── storage/
│   ├── logs/              # โฟลเดอร์เก็บไฟล์ Log (app.log, error.log, security.log)
│   ├── cache/             # โฟลเดอร์เก็บข้อมูลแคชและ Rate Limiter
│   └── uploads/           # โฟลเดอร์เก็บไฟล์ที่ผู้ใช้ทำการอัปโหลด
│
├── tests/
│   ├── Unit/              # ชุดทดสอบระดับ Unit Test
│   └── Feature/           # ชุดทดสอบระดับ Feature / Integration Test
│
├── .env                   # ไฟล์เก็บค่าความลับและการตั้งค่าระบบ (ห้าม Commit ขึ้น Git)
├── .env.example           # ไฟล์ตัวอย่างการตั้งค่า Environment
├── composer.json          # ไฟล์กำหนด Package Dependencies ตามมาตรฐาน Composer
├── newla.json             # ไฟล์ Manifest ของโปรเจกต์ NEWLA
└── README.md              # คำอธิบายโปรเจกต์
```