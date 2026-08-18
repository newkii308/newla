<?php

declare(strict_types=1);

use Newla\Core\Routing\RouteFacade as Route;
use App\Controllers\HomeController;
use App\Controllers\ProductController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);