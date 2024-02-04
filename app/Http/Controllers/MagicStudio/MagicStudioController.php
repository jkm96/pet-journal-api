<?php

namespace App\Http\Controllers\MagicStudio;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\SavePdfRequest;
use App\Services\MagicStudio\MagicStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function getProjects(){
        return $this->_magicStudioService->getAllProjects();
    }

    /**
     * @param $projectSlug
     * @return JsonResponse
     */
    public function getProjectById($projectSlug){
        return $this->_magicStudioService->getProjectWithEntries($projectSlug);
    }

    /**
     * @param SavePdfRequest $savePdfRequest
     * @return JsonResponse
     */
    public function saveProjectPdf(SavePdfRequest $savePdfRequest){
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
}
