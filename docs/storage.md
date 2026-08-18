# ระบบจัดเก็บไฟล์ (Storage Package)

แพ็กเกจ `@newla/storage` มีระบบจัดการไฟล์ที่สลับใช้งานระหว่าง Local Disk, AWS S3 และ Cloudflare R2 ได้อย่างราบรื่น

## การใช้งาน Local Storage

```php
use Newla\Storage\Storage;

// บันทึกไฟล์
Storage::put('documents/invoice_101.pdf', $fileData);

// อ่านเนื้อหาไฟล์
$content = Storage::get('documents/invoice_101.pdf');

// ตรวจสอบว่ามีไฟล์อยู่หรือไม่
if (Storage::exists('documents/invoice_101.pdf')) {
    // พบไฟล์
}

// รับ URL สาธารณะของไฟล์
$url = Storage::url('documents/invoice_101.pdf');

// ลบไฟล์
Storage::delete('documents/invoice_101.pdf');
```

## การเชื่อมต่อ Cloudflare R2 และ AWS S3

ตั้งค่าใน `.env`:
```env
STORAGE_DRIVER=r2
R2_ACCESS_KEY_ID=your_access_key
R2_SECRET_ACCESS_KEY=your_secret_key
R2_BUCKET=my-bucket
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://cdn.yourdomain.com
```

ใช้งานผ่าน Storage Facade:
```php
Storage::disk('r2')->put('uploads/photo.jpg', $imageData);
```