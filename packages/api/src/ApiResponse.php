<?php

declare(strict_types=1);

namespace Newla\Api;

use Newla\Core\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = [], string $message = 'Success', int $status = 200, array $headers = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return new JsonResponse($payload, $status, $headers);
    }

    public static function created(mixed $data = [], string $message = 'Resource created successfully', array $headers = []): JsonResponse
    {
        return static::success($data, $message, 201, $headers);
    }

    public static function noContent(array $headers = []): JsonResponse
    {
        return new JsonResponse(null, 204, $headers);
    }

    public static function error(string $message, string $code = 'ERROR', int $status = 400, array $details = [], array $headers = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ];

        if (!empty($details)) {
            $payload['error']['details'] = $details;
        }

        return new JsonResponse($payload, $status, $headers);
    }

    public static function unauthorized(string $message = 'Unauthorized', string $code = 'UNAUTHORIZED'): JsonResponse
    {
        return static::error($message, $code, 401);
    }

    public static function forbidden(string $message = 'Forbidden', string $code = 'FORBIDDEN'): JsonResponse
    {
        return static::error($message, $code, 403);
    }

    public static function notFound(string $message = 'Resource not found', string $code = 'NOT_FOUND'): JsonResponse
    {
        return static::error($message, $code, 404);
    }

    public static function validationFailed(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return static::error($message, 'VALIDATION_FAILED', 422, $errors);
    }

    public static function paginate(array $paginatedData, string $message = 'Success'): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $paginatedData['data'] ?? [],
            'pagination' => [
                'total' => $paginatedData['total'] ?? 0,
                'per_page' => $paginatedData['per_page'] ?? 15,
                'current_page' => $paginatedData['current_page'] ?? 1,
                'last_page' => $paginatedData['last_page'] ?? 1,
                'from' => $paginatedData['from'] ?? 0,
                'to' => $paginatedData['to'] ?? 0,
            ]
        ];

        return new JsonResponse($payload, 200);
    }
}