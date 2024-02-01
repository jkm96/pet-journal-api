<?php

namespace App\Http\Controllers\MagicStudio;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProjectRequest;
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
     * @param CreateProjectRequest $createProjectRequest
     * @return JsonResponse
     */
    public function createMagicProject(CreateProjectRequest $createProjectRequest): JsonResponse
    {
        return $this->_magicStudioService->createProject($createProjectRequest);
    }
}
