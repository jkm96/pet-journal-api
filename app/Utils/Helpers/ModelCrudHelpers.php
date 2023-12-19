<?php

namespace App\Utils\Helpers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

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

    /**
     * @param $sourceUrl
     * @return array
     */
    public static function getImageBuffer($sourceUrl): array
    {
        $filePath = parse_url($sourceUrl, PHP_URL_PATH);
        $fileName = pathinfo($filePath, PATHINFO_BASENAME);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!empty($fileName) && !empty($extension)) {
            $publicPath = public_path('images/journal_uploads/' . $fileName);

            // Check if the file exists before attempting to read it
            if (file_exists($publicPath)) {
                $imageContents = file_get_contents($publicPath);

                if ($imageContents !== false) {
                    return array($extension, base64_encode($imageContents));
                }
            }
        }

        // Return default values if something goes wrong
        return array('png', base64_encode(''));
    }

    /**
     * @param $sourceUrl
     * @return void
     */
    public static function deleteImageFromStorage($sourceUrl): void
    {
        // Extract the file path from the URL
        $filePath = public_path(parse_url($sourceUrl, PHP_URL_PATH));

        // Check if the file exists before attempting to delete it
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
