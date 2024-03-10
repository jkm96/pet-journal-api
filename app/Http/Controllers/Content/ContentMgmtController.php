<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSiteContentRequest;
use App\Http\Requests\CustomerFeedbackRequest;
use App\Http\Requests\FetchSiteContentRequest;
use App\Services\Content\SiteContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentMgmtController extends Controller
{
    private SiteContentService $_contentService;

    public function __construct(SiteContentService $contentService)
    {
        $this->_contentService = $contentService;
    }

    /**
     * @param FetchSiteContentRequest $contentRequest
     * @return JsonResponse
     */
    public function getSiteContent(FetchSiteContentRequest $contentRequest): JsonResponse
    {
        return $this->_contentService->fetchSiteContentByType($contentRequest);
    }

    /**
     * @param $contentId
     * @return JsonResponse
     */
    public function getSiteContentById($contentId): JsonResponse
    {
        return $this->_contentService->fetchSiteContentById($contentId);
    }

    /**
     * @param CreateSiteContentRequest $siteContentRequest
     * @return JsonResponse
     */
    public function createContent(CreateSiteContentRequest $siteContentRequest): JsonResponse
    {
        return $this->_contentService->addSiteContent($siteContentRequest);
    }

    /**
     * @param CreateSiteContentRequest $siteContentRequest
     * @param $contentId
     * @return JsonResponse
     */
    public function updateSiteContent(CreateSiteContentRequest $siteContentRequest,$contentId): JsonResponse
    {
        return $this->_contentService->editSiteContent($siteContentRequest,$contentId);
    }

    /**
     * @param CustomerFeedbackRequest $feedbackRequest
     * @return JsonResponse
     */
    public function saveCustomerFeedback(CustomerFeedbackRequest $feedbackRequest): JsonResponse
    {
        return $this->_contentService->addCustomerFeedback($feedbackRequest);
    }
}
