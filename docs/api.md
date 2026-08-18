# มาตรฐานการตอบกลับ REST API (API Package)

แพ็กเกจ `@newla/api` ช่วยจัดรูปแบบโครงสร้างข้อมูล JSON ให้เป็นมาตรฐานเดียวกันทั้งระบบ

## การตอบกลับเมื่อสำเร็จ (Success Response)

```php
use Newla\Api\ApiResponse;

return ApiResponse::success(['id' => 1, 'name' => 'สินค้าชิ้นที่ 1'], 'ดึงข้อมูลสำเร็จ');
```
ผลลัพธ์ JSON:
```json
{
  "success": true,
  "message": "ดึงข้อมูลสำเร็จ",
  "data": {
    "id": 1,
    "name": "สินค้าชิ้นที่ 1"
  }
}
```

## การตอบกลับเมื่อเกิดข้อผิดพลาด (Error Response)

```php
return ApiResponse::error('ไม่พบข้อมูลที่ต้องการ', 'NOT_FOUND', 404);
```
ผลลัพธ์ JSON:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "ไม่พบข้อมูลที่ต้องการ"
  }
}
```

## การตอบกลับข้อมูลแบบแบ่งหน้า (Paginated Response)

```php
$paginated = Product::where('is_active', 1)->paginate(perPage: 10, page: 1);
return ApiResponse::paginate($paginated, 'ดึงรายการสินค้าสำเร็จ');
```