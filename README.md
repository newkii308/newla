# NEWLA — Modern Native PHP Framework & Developer Toolkit

<p align="center">
  <strong>เฟรมเวิร์ก PHP ยุคใหม่ ขนาดเล็ก ทำงานเร็ว ปลอดภัยในตัว แยกเป็นโมดูล ไม่ต้องพึ่งพา Laravel</strong>
</p>

---

## ⚡ NEWLA คืออะไร?

**NEWLA** (นิวลา) คือ PHP Framework และ CLI Developer Toolkit ยุคใหม่ที่ถูกออกแบบมาเพื่อให้การสร้างเว็บไซต์และ REST API มีความสะดวกรวดเร็ว ได้มาตรฐานแบบโปรดักชัน พร้อมสถาปัตยกรรมที่เข้าใจง่าย เป็นระเบียบ โดยเขียนขึ้นด้วย **Native PHP 8.2+ 100% ไม่มีการดึงหรือพึ่งพา Laravel Framework**

### ✨ จุดเด่นสำคัญ

- **PHP Native 8.2+ แท้**: โหลดเร็วเป็นพิเศษ ใช้ทรัพยากรหน่วยความจำต่ำมาก เริ่มต้นทำงานได้ทันที
- **Modular Architecture (สถาปัตยกรรมแยกโมดูล)**: แยกแพ็กเกจเป็นเอกเทศ เช่น `@newla/core`, `@newla/security`, `@newla/validator`, `@newla/logger`, `@newla/storage`, `@newla/image`, `@newla/auth`, และ `@newla/api`
- **CLI-First Workflow**: มีคำสั่ง `newla` ครบครันสำหรับสร้างโปรเจกต์ สร้าง Controller/Model/Migration, ตรวจเช็กระบบ (`newla doctor`), และรัน Dev Server (`newla dev`)
- **Security by Default (ปลอดภัยโดยค่าเริ่มต้น)**: มีระบบป้องกัน CSRF, เข้ารหัสรหัสผ่านด้วย Argon2id/Bcrypt, จัดการ Session คุกกี้แบบปลอดภัย, Security Headers, การตัดอักขระแปลกปลอม (Sanitizer) และ Rate Limiter ป้องกันการยิงสแปม
- **รองรับทุกระบบปฏิบัติการ (Cross-Platform)**: ใช้งานได้สมบูรณ์บน **Linux**, **macOS**, **Windows** (PowerShell, CMD, Git Bash) และ **Android / Termux** โดยไม่ต้องรูทเครื่อง
- **Deploy ขึ้นเซิร์ฟเวอร์ง่าย**: รองรับทั้ง Shared Hosting (cPanel, DirectAdmin), VPS (Nginx + PHP-FPM) และ Apache

---

## 🚀 เริ่มต้นใช้งานอย่างรวดเร็ว (Quick Start)

### 1. ติดตั้ง NEWLA CLI

#### สำหรับ Linux / macOS / Termux:
```bash
curl -fsSL https://raw.githubusercontent.com/newla-php/newla/main/install.sh | sh
```

#### สำหรับ Windows (PowerShell):
```powershell
iwr -useb https://raw.githubusercontent.com/newla-php/newla/main/install.ps1 | iex
```

#### หรือติดตั้งผ่าน Composer Global:
```bash
composer global require newla/cli
```

---

### 2. สร้างโปรเจกต์ใหม่

```bash
# สร้างโปรเจกต์ร้านค้าใหม่
newla create my-shop

# เข้าสู่โฟลเดอร์โปรเจกต์
cd my-shop

# เพิ่มโมดูลที่ต้องการใช้งาน
newla add security
newla add validator
newla add storage

# เริ่มต้น Development Server
newla dev
```

เปิดบราวเซอร์ไปที่ `http://127.0.0.1:8000` ได้ทันที!

---

## 📦 แพ็กเกจของ NEWLA (Modular Packages)

| แพ็กเกจ | รายละเอียด | เวอร์ชัน |
|---|---|---|
| [`@newla/core`](packages/core) | แกนหลักของเฟรมเวิร์ก, Router, Request, Response, Container DI, Database & Migration | `^1.0` |
| [`@newla/security`](packages/security) | ระบบ CSRF, Password Hashing, Security Headers, Rate Limiting, Session Management | `^1.0` |
| [`@newla/validator`](packages/validator) | ระบบตรวจสอบความถูกต้องของข้อมูลพร้อม Rule สำเร็จรูป และรองรับ Custom Rule | `^1.0` |
| [`@newla/logger`](packages/logger) | ระบบบันทึก Log แยก Channel (ไฟล์, Stderr, ฐานข้อมูล) พร้อม Formatter | `^1.0` |
| [`@newla/storage`](packages/storage) | ระบบจัดการไฟล์ (Local Disk, AWS S3, Cloudflare R2) โดยไม่ต้องใช้ SDK ขนาดใหญ่ | `^1.0` |
| [`@newla/image`](packages/image) | จัดการรูปภาพ (Resize, Thumbnail, แปลงเป็น WebP) พร้อมตรวจความปลอดภัยไฟล์อัปโหลด | `^1.0` |
| [`@newla/auth`](packages/auth) | ระบบล็อกอิน, Session Guard, ตรวจสอบสิทธิ์ และ Middleware ป้องกันหน้าเว็บ | `^1.0` |
| [`@newla/api`](packages/api) | จัดรูปแบบ JSON REST API มาตรฐาน Success/Error และการแบ่งหน้า (Pagination) | `^1.0` |

---

## 🛠️ สรุปคำสั่ง CLI ทั้งหมด

```text
คำสั่งเกี่ยวกับโปรเจกต์:
  newla create <name>     สร้างโปรเจกต์ใหม่พร้อมโครงสร้างแบบ Production-ready
  newla init              สร้างโครงสร้าง NEWLA ในโฟลเดอร์ปัจจุบัน
  newla info              แสดงข้อมูลระบบ PHP, OS และ Packages ที่ติดตั้ง
  newla doctor            ตรวจสุขภาพระบบ PHP Extensions และความพร้อมของโปรเจกต์

คำสั่งจัดการแพ็กเกจ:
  newla add <pkg>         ติดตั้งแพ็กเกจโมดูล (เช่น newla add security)
  newla remove <pkg>      ลบแพ็กเกจออกจาก newla.json
  newla update            อัปเดตแพ็กเกจและ dependencies
  newla list              ดูรายการคำสั่งทั้งหมด หรือดูรายชื่อ packages

คำสั่งสำหรับพัฒนาโปรแกรม:
  newla dev               เริ่มรัน Development Server ด้วย PHP Built-in (php -S)
  newla serve             คำสั่งเสมือนของ dev
  newla test              รันชุดทดสอบอัตโนมัติทั้งหมด (Automated Test Suite)

คำสั่งสร้างโค้ดอัตโนมัติ (Generators):
  newla make:controller   สร้าง Controller class ใน app/Controllers/
  newla make:model        สร้าง Active Record Model ใน app/Models/
  newla make:middleware   สร้าง Middleware ใน app/Middleware/
  newla make:service      สร้าง Service class ใน app/Services/
  newla make:migration    สร้างไฟล์ Database Migration ใน database/migrations/
  newla make:seeder       สร้าง Database Seeder ใน database/seeders/

คำสั่งจัดการฐานข้อมูล:
  newla migrate           รัน Migration ที่ยังไม่ได้ทำงาน
  newla migrate:rollback  ย้อนกลับ Migration ชุดล่าสุด
  newla migrate:fresh     ล้างตารางทั้งหมดแล้วรัน Migration ใหม่ตั้งแต่ต้น
  newla db:seed           รัน Seeder เติมข้อมูลเริ่มต้น

คำสั่งสำหรับ Production:
  newla cache:clear       ล้างไฟล์แคชใน storage/cache/
  newla build             ตรวจสอบและเตรียมโปรเจกต์สำหรับขึ้น Production
```

---

## 📚 เอกสารคู่มือการใช้งาน (Documentation)

สามารถอ่านคู่มือภาษาไทยแบบละเอียดได้ที่โฟลเดอร์ [`docs/`](docs/):

- [คู่มือการติดตั้ง (Installation)](docs/installation.md)
- [คู่มือเริ่มต้นอย่างรวดเร็ว (Quickstart)](docs/quickstart.md)
- [สถาปัตยกรรมและการทำงาน (Architecture)](docs/architecture.md)
- [คู่มือคำสั่ง CLI ทั้งหมด (CLI Reference)](docs/cli.md)
- [ระบบ Routing และ HTTP (Routing)](docs/routing.md)
- [การใช้งาน Controller และ Dependency Injection](docs/controllers.md)
- [การใช้งาน Middleware](docs/middleware.md)
- [ระบบจัดการฐานข้อมูล, Migration และ Model](docs/database.md)
- [ระบบความปลอดภัยและ CSRF (Security)](docs/security.md)
- [การตรวจสอบข้อมูล (Validation)](docs/validation.md)
- [การบันทึก Log (Logging)](docs/logging.md)
- [ระบบจัดเก็บไฟล์และ Cloudflare R2 / S3 (Storage)](docs/storage.md)
- [การประมวลผลรูปภาพ (Image Processing)](docs/image.md)
- [ระบบยืนยันตัวตน (Authentication)](docs/authentication.md)
- [มาตรฐานการตอบกลับ REST API](docs/api.md)
- [คู่มือการ Deploy ขึ้นเซิร์ฟเวอร์จริง (VPS / Nginx / Apache / Shared Hosting)](docs/deployment.md)
- [คู่มือการใช้งานบน Termux / Android](docs/termux.md)
- [คู่มือการใช้งานบน Windows](docs/windows.md)
- [คู่มือการใช้งานบน Linux](docs/linux.md)
- [แนวทางการร่วมพัฒนา (Contributing)](docs/contributing.md)

---

## 📄 ลิขสิทธิ์ (License)

NEWLA Framework เป็นซอฟต์แวร์เปิดเผยซอร์สโค้ด (Open-source) ภายใต้ลิขสิทธิ์ [MIT License](LICENSE)