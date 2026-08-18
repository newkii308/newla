<?php

declare(strict_types=1);

namespace App\Models;

use Newla\Core\Database\Model;

class Product extends Model
{
    protected string $table = 'products';
    protected array $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
        'created_at',
        'updated_at'
    ];
}