# ระบบ Routing

NEWLA มีระบบ Route ที่ยืดหยุ่น ใช้งานง่าย รองรับทั้ง HTTP Verbs ทุกรูปแบบ, Route Parameters, Route Groups และ Middleware

## การกำหนด Route พื้นฐาน

```php
use Newla\Core\Routing\RouteFacade as Route;

// Route แบบ Closure
Route::get('/', function () {
    return 'ยินดีต้อนรับสู่ NEWLA Framework!';
});

// Route เชื่อมโยงกับ Controller
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'patch']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
```

## Route Parameters

สามารถรับค่าตัวแปรจาก URL ได้อย่างง่ายดาย:

```php
Route::get('/products/{id}', function (Request $request, int $id) {
    return json(['product_id' => $id]);
});

// รับหลายพารามิเตอร์
Route::get('/categories/{category}/items/{id}', function (Request $request, string $category, int $id) {
    return json([
        'category' => $category,
        'item_id' => $id
    ]);
});
```

## Route Groups และ Prefixes

จัดกลุ่ม Route ตามหมวดหมู่หรือกำหนด Middleware ร่วมกัน:

```php
// กำหนด Prefix
Route::prefix('/admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/reports', [AdminController::class, 'reports']);
});

// กำหนด Middleware ร่วมกัน
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
});
```