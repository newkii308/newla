# การประมวลผลรูปภาพ (Image Processing)

แพ็กเกจ `@newla/image` ใช้ฟังก์ชัน GD Native ของ PHP ช่วยย่อขนาด ตัดภาพ และแปลงเป็น WebP พร้อมระบบตรวจสอบความปลอดภัยของไฟล์อัปโหลด

## การใช้งาน

```php
use Newla\Image\Image;

// ย่อขนาดรูปภาพ (รักษาสัดส่วนภาพเดิม)
Image::resize('source.jpg', 'resized.jpg', width: 800, height: 600);

// ตัดภาพเป็นรูปสี่เหลี่ยมจัตุรัสสำหรับ Thumbnail
Image::thumbnail('profile.jpg', 'thumb.jpg', width: 200, height: 200);

// แปลงไฟล์เป็น WebP เพื่อประหยัดพื้นที่และโหลดไว
Image::webp('banner.png', 'banner.webp', quality: 85);

// ใช้งานแบบต่อเนื่อง (Method Chaining)
Image::make('photo.jpg')
    ->resize(1200, 800)
    ->crop(x: 0, y: 0, width: 1000, height: 800)
    ->toWebp('optimized.webp', quality: 80);
```

## การตรวจสอบความปลอดภัยไฟล์อัปโหลด

ป้องกันการปลอมแปลงนามสกุลไฟล์ และไฟล์รูปภาพที่มีโค้ด PHP แฝง (Polyglot Exploits):

```php
if (!Image::validate($_FILES['avatar']['tmp_name'], maxSizeBytes: 2 * 1024 * 1024)) {
    throw new \Exception("ไฟล์รูปภาพไม่ถูกต้อง หรือขนาดเกิน 2MB");
}
```