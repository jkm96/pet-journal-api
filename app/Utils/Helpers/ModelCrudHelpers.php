<?php

namespace App\Utils\Helpers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ModelCrudHelpers
{
    /**
     * @param ModelNotFoundException|\Exception $e
     * @return JsonResponse
     */
    public static function itemNotFoundError(ModelNotFoundException|\Exception $e): JsonResponse
    {
        $fullyQualifiedName = $e->getModel();
        $className = class_basename($fullyQualifiedName);

        return ResponseHelpers::ConvertToJsonResponseWrapper(
            ['error' => $className . ' not found'],
            $className . ' not found',
            404
        );
    }
}
