# คอนโทรลเลอร์และการฉีด Dependency (Controllers & DI)

Controller ใน NEWLA รองรับการทำ **Constructor Injection** และ **Method Injection** อัตโนมัติผ่าน IoC Container

## ตัวอย่าง Controller

```php
namespace App\Controllers;

use App\Models\User;
use App\Services\PaymentService;
use Newla\Api\ApiResponse;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;

class UserController
{
    protected PaymentService $paymentService;

    // ฉีด Dependency เข้าทาง Constructor อัตโนมัติ
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // ฉีด Request และ Route Parameter เข้าทาง Method อัตโนมัติ
    public function show(Request $request, int $id): Response
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::notFound("ไม่พบผู้ใช้งานรหัส {$id}");
        }

        return ApiResponse::success($user->toArray());
    }
}
```