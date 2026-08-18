<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($appName ?? 'NEWLA') ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0b0f19; color: #e2e8f0; line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #111827; border-bottom: 1px solid #1f2937; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.25rem; font-weight: bold; color: #38bdf8; text-decoration: none; }
        .container { max-width: 900px; margin: 3rem auto; padding: 0 1.5rem; flex: 1; }
        .card { background: #1f2937; border-radius: 12px; padding: 2rem; border: 1px solid #374151; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        h1 { color: #38bdf8; font-size: 2.25rem; margin-bottom: 1rem; }
        p { margin-bottom: 1rem; color: #94a3b8; font-size: 1.1rem; }
        .badge { background: #0284c7; color: #fff; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-block; margin-right: 0.5rem; }
        .code { background: #0f172a; border-radius: 6px; padding: 1rem; color: #38bdf8; font-family: monospace; margin: 1.5rem 0; border: 1px solid #1e293b; }
        .footer { text-align: center; padding: 2rem; color: #64748b; font-size: 0.875rem; border-top: 1px solid #1f2937; }
    </style>
</head>
<body>
    <header class="navbar">
        <a href="/" class="logo">⚡ NEWLA</a>
        <div>
            <span class="badge">PHP <?= PHP_VERSION ?></span>
            <span class="badge" style="background:#10b981;">Production Ready</span>
        </div>
    </header>

    <main class="container">
        <?= $this->yield('content') ?>
    </main>

    <footer class="footer">
        Powered by NEWLA — Native, Fast, Modular PHP Toolkit
    </footer>
</body>
</html>
