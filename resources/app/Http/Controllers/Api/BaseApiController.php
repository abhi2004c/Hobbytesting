<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseApiController extends Controller
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $payload = [
            'status'  => 'success',
            'message' => $message,
            'data'    => $data instanceof JsonResource || $data instanceof ResourceCollection
                ? $data->resolve()
                : $data,
        ];

        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function errorResponse(
        string $message,
        int $status = 400,
        array $errors = [],
    ): JsonResponse {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => (object) $errors,
        ], $status);
    }

    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'OK',
        ?callable $transform = null,
    ): JsonResponse {
        $items = $paginator->getCollection();

        if ($transform) {
            $items = $items->map($transform);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $items->values(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ]);
    }
}