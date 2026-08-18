# ระบบบันทึก Log (Logging Package)

แพ็กเกจ `@newla/logger` จัดการบันทึกเหตุการณ์และข้อผิดพลาดของระบบแยกเป็นหมวดหมู่ตามมาตรฐาน PSR-3

## การใช้งาน

```php
use Newla\Logger\Logger;

// บันทึกข้อมูลทั่วไป
Logger::info('ผู้ใช้เข้าสู่ระบบสำเร็จ', ['user_id' => 42, 'email' => 'somchai@example.com']);

// บันทึกคำเตือนและข้อผิดพลาด
Logger::warning('หน่วยความจำของเซิร์ฟเวอร์ใกล้เต็ม');
Logger::error('ไม่สามารถเชื่อมต่อ Payment Gateway ได้', ['order_id' => 1002]);
Logger::critical('ฐานข้อมูลหลักหยุดทำงาน');

// บันทึกลง Channel ความปลอดภัยเฉพาะ (storage/logs/security.log)
Logger::channel('security')->warning('พบการพยายามล็อกอินล้มเหลวเกินกำหนด', ['ip' => $request->ip()]);
```