# คู่มือเริ่มต้นใช้งานด่วน (Quickstart Guide)

เรียนรู้วิธีสร้าง REST API และจัดการข้อมูลแบบ CRUD ด้วย NEWLA ภายใน 5 นาที

## 1. สร้างโปรเจกต์ใหม่

```bash
newla create blog-api
cd blog-api
```

## 2. สร้าง Model, Migration และ Controller

```bash
# สร้าง Model สำหรับบทความ
newla make:model Post

# สร้างไฟล์ Migration โครงสร้างตาราง
newla make:migration create_posts_table

# สร้าง Controller
newla make:controller PostController
```

## 3. กำหนดโครงสร้างตารางฐานข้อมูล

เปิดไฟล์ `database/migrations/*_create_posts_table.php` แล้วระบุฟิลด์ข้อมูล:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->boolean('is_published')->default(false);
    $table->timestamps();
});
```

สั่งรัน Migration เพื่อสร้างตารางจริง:

```bash
newla migrate
```

## 4. เขียนโค้ดใน Controller

เปิดไฟล์ `app/Controllers/PostController.php`:

```php
namespace App\Controllers;

use App\Models\Post;
use Newla\Api\ApiResponse;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Support\Str;
use Newla\Validator\Validator;

class PostController
{
    // ดึงรายการบทความ
    public function index(Request $request): Response
    {
        $posts = Post::where('is_published', 1)->get();
        return ApiResponse::success($posts);
    }

    // บันทึกบทความใหม่
    public function store(Request $request): Response
    {
        // ตรวจสอบความถูกต้องของข้อมูล
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:200',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationFailed($validator->errors()->all());
        }

        $data = $validator->validated();
        $data['slug'] = Str::slug($data['title']);
        $data['is_published'] = 1;

        $post = Post::create($data);

        return ApiResponse::created($post->toArray(), 'สร้างบทความสำเร็จ');
    }
}
```

## 5. กำหนด Route สำหรับ API

เปิดไฟล์ `routes/api.php`:

```php
use Newla\Core\Routing\RouteFacade as Route;
use App\Controllers\PostController;

Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
```

## 6. รันเซิร์ฟเวอร์และทดสอบ

```bash
newla dev
```

ทดสอบยิง API ด้วย curl:

```bash
# ดึงรายการบทความ
curl http://127.0.0.1:8000/api/posts

# สร้างบทความใหม่
curl -X POST http://127.0.0.1:8000/api/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"เริ่มต้นใช้งาน NEWLA","content":"บทความแรกบน NEWLA Framework"}'
```