<?php

namespace App\Services\Content;

use App\Http\Resources\MagicProjectResource;
use App\Http\Resources\SiteContentResource;
use App\Models\SiteContent;
use App\Models\User;
use App\Utils\Helpers\ResponseHelpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Psy\Util\Str;

class SiteContentService
{

    public function fetchSiteContentByType($contentRequest)
    {
        $type = trim($contentRequest['type']);
        try {
            $content = SiteContent::where('type',$type)->first();
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new SiteContentResource($content),
                'Site content fetched successfully',
                200
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error fetching site content',
                500
            );
        }
    }

    /**
     * @param $siteContentRequest
     * @return JsonResponse
     */
    public function addSiteContent($siteContentRequest): JsonResponse
    {
        try {
            $content = SiteContent::create([
                'content'=> $siteContentRequest['content'],
                'type'=> trim($siteContentRequest['type'])//privacy, terms
            ]);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new SiteContentResource($content),
                'Site content created successfully',
                200
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error creating site content',
                500
            );
        }
    }
}
