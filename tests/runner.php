<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Newla\Core\Container\Container;
use Newla\Core\Database\Connection;
use Newla\Core\Database\DatabaseManager;
use Newla\Core\Database\Migrator;
use Newla\Core\Database\Model;
use Newla\Core\Database\Schema\Blueprint;
use Newla\Core\Database\Schema\Schema;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Routing\Router;
use Newla\Security\Security;
use Newla\Security\RateLimit\RateLimiter;
use Newla\Security\Csrf\CsrfManager;
use Newla\Validator\Validator;
use Newla\Logger\Logger;
use Newla\Logger\Handler\FileHandler;
use Newla\Storage\Storage;
use Newla\Storage\Driver\LocalStorageDriver;
use Newla\Image\Image;
use Newla\Api\ApiResponse;

echo "\033[1;36m========================================\033[0m\n";
echo "\033[1;36m       NEWLA Comprehensive Test Suite    \033[0m\n";
echo "\033[1;36m========================================\033[0m\n\n";

$passed = 0;
$failed = 0;

$it = function (string $description, Closure $test) use (&$passed, &$failed) {
    try {
        $test();
        echo "\033[32m  ✓ {$description}\033[0m\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "\033[31m  ✗ {$description}\033[0m\n";
        echo "    \033[33m" . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\033[0m\n";
        $failed++;
    }
};

// ----------------------------------------------------
echo "\033[1;34m1. Container & Dependency Injection\033[0m\n";

$it('binds and resolves instances correctly', function () {
    $c = new Container();
    $c->bind('foo', fn() => 'bar');
    assert($c->make('foo') === 'bar');
});

$it('resolves singletons properly', function () {
    $c = new Container();
    $c->singleton('obj', fn() => new \stdClass());
    $o1 = $c->make('obj');
    $o2 = $c->make('obj');
    assert($o1 === $o2);
});

$it('resolves constructor dependencies with autowiring', function () {
    $c = new Container();
    $c->singleton(Container::class, $c);
    $router = $c->make(Router::class);
    assert($router instanceof Router);
});

// ----------------------------------------------------
echo "\n\033[1;34m2. Routing, HTTP & Middleware\033[0m\n";

$it('dispatches GET route with parameters and returns JSON response', function () {
    $c = new Container();
    $router = new Router($c);
    $router->get('/products/{id}', function (Request $req, int $id) {
        return Response::json(['product_id' => $id]);
    });

    $req = Request::create('/products/99', 'GET');
    $resp = $router->dispatch($req);
    assert($resp->getStatusCode() === 200);
    $data = json_decode($resp->getContent(), true);
    assert($data['product_id'] === 99);
});

$it('handles route groups with prefix and middleware', function () {
    $c = new Container();
    $router = new Router($c);

    $router->prefix('/admin')->group(function ($r) {
        $r->get('/dashboard', fn() => Response::json(['page' => 'dashboard']));
    });

    $req = Request::create('/admin/dashboard', 'GET');
    $resp = $router->dispatch($req);
    $data = json_decode($resp->getContent(), true);
    assert($data['page'] === 'dashboard');
});

$it('throws 404 for unknown route and 405 for wrong method', function () {
    $c = new Container();
    $router = new Router($c);
    $router->post('/submit', fn() => 'done');

    try {
        $router->dispatch(Request::create('/unknown', 'GET'));
        assert(false, 'Should throw 404');
    } catch (\Newla\Core\Exceptions\NotFoundException $e) {
        assert($e->getStatusCode() === 404);
    }

    try {
        $router->dispatch(Request::create('/submit', 'GET'));
        assert(false, 'Should throw 405');
    } catch (\Newla\Core\Exceptions\MethodNotAllowedException $e) {
        assert($e->getStatusCode() === 405);
    }
});

// ----------------------------------------------------
echo "\n\033[1;34m3. Database, Schema, Migrator & Models\033[0m\n";

$db = new DatabaseManager(['default' => 'sqlite', 'connections' => ['sqlite' => ['driver' => 'sqlite', 'database' => ':memory:']]]);
$conn = $db->connection();
Container::getInstance()->instance('db', $db);

$it('creates database tables with Schema Blueprint', function () {
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('body')->nullable();
        $table->integer('views')->default(0);
        $table->timestamps();
    });

    assert(Schema::hasTable('articles') === true);
});

$it('performs query builder insert, select, where, update, count, and paginate', function () use ($conn) {
    $conn->table('articles')->insert([
        ['title' => 'Article 1', 'views' => 10, 'created_at' => date('Y-m-d H:i:s')],
        ['title' => 'Article 2', 'views' => 20, 'created_at' => date('Y-m-d H:i:s')],
        ['title' => 'Article 3', 'views' => 30, 'created_at' => date('Y-m-d H:i:s')],
    ]);

    $count = $conn->table('articles')->count();
    assert($count === 3);

    $popular = $conn->table('articles')->where('views', '>=', 20)->get();
    assert(count($popular) === 2);

    $conn->table('articles')->where('title', 'Article 1')->update(['views' => 15]);
    $updated = $conn->table('articles')->where('title', 'Article 1')->first();
    assert($updated['views'] === 15);

    $page = $conn->table('articles')->paginate(2, 1);
    assert(count($page['data']) === 2);
    assert($page['total'] === 3);
    assert($page['last_page'] === 2);
});

$it('operates Model Active Record pattern', function () {
    class Article extends Model {
        protected string $table = 'articles';
        protected array $fillable = ['title', 'body', 'views'];
    }

    $art = Article::create(['title' => 'Model Article', 'views' => 5]);
    assert($art->id > 0);
    assert($art->title === 'Model Article');

    $found = Article::find($art->id);
    assert($found->title === 'Model Article');

    $found->title = 'Updated Article';
    $found->save();

    $reloaded = Article::find($art->id);
    assert($reloaded->title === 'Updated Article');

    $found->delete();
    assert(Article::find($art->id) === null);
});

// ----------------------------------------------------
echo "\n\033[1;34m4. Security Package (@newla/security)\033[0m\n";

$it('hashes and verifies passwords securely', function () {
    $hash = Security::hashPassword('Secret123!');
    assert(strlen($hash) > 20);
    assert(Security::verifyPassword('Secret123!', $hash) === true);
    assert(Security::verifyPassword('WrongPass', $hash) === false);
});

$it('generates CSRF token and validates properly', function () {
    $token = Security::csrfToken();
    assert(strlen($token) === 64);
    assert(Security::verifyCsrf($token) === true);
    assert(Security::verifyCsrf('invalid_token') === false);
});

$it('executes rate limiter tracking and throttling', function () {
    $limiter = new RateLimiter(sys_get_temp_dir() . '/test_ratelimit_' . uniqid());
    $key = 'user_test_1';

    assert($limiter->attempts($key) === 0);
    $limiter->hit($key, 60);
    $limiter->hit($key, 60);
    assert($limiter->attempts($key) === 2);
    assert($limiter->tooManyAttempts($key, 2) === true);
    assert($limiter->tooManyAttempts($key, 5) === false);
    $limiter->clear($key);
    assert($limiter->attempts($key) === 0);
});

$it('sanitizes input and escapes HTML', function () {
    $dirty = "  Hello <script>alert('xss')</script> \0 ";
    $clean = Security::sanitize($dirty);
    assert($clean === "Hello <script>alert('xss')</script>");

    $escaped = Security::escape("<script>alert('xss')</script>");
    assert(!str_contains($escaped, '<script>'));
    assert(str_contains($escaped, '&lt;script&gt;'));
});

// ----------------------------------------------------
echo "\n\033[1;34m5. Validator Package (@newla/validator)\033[0m\n";

$it('validates required, string, email, min, max, integer, in rules', function () {
    $v = Validator::make([
        'username' => 'alice',
        'email' => 'alice@newla.dev',
        'age' => 25,
        'role' => 'admin',
    ], [
        'username' => 'required|string|min:3|max:20',
        'email' => 'required|email',
        'age' => 'required|integer|min:18',
        'role' => 'required|in:admin,editor,user',
    ]);

    assert($v->passes() === true);
    assert($v->fails() === false);
    $valid = $v->validated();
    assert($valid['username'] === 'alice');
});

$it('fails invalid data and provides detailed error messages', function () {
    $v = Validator::make([
        'username' => 'al',
        'email' => 'not-an-email',
        'age' => 15,
        'role' => 'superadmin',
    ], [
        'username' => 'required|string|min:3',
        'email' => 'required|email',
        'age' => 'required|integer|min:18',
        'role' => 'required|in:admin,editor,user',
    ]);

    assert($v->fails() === true);
    assert($v->errors()->has('username') === true);
    assert($v->errors()->has('email') === true);
    assert($v->errors()->has('age') === true);
    assert($v->errors()->has('role') === true);
});

// ----------------------------------------------------
echo "\n\033[1;34m6. Logger Package (@newla/logger)\033[0m\n";

$it('writes formatted log entries to log files', function () {
    $tempLog = sys_get_temp_dir() . '/test_app_' . uniqid() . '.log';
    $channel = new \Newla\Logger\LoggerChannel('test', [
        new FileHandler($tempLog)
    ]);

    $channel->info('User logged in', ['user_id' => 123]);
    assert(file_exists($tempLog));
    $content = file_get_contents($tempLog);
    assert(str_contains($content, 'test.INFO: User logged in'));
    assert(str_contains($content, '"user_id":123'));
    @unlink($tempLog);
});

// ----------------------------------------------------
echo "\n\033[1;34m7. Storage Package (@newla/storage)\033[0m\n";

$it('handles local file storage put, get, exists, size, url, and delete', function () {
    $tempDir = sys_get_temp_dir() . '/newla_storage_test_' . uniqid();
    $storage = new LocalStorageDriver($tempDir, 'http://localhost/uploads');

    assert($storage->put('docs/readme.txt', 'NEWLA Storage Engine') === true);
    assert($storage->exists('docs/readme.txt') === true);
    assert($storage->get('docs/readme.txt') === 'NEWLA Storage Engine');
    assert($storage->size('docs/readme.txt') === 20);
    assert($storage->url('docs/readme.txt') === 'http://localhost/uploads/docs/readme.txt');
    assert($storage->delete('docs/readme.txt') === true);
    assert($storage->exists('docs/readme.txt') === false);
    $storage->deleteDirectory('');
});

// ----------------------------------------------------
echo "\n\033[1;34m8. Image Package (@newla/image)\033[0m\n";

$it('creates, resizes, thumbnails and converts images to WebP', function () {
    $tempDir = sys_get_temp_dir();
    $sourcePng = $tempDir . '/test_src_' . uniqid() . '.png';
    $destJpg = $tempDir . '/test_dest_' . uniqid() . '.jpg';
    $destWebp = $tempDir . '/test_dest_' . uniqid() . '.webp';

    $im = imagecreatetruecolor(200, 100);
    $bg = imagecolorallocate($im, 50, 100, 200);
    imagefill($im, 0, 0, $bg);
    imagepng($im, $sourcePng);

    assert(Image::validate($sourcePng) === true);

    $processor = Image::make($sourcePng);
    assert($processor->getWidth() === 200);
    assert($processor->getHeight() === 100);

    $processor->resize(100, 50)->save($destJpg);
    assert(file_exists($destJpg));

    $processor2 = Image::make($sourcePng);
    $processor2->thumbnail(64, 64)->toWebp($destWebp);
    assert(file_exists($destWebp));

    @unlink($sourcePng);
    @unlink($destJpg);
    @unlink($destWebp);
});

// ----------------------------------------------------
echo "\n\033[1;34m9. API Package (@newla/api)\033[0m\n";

$it('formats standardized API success, error, and paginated responses', function () {
    $resp = ApiResponse::success(['id' => 1, 'name' => 'Item 1'], 'Fetched');
    assert($resp->getStatusCode() === 200);
    $data = json_decode($resp->getContent(), true);
    assert($data['success'] === true);
    assert($data['data']['name'] === 'Item 1');

    $err = ApiResponse::error('Not found', 'NOT_FOUND', 404);
    assert($err->getStatusCode() === 404);
    $errData = json_decode($err->getContent(), true);
    assert($errData['success'] === false);
    assert($errData['error']['code'] === 'NOT_FOUND');
});

// ----------------------------------------------------
echo "\n========================================\n";
echo "Tests Passed: \033[32m{$passed}\033[0m\n";
echo "Tests Failed: \033[" . ($failed > 0 ? "31" : "32") . "m{$failed}\033[0m\n";
echo "========================================\n";

exit($failed > 0 ? 1 : 0);
