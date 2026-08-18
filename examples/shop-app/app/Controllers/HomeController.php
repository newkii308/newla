<?php

declare(strict_types=1);

namespace App\Controllers;

use Newla\Core\Http\Request;
use Newla\Core\Http\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        return view('home', [
            'appName' => config('app.name', 'NEWLA Application'),
            'phpVersion' => PHP_VERSION,
            'time' => date('Y-m-d H:i:s'),
        ]);
    }
}
