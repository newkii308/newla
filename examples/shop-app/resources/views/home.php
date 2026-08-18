<?php $this->layout('layouts/app'); ?>

<div class="card">
    <h1>Welcome to <?= $this->e($appName) ?>!</h1>
    <p>Your new native PHP application is ready. Built with speed, simplicity, and security by default.</p>
    
    <div class="code">
        // Start building your routes in routes/web.php<br>
        Route::get('/hello', function () {<br>
        &nbsp;&nbsp;&nbsp;&nbsp;return json(['message' => 'Hello World!']);<br>
        });
    </div>

    <p style="font-size: 0.95rem; color: #64748b;">
        Current Server Time: <?= $this->e($time) ?>
    </p>
</div>
