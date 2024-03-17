<?php

namespace App\Http\Controllers\MagicStudio;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\FetchMagicProjectsRequest;
use App\Http\Requests\SavePdfRequest;
use App\Services\MagicStudio\MagicStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MagicStudioController extends Controller
{
    private MagicStudioService $_magicStudioService;

    /**
     * @param MagicStudioService $magicStudioService
     */
    public function __construct(MagicStudioService $magicStudioService)
    {
        $this->_magicStudioService = $magicStudioService;
    }

    /**
     * @return JsonResponse
     */
    public function getProjects(FetchMagicProjectsRequest $projectsRequest)
    {
        return $this->_magicStudioService->getAllProjects($projectsRequest);
    }

    /**
     * @param $projectSlug
     * @return JsonResponse
     */
    public function getProjectById($projectSlug)
    {
        return $this->_magicStudioService->getProjectWithEntries($projectSlug);
    }

    /**
     * @param SavePdfRequest $savePdfRequest
     * @return JsonResponse
     */
    public function saveProjectPdf(SavePdfRequest $savePdfRequest)
    {
        Log::info($savePdfRequest);
        return $this->_magicStudioService->updateProjectContent($savePdfRequest);
    }

    /**
     * @param CreateProjectRequest $createProjectRequest
     * @return JsonResponse
     */
    public function createMagicProject(CreateProjectRequest $createProjectRequest): JsonResponse
    {
        return $this->_magicStudioService->createProject($createProjectRequest);
    }

    /**
     * @param $projectId
     * @return JsonResponse
     */
    public function deleteProject($projectId): JsonResponse
    {
        return $this->_magicStudioService->removeMagicProject($projectId);
    }
}
