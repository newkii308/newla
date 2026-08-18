<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use Newla\Api\ApiResponse;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Support\Str;
use Newla\Logger\Logger;
use Newla\Validator\Validator;

class ProductController
{
    public function index(Request $request): Response
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);

        $paginated = Product::where('is_active', 1)->paginate($perPage, $page);

        if ($request->expectsJson()) {
            return ApiResponse::paginate($paginated, 'Products retrieved successfully');
        }

        return view('products.index', [
            'products' => $paginated['data'],
            'total' => $paginated['total'],
            'page' => $page,
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $product = Product::find($id);
        if (!$product) {
            return ApiResponse::notFound("Product with ID [{$id}] not found");
        }

        if ($request->expectsJson()) {
            return ApiResponse::success($product->toArray(), 'Product details');
        }

        return view('products.show', ['product' => $product]);
    }

    public function store(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0',
            'description' => 'string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationFailed($validator->errors()->all());
        }

        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        $data['is_active'] = 1;

        $product = Product::create($data);

        Logger::info("New product created: {$product->name} (ID: {$product->id})");

        return ApiResponse::created($product->toArray(), 'Product created successfully');
    }
}