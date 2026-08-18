# ฐานข้อมูล, Migration และ Model (Database & ORM)

NEWLA ใช้เทคโนโลยี **PDO แท้** พร้อมระบบ Prepared Statements 100% ปลอดภัยจากการโจมตีแบบ SQL Injection

## ฐานข้อมูลที่รองรับ
- SQLite 3
- MySQL 5.7+ / 8.0+
- MariaDB 10.3+
- PostgreSQL 12+

---

## 1. Query Builder

เรียกใช้งานง่ายผ่าน Facade `DB`:

```php
use Newla\Core\Database\DB;

// ดึงข้อมูลทั้งหมด
$users = DB::table('users')->where('status', 'active')->get();

// ดึงข้อมูลแถวเดียว
$user = DB::table('users')->where('id', 1)->first();

// การเพิ่มข้อมูล (Insert)
DB::table('users')->insert([
    'name' => 'สมชาย ใจดี',
    'email' => 'somchai@example.com',
    'created_at' => date('Y-m-d H:i:s'),
]);

// การอัปเดตข้อมูล (Update)
DB::table('users')->where('id', 1)->update(['status' => 'suspended']);

// การลบข้อมูล (Delete)
DB::table('users')->where('id', 1)->delete();

// การแบ่งหน้า (Pagination)
$page = DB::table('products')->paginate(perPage: 15, page: 1);
```

---

## 2. Database Migrations

สร้างไฟล์ Migration:
```bash
newla make:migration create_products_table
```

กำหนด Schema ด้วย Blueprint:
```php
use Newla\Core\Database\Migration;
use Newla\Core\Database\Schema\Blueprint;
use Newla\Core\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

คำสั่งจัดการ Migration:
```bash
newla migrate           # รัน Migration ที่ค้างอยู่
newla migrate:rollback  # ย้อนกลับ Migration ล่าสุด
newla migrate:fresh     # ลบตารางทั้งหมดแล้วรันใหม่
```

---

## 3. Active Record Model

สร้าง Model:
```bash
newla make:model Product
```

การใช้งาน Model:
```php
namespace App\Models;

use Newla\Core\Database\Model;

class Product extends Model
{
    protected string $table = 'products';
    protected array $fillable = ['name', 'slug', 'price', 'stock', 'is_active'];
}

// ตัวอย่างการใช้งาน
$product = Product::find(1);
$activeProducts = Product::where('is_active', 1)->get();
$newProduct = Product::create([
    'name' => 'คีย์บอร์ดไร้สาย',
    'price' => 1490.00,
    'stock' => 50
]);
```