# ระบบยืนยันตัวตน (Authentication Package)

แพ็กเกจ `@newla/auth` จัดการระบบล็อกอิน, Session Guard และป้องกันการเข้าถึงหน้าเว็บ

## การใช้งาน

```php
use Newla\Auth\Auth;

// ตรวจสอบอีเมลและรหัสผ่านเพื่อเข้าสู่ระบบ
if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // ล็อกอินสำเร็จ
    $user = Auth::user();
    $userId = Auth::id();
}

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (Auth::check()) {
    // ผู้ใช้ล็อกอินอยู่
}

// ออกจากระบบ
Auth::logout();
```

## การใช้ AuthMiddleware ป้องกัน Route

```php
use Newla\Auth\AuthMiddleware;

Route::middleware([AuthMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'index']);
});
```