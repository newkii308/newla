# แนวทางการร่วมพัฒนา NEWLA (Contributing Guide)

ขอขอบคุณทุกท่านที่สนใจร่วมพัฒนา NEWLA Framework!

## มาตรฐานการเขียนโค้ด
- ใช้ PHP 8.2+ Strict Typing (`declare(strict_types=1);` ในทุกไฟล์)
- ยึดตามมาตรฐานการจัดรูปแบบโค้ด **PSR-12**
- ออกแบบโค้ดตามหลัก SOLID และ Dependency Injection
- เขียน Unit & Feature Test รองรับการเปลี่ยนแปลงเสมอ
- รันชุดทดสอบด้วยคำสั่ง `newla test` หรือ `php tests/runner.php` ให้ผ่านครบ 100% ก่อนส่ง Pull Request