# มิดเดิลแวร์ (Middleware)

Middleware ใช้สำหรับตรวจสอบ กรอง หรือดัดแปลง HTTP Request ก่อนที่จะส่งต่อไปยัง Controller

## โครงสร้างของ Middleware

```php
namespace App\Middleware;

use Closure;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Middleware\MiddlewareInterface;
use Newla\Auth\Auth;

class EnsureAdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // ตรวจสอบสิทธิ์ผู้ดูแลระบบ
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'ไม่มีสิทธิ์เข้าถึง'], 403);
            }
            return Response::redirect('/login');
        }

        return $next($request);
    }
}
```