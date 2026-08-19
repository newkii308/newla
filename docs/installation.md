# คู่มือการติดตั้ง NEWLA (Installation Guide)

NEWLA สามารถติดตั้งและใช้งานได้บนทุกระบบปฏิบัติการ ทั้ง **Linux**, **macOS**, **Windows** และ **Termux (Android)**

## ความต้องการของระบบ (System Requirements)

- **PHP**: เวอร์ชัน 8.2 ขึ้นไป (8.2, 8.3, 8.4, 8.5+)
- **PHP Extensions**:
  - `pdo`, `pdo_sqlite` (หรือ `pdo_mysql` / `pdo_pgsql`)
  - `openssl` (สำหรับระบบความปลอดภัยและสร้าง Token)
  - `json` (สำหรับการอ่านและแปลงข้อมูล JSON)
  - `mbstring` (สำหรับจัดการข้อความ Multibyte / ภาษาไทย)
  - `fileinfo` (สำหรับตรวจชนิด MIME Type ของไฟล์อัปโหลด)
  - `gd` (สำหรับตกแต่งและบีบอัดรูปภาพ)
- **Composer**: เวอร์ชัน 2.0+

---

## 1. การติดตั้งบน Windows (PowerShell)

1. ติดตั้ง PHP 8.2+ และเปิดใช้งาน extensions ในไฟล์ `php.ini`:
   ```ini
   extension_dir = "ext"
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=pdo_sqlite
   extension=sqlite3
   ```

2. รันคำสั่งติดตั้งผ่าน PowerShell:
```powershell
irm newla-dev.verin.online | iex
```

หรือติดตั้งผ่าน Composer Global:
```powershell
composer global require newla/cli
```

---

## 2. การติดตั้งบน Linux (Ubuntu / Debian / Arch / Alpine)

```bash
# Ubuntu / Debian
sudo apt update
sudo apt install -y php-cli php-mbstring php-sqlite3 php-mysql php-curl php-gd php-xml composer

# ติดตั้ง NEWLA CLI
curl -fsSL newla-dev.verin.online | bash
```

---

## 3. การติดตั้งบน macOS

```bash
# ติดตั้งผ่าน Homebrew
brew install php composer

# ติดตั้ง NEWLA CLI
curl -fsSL newla-dev.verin.online | bash
```

---

## 4. การติดตั้งบน Android / Termux

ไม่ต้องรูทเครื่อง (No Root Required):

```bash
# อัปเดตรายการแพ็กเกจ
pkg update && pkg upgrade

# ติดตั้ง PHP และ Composer
pkg install -y php composer git

# ติดตั้ง NEWLA CLI
curl -fsSL newla-dev.verin.online | bash
```

---

## 5. การตรวจสอบความพร้อมของระบบ

ใช้คำสั่ง `doctor` เพื่อตรวจเช็กการตั้งค่าทั้งหมด:

```bash
newla doctor
```
---

## 6. การอัปเดต NEWLA CLI เป็นเวอร์ชันล่าสุด

เมื่อมีเวอร์ชันใหม่ปล่อยออกมา สามารถสั่งอัปเดตได้ทันทีด้วยคำสั่ง:

```bash
newla self-update
```
