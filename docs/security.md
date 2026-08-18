# ระบบความปลอดภัย (Security by Default)

NEWLA ถูกออกแบบโดยยึดหลัก **Security by Default** มอบความปลอดภัยระดับสูงสุดตั้งแต่เริ่มโปรเจกต์

---

## 1. การป้องกัน CSRF (Cross-Site Request Forgery)

ใน Form หน้าเว็บ HTML / PHP:
```html
<form method="POST" action="/login">
    <?= csrf_field() ?>
    <input type="email" name="email">
    <button type="submit">เข้าสู่ระบบ</button>
</form>
```

ใน JavaScript / Fetch API:
```javascript
fetch('/api/checkout', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '<?= csrf_token() ?>',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
});
```

---

## 2. การเข้ารหัสรหัสผ่าน (Password Hashing)

ใช้ฟังก์ชันความปลอดภัยมาตรฐานของ PHP (Argon2id และ Bcrypt):

```php
use Newla\Security\Security;

// แฮชรหัสผ่าน
$hashed = Security::hashPassword('MySecurePassword123!');

// ตรวจสอบรหัสผ่าน
if (Security::verifyPassword('MySecurePassword123!', $hashed)) {
    // รหัสผ่านถูกต้อง
}
```

---

## 3. ระบบจำกัดความถี่ Request (Rate Limiting)

ป้องกันการยิง Brute-force และ DoS:

```php
use Newla\Security\RateLimit\RateLimitMiddleware;

// จำกัด 10 ครั้งต่อ 1 นาที
Route::middleware([new RateLimitMiddleware(maxAttempts: 10, decayMinutes: 1)])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
```

---

## 4. Security Headers Middleware

แนบส่วนหัวความปลอดภัย HTTP ตามมาตรฐาน OWASP:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN` (ป้องกัน Clickjacking)
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

---

## 5. การป้องกัน Path Traversal ใน Local Storage

NEWLA Storage Driver มีระบบตรวจสอบและตัด Path Segment ที่อันตราย (`..`, `../`, `..\`) ออกจาก Root Directory โดยอัตโนมัติ:

```php
use Newla\Storage\Storage;

// พาธอันตรายจะถูก Reject ทันทีด้วย InvalidArgumentException
// Storage::disk('local')->get('../secret.txt'); // Throws Exception!

// ใช้งานพาธปกติได้อย่างปลอดภัย
Storage::disk('local')->put('slips/2026/receipt.png', $fileContent);
```

---

## 6. Trusted Proxies & การป้องกัน IP Spoofing (Cloudflare Integration)

เพื่อป้องกันการปลอมแปลง Header `X-Forwarded-For` สำหรับระบบ Rate Limiting ตัว `Request::ip()` จะอ่านค่าจาก Header ก็ต่อเมื่อเชื่อมต่อผ่าน **Trusted Proxies** ที่กำหนดไว้เท่านั้น:

```php
use Newla\Core\Http\Request;

// เปิดใช้งาน Cloudflare IP Ranges
Request::setTrustedProxies(Request::CLOUDFLARE_PROXIES);

// หรือระบุ IP Proxy ของคุณเอง
Request::setTrustedProxies(['127.0.0.1', '10.0.0.0/8']);
```

---

## 7. การป้องกัน XSS ด้วยฟังก์ชัน `e($value)` (View Escaping)

ในการแสดงผลค่าตัวแปรจากผู้ใช้ใน View ให้ใช้ฟังก์ชัน `e()` หรือ `$this->e()` เสมอ:

```php
<h1>ยินดีต้อนรับ <?= e($username) ?></h1>
<p><?= e($userInput) ?></p>
```

---

## 8. การป้องกัน Timing Attack & ระบบ Remember-Me

- **SessionGuard** มีระบบ Constant-time Defense ตรวจสอบรหัสผ่านด้วย Dummy Hash เสมอแม้ไม่พบบัญชีผู้ใช้ เพื่อป้องกัน Hacker คาดเดารายชื่อผู้ใช้จากระยะเวลา Response Time
- **Remember-Me Cookie** ใช้ Token ที่สุ่มด้วย `TokenGenerator::randomBase64()`, เก็บ Hash ในฐานข้อมูล, ตั้ง Cookie แบบ `HttpOnly`, `SameSite=Lax`, `Secure` และหมุนเวียน Token (Rotate) ทุกครั้งที่ใช้งาน