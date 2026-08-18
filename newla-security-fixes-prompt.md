

---

## คำสั่งงานทั่วไป (ทำกับทุกข้อ)

1. แก้ไขเฉพาะไฟล์ที่ระบุ ห้ามเปลี่ยน public API signature เดิมโดยไม่จำเป็น (จะกระทบโค้ดที่ใช้ framework นี้อยู่)
2. ทุกจุดที่แก้ ให้เพิ่ม/แก้ unit test ใน `tests/` ให้ครอบคลุม case ที่เป็นช่องโหว่ (เช่น path traversal payload, spoofed header, timing)
3. รักษา `declare(strict_types=1);` และ code style เดิมของไฟล์ (namespace, PHPDoc, การเว้นบรรทัด)
4. หลังแก้เสร็จแต่ละข้อ รัน `php tests/runner.php` (หรือ test runner ที่มีอยู่) แล้วรายงานผลผ่าน/ไม่ผ่าน
5. ห้ามลบ backward compatibility ของ constructor/method ที่ package อื่น (เช่น `auth`, `security`) เรียกใช้อยู่ — ให้ grep หาการเรียกใช้ก่อนแก้เสมอ

---

## 1. [Critical] Path Traversal ใน `LocalStorageDriver`

**ไฟล์:** `packages/storage/src/Driver/LocalStorageDriver.php`

**ปัญหา:** `getFullPath()` แปลง `/` และ `\` เป็น `DIRECTORY_SEPARATOR` แต่ไม่ตรวจสอบ `..` เลย ทำให้ path เช่น `../../../../etc/passwd` หลุดออกนอก `$this->root` ได้ทุก method ที่เรียก `getFullPath()` (put, get, delete, exists, size, lastModified, mimeType, files, makeDirectory, deleteDirectory)

**สิ่งที่ต้องทำ:**
- แก้ `getFullPath()` ให้ resolve path แล้วตรวจสอบว่าผลลัพธ์ยังอยู่ภายใต้ `$this->root` เท่านั้น ถ้าไม่ใช่ให้ throw exception (เช่น `\InvalidArgumentException` หรือสร้าง `Newla\Storage\Exceptions\PathTraversalException` ใหม่)
- ต้องรองรับกรณีไฟล์/โฟลเดอร์ที่ยังไม่ถูกสร้าง (`realpath()` จะ return `false` ถ้าไฟล์ไม่มีอยู่จริง) — ให้ normalize แบบ manual (ตัด segment `.` และ `..` ออกจาก path string) แทนการพึ่ง `realpath()` อย่างเดียว
- เพิ่มการเรียก `Newla\Security\Sanitizer\InputSanitizer::safeFilename()` ที่จุดที่รับชื่อไฟล์จาก user โดยตรง (ตรวจดูว่ามี controller/example ใน `examples/shop-app` ที่รับชื่อไฟล์อัปโหลดแล้วส่งตรงเข้า Storage หรือไม่ ถ้ามีให้แก้ตรงนั้นด้วย)
- เขียน unit test ยิง payload: `../secret.txt`, `..\\..\\windows\\win.ini`, `a/../../b`, `....//....//etc/passwd` ต้องถูก reject ทั้งหมด ส่วน path ปกติ เช่น `products/img.jpg`, `2026/08/slip.png` ต้องทำงานได้เหมือนเดิม

**ตัวอย่างแนวทาง (ปรับ logic ให้เข้ากับโค้ดจริง):**
```php
protected function normalizeRelativePath(string $path): string
{
    $path = str_replace(['\\'], '/', $path);
    $parts = [];
    foreach (explode('/', $path) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }
        if ($segment === '..') {
            throw new \InvalidArgumentException("Invalid path: path traversal detected in [{$path}]");
        }
        $parts[] = $segment;
    }
    return implode(DIRECTORY_SEPARATOR, $parts);
}

public function getFullPath(string $path): string
{
    return $this->root . DIRECTORY_SEPARATOR . $this->normalizeRelativePath($path);
}
```

---

## 2. [High] IP Spoofing ผ่าน `X-Forwarded-For` ใน `Request::ip()`

**ไฟล์:** `packages/core/src/Http/Request.php`

**ปัญหา:** `ip()` เชื่อ header `x-forwarded-for` แบบไม่มีเงื่อนไข ผู้ใช้ปลอม header นี้ตรง ๆ ได้ (ถ้า client เข้าถึง origin server ได้โดยตรง หรือ proxy ไม่ strip header เดิมทิ้งก่อน) ทำให้ `RateLimitMiddleware` (ซึ่งใช้ `ip()` เป็นส่วนหนึ่งของ signature) ถูก bypass ได้ง่ายด้วยการสุ่มค่า header ทุก request

**สิ่งที่ต้องทำ:**
- เพิ่ม concept "trusted proxies" ให้ `Request` — เช่น constructor property หรือ static config `Request::setTrustedProxies(array $cidrs)`
- `ip()` จะอ่านค่าจาก `x-forwarded-for` **ก็ต่อเมื่อ** `REMOTE_ADDR` อยู่ใน trusted proxies list เท่านั้น (ปกติคือ Cloudflare IP ranges) ไม่งั้นให้ใช้ `REMOTE_ADDR` เป็นหลักเสมอ
- ถ้าใช้ `x-forwarded-for` ให้เอาค่า "แรกสุด" ใน chain (client IP จริง อยู่ซ้ายสุด) และ validate ว่าเป็น IP format ที่ถูกต้องด้วย `filter_var($ip, FILTER_VALIDATE_IP)`
- แนะนำเพิ่ม config default สำหรับ Cloudflare IP ranges (ดึงจาก https://www.cloudflare.com/ips/ หรือให้ผู้ใช้ config เอง) — เขียนเป็น constant/array แยกไฟล์ config ไม่ hardcode ปนกับ logic
- เพิ่ม unit test: request จาก `REMOTE_ADDR` ที่ไม่ใช่ trusted proxy พร้อม header `x-forwarded-for` ปลอม → `ip()` ต้องคืนค่า `REMOTE_ADDR` ไม่ใช่ค่าจาก header

---

## 3. [Medium] User Enumeration ผ่าน Timing ใน `SessionGuard::attempt()`

**ไฟล์:** `packages/auth/src/SessionGuard.php`

**ปัญหา:** ถ้าหา user ไม่เจอ จะ `return false` ทันทีโดยไม่เรียก `password_verify()` เลย แต่ถ้า user เจอ (แม้ password ผิด) จะเสียเวลา hash-verify เพิ่ม ทำให้แยกแยะได้จาก response time ว่า email/username มีอยู่ในระบบหรือไม่

**สิ่งที่ต้องทำ:**
- แก้ `attempt()` ให้เรียก `Security::verifyPassword()` เสมอ ไม่ว่าจะเจอ user หรือไม่ โดยใช้ dummy hash คงที่เมื่อไม่เจอ user เพื่อให้ timing ใกล้เคียงกัน
- dummy hash ให้ generate จาก `password_hash()` จริง (ไม่ hardcode ค่าคงที่ตรง ๆ ในซอร์ส เพื่อไม่ให้กลายเป็น known-hash ที่ถูกไล่ crack ได้ง่าย) เช่น cache ไว้เป็น static property ที่ generate ครั้งแรกที่เรียกใช้
- เพิ่ม unit/feature test เปรียบเทียบเวลา response ระหว่าง "user ไม่มีอยู่" กับ "user มีอยู่แต่ password ผิด" ต้องต่างกันไม่เกิน threshold ที่ยอมรับได้ (เช่น < 20ms หรือค่าที่ทีมกำหนด)

---

## 4. [Medium] Column/Direction ไม่ผ่านการ Validate ใน `QueryBuilder`

**ไฟล์:** `packages/core/src/Database/QueryBuilder.php`

**ปัญหา:**
- `orderBy(string $column, string $direction)` ต่อ `$direction` เข้า SQL ตรง ๆ ผ่าน `strtoupper($direction)` โดยไม่ validate ว่าเป็น `ASC`/`DESC` เท่านั้น
- `select()`, `table()`, `join()` รับชื่อ column/table เป็น string แล้วต่อเข้า SQL ตรง ๆ โดยไม่ผ่าน placeholder (เป็นข้อจำกัดปกติของ query builder แต่ต้อง "บังคับ" ผู้ใช้ผ่าน API ให้ปลอดภัยที่สุดเท่าที่ทำได้)
- `whereIn()` เมื่อ `$values` เป็น array ว่าง จะ compile เป็น `IN ()` ซึ่งเป็น SQL invalid syntax

**สิ่งที่ต้องทำ:**
- `orderBy()`: validate `$direction` ต้องเป็น `ASC` หรือ `DESC` เท่านั้น (case-insensitive) ไม่ตรงให้ throw `\InvalidArgumentException`
- `orderBy()`/`select()`/`table()`: เพิ่ม validate column/table name ด้วย regex ที่อนุญาตเฉพาะ `[a-zA-Z0-9_.` `]` (รองรับ `table.column` และ backtick ถ้า driver เป็น MySQL) — ถ้ามี aggregate function เช่น `COUNT(*)` ให้เพิ่ม whitelist แยกกรณี หรือเปิดช่องทางผ่าน method อื่น (เช่น `selectRaw()`) ที่ระบุชัดเจนว่าเป็นค่าที่ dev ตั้งใจใส่เอง ไม่ใช่จาก user input
- `whereIn()`: ถ้า `empty($values)` ให้ compile เป็นเงื่อนไขที่ไม่ match อะไรเลยแทน (เช่น `1 = 0`) และไม่ throw error
- อัปเดต docblock ของ `select/table/orderBy` ให้ระบุชัดเจนว่า "ห้ามส่งค่าที่มาจาก user input โดยตรงโดยไม่ validate/whitelist ก่อน"
- เพิ่ม test: `orderBy('name', 'ASC; DROP TABLE users')` ต้อง throw, `whereIn('id', [])->get()` ต้องคืน array ว่างโดยไม่ error

---

## 5. [Low] `count()`/`exists()` เปลี่ยน State ของ `QueryBuilder` แบบถาวร

**ไฟล์:** `packages/core/src/Database/QueryBuilder.php`

**ปัญหา:** `count()` เซ็ต `$this->columns` เป็น `["COUNT(...) as aggregate"]` แบบถาวร ถ้ามีการ reuse builder object หลังเรียก `count()`/`exists()` แล้วเรียก `get()` ต่อ จะได้ผลลัพธ์ผิด (ยกเว้น `paginate()` ที่ reset เองอยู่แล้ว)

**สิ่งที่ต้องทำ:**
- แก้ `count()` ให้ backup `$this->columns` เดิมไว้ก่อน แล้ว restore กลับหลังอ่านผลลัพธ์ ไม่ mutate state แบบถาวร
- เพิ่ม test: สร้าง builder → เรียก `count()` → เรียก `get()` ต่อบน builder ตัวเดิม → ผลลัพธ์ต้องเป็น full row ไม่ใช่ aggregate

---

## 6. [Low] View Engine ไม่ Auto-escape

**ไฟล์:** `packages/core/src/View/ViewEngine.php`

**ปัญหา:** `render()`/`include()` ใช้ raw `include` ของ PHP ตรง ๆ ไม่มีการ escape ค่าอัตโนมัติ ต้องพึ่งให้ dev เรียก `$this->e()` เองทุกจุดที่ print ค่าจาก user — เสี่ยง XSS จาก human error

**สิ่งที่ต้องทำ (เลือกแนวทางใดแนวทางหนึ่ง แล้วบันทึกเหตุผลไว้ใน `docs/architecture.md`):**
- **แนวทาง A (แนะนำ, non-breaking):** เพิ่ม global helper function สั้น ๆ เช่น `e($value)` ที่ auto-register ตอน bootstrap แทนการต้องพิมพ์ `$this->e()` เต็ม ๆ ทุกครั้ง เพื่อลดโอกาสลืม พร้อมอัปเดต `docs/security.md` ให้เตือนเรื่องนี้ชัดเจนเป็นหัวข้อแยก
- **แนวทาง B (breaking, ทำเป็น major version ใหม่):** เพิ่ม syntax แบบ `{{ $var }}` ที่ compile เป็น escape-by-default คล้าย Blade แล้ว parse ก่อน include (ต้องเพิ่ม compile step และ cache คอมไพล์ผลลัพธ์เพื่อ performance)
- ไม่ว่าจะเลือกแนวทางไหน ต้องอัปเดต `docs/architecture.md` และตัวอย่างใน `examples/shop-app` ให้สอดคล้องกัน

---

## 7. [Low] `SessionGuard::attempt()` รับ `$remember` แต่ไม่ได้ใช้งานจริง

**ไฟล์:** `packages/auth/src/SessionGuard.php`, `packages/auth/src/AuthenticatableInterface.php`

**ปัญหา:** พารามิเตอร์ `bool $remember` ถูกรับเข้ามาแต่ไม่มี logic ใด ๆ ใช้งาน ทั้งที่ interface มี `getRememberToken()`/`setRememberToken()` รออยู่แล้ว — เป็น incomplete feature

**สิ่งที่ต้องทำ:**
- Implement remember-me cookie: เมื่อ `$remember === true` ให้ generate token ผ่าน `Newla\Security\Token\TokenGenerator::randomBase64()`, เก็บ hash ของ token ลง DB ผ่าน `setRememberToken()`, ตั้ง cookie ที่เป็น `HttpOnly`, `Secure` (เมื่อ HTTPS), `SameSite=Lax`, อายุยาว (เช่น 30 วัน)
- เพิ่ม method `loginViaRememberCookie()` หรือ logic ใน `user()`/constructor ที่เช็ค cookie นี้เมื่อไม่มี session อยู่ แล้ว auto-login พร้อม regenerate token ใหม่ทุกครั้งที่ใช้ (rotate token ป้องกัน replay)
- ถ้ายังไม่พร้อม implement เต็มรูปแบบในรอบนี้ อย่างน้อยให้ throw `\LogicException('remember-me not implemented yet')` เมื่อ `$remember === true` แทนการรับเงียบ ๆ แล้วไม่ทำอะไร เพื่อไม่ให้ dev ที่เรียกใช้เข้าใจผิดว่า feature ทำงานอยู่

---

## ลำดับความสำคัญที่แนะนำให้แก้ก่อน

1. ข้อ 1 (Path Traversal) — กระทบระบบอัปโหลดสลิป/รูปสินค้าโดยตรง
2. ข้อ 2 (IP Spoofing) — กระทบ rate limiting ที่ใช้จริงกับ tpbaba.shop
3. ข้อ 3 (Timing attack)
4. ข้อ 4 (QueryBuilder validation)
5. ข้อ 5–7 (Low priority, ทำเมื่อมีเวลา)

## Checklist ก่อนปิดงาน

- [ ] ข้อ 1: path traversal ถูก block, test ผ่าน
- [ ] ข้อ 2: trusted proxy logic ทำงานถูกต้อง, test ผ่าน
- [ ] ข้อ 3: timing ใกล้เคียงกันระหว่าง user เจอ/ไม่เจอ
- [ ] ข้อ 4: orderBy/whereIn validate ผ่าน, test ผ่าน
- [ ] ข้อ 5: builder state ไม่ leak, test ผ่าน
- [ ] ข้อ 6: ตัดสินใจแนวทาง A หรือ B แล้วอัปเดตเอกสาร
- [ ] ข้อ 7: remember-me implement เต็มรูปแบบ หรือ throw ชัดเจน
- [ ] รัน `php tests/runner.php` ผ่านทั้งหมด
- [ ] อัปเดต `CHANGELOG` (ถ้ามี) หรือ commit message ระบุ "Security fix" ชัดเจนสำหรับข้อ 1–3
