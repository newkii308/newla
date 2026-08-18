# ระบบตรวจสอบความถูกต้องของข้อมูล (Validation)

แพ็กเกจ `@newla/validator` ช่วยตรวจสอบข้อมูลที่รับเข้ามาอย่างแม่นยำ ป้องกันข้อมูลผิดพลาดและช่องโหว่ความปลอดภัย

## การใช้งานพื้นฐาน

```php
use Newla\Validator\Validator;

$validator = Validator::make($request->all(), [
    'username' => 'required|string|min:4|max:20',
    'email' => 'required|email|unique:users,email',
    'age' => 'nullable|integer|min:18',
    'role' => 'required|in:admin,editor,user',
]);

if ($validator->fails()) {
    // ดึง Error ทั้งหมด
    $errors = $validator->errors()->all();
    return json(['errors' => $errors], 422);
}

// ดึงข้อมูลที่ผ่านการตรวจสอบแล้ว
$validatedData = $validator->validated();
```

## กฎการตรวจสอบที่มีให้ใช้งาน (Built-in Rules)

| กฎ (Rule) | คำอธิบาย |
|---|---|
| `required` | ต้องมีข้อมูล และไม่เป็นค่าว่าง |
| `nullable` | อนุญาตให้เป็น null หรือว่างได้ |
| `string` | ต้องเป็นข้อความ |
| `integer` | ต้องเป็นจำนวนเต็ม |
| `numeric` | ต้องเป็นตัวเลข (ทั้งจำนวนเต็มและทศนิยม) |
| `boolean` | ต้องเป็นค่า boolean (true, false, 1, 0) |
| `email` | ต้องเป็นรูปแบบอีเมลที่ถูกต้องตาม RFC |
| `url` | ต้องเป็นรูปแบบ URL ที่ถูกต้อง |
| `min:value` | ค่าต่ำสุด (สำหรับตัวเลข) หรือความยาวต่ำสุด (สำหรับข้อความ) |
| `max:value` | ค่าสูงสุด (สำหรับตัวเลข) หรือความยาวสูงสุด (สำหรับข้อความ) |
| `length:min,max` | ความยาวต้องอยู่ในช่วงที่กำหนด |
| `regex:pattern` | ต้องตรงตาม Regular Expression |
| `in:a,b,c` | ค่าต้องตรงกับรายการที่ระบุ |
| `not_in:a,b,c` | ค่าต้องไม่อยู่ในรายการที่ระบุ |
| `confirmed` | ต้องมีฟิลด์ยืนยันที่ตรงกัน (เช่น `password_confirmation`) |
| `unique:table,col` | ตรวจสอบว่าไม่ซ้ำกับข้อมูลในฐานข้อมูล |

## การสร้าง Custom Rule

```php
Validator::extend('thai_id_card', function ($attribute, $value) {
    return preg_match('/^[0-9]{13}$/', (string) $value) === 1;
}, 'รหัสบัตรประชาชน :attribute ต้องเป็นตัวเลข 13 หลัก');
```