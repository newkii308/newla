# คู่มือการใช้งานบน Windows

NEWLA รองรับการทำงานบน Windows ทั้งบน **PowerShell**, **Command Prompt (CMD)** และ **Git Bash**

## การตั้งค่าเริ่มต้น

1. ดาวน์โหลดและติดตั้ง PHP 8.2+ จาก [windows.php.net](https://windows.php.net)
2. เปิด Extension ใน `php.ini`:
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
3. เพิ่มโฟลเดอร์ PHP ใน System PATH ของ Windows
4. ติดตั้ง NEWLA CLI ผ่าน PowerShell:
   ```powershell
   iwr -useb https://raw.githubusercontent.com/newla-php/newla/main/install.ps1 | iex
   ```
5. ตรวจสอบความพร้อมของระบบ:
   ```powershell
   newla doctor
   ```