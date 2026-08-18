# ระบบความปลอดภัย (Security by Default)

NEWLA ถูกออกแบบโดยยึดหลัก **Security by Default** มอบความปลอดภัยระดับสูงสุดตั้งแต่เริ่มโปรเจกต์

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