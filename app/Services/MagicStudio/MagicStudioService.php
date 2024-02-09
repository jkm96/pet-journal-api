<?php

namespace App\Services\MagicStudio;

use App\Http\Resources\JournalEntryResource;
use App\Http\Resources\MagicProjectResource;
use App\Models\JournalEntry;
use App\Models\MagicStudioProject;
use App\Models\MagicStudioProjectEntry;
use App\Models\User;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MagicStudioService
{
    use DateFilterTrait;

    /**
     * @return JsonResponse
     */
    public function getAllProjects()
    {
        try {
            $projects = MagicStudioProject::all();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                MagicProjectResource::collection($projects),
                'Projects fetched successfully',
                200
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error fetching projects',
                500
            );
        }
    }


    /**
     * @param $createRequest
     * @return JsonResponse
     */
    public function createProject($createRequest)
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $query = $user->journalEntries()
                ->with(['journalAttachments'])
                ->orderBy('created_at', 'asc');

            $periodFrom = $createRequest['period_from'] == null ? date('Y-m-01') : $createRequest['period_from'];
            $periodTo = $createRequest['period_to'] == null ? date('Y-m-t') : $createRequest['period_to'];

            $this->applyDateFilters($query, $periodFrom, $periodTo);
            $journalEntryIds = $query->pluck('id')->toArray();

            DB::beginTransaction();

            try {
                $project = MagicStudioProject::create([
                    'user_id' => $user->id,
                    'title' => $createRequest['title'],
                    'content' => $createRequest['content'],
                    'pdf_content' => $createRequest['content'],
                    'period_from' => Carbon::parse($periodFrom),
                    'period_to' => Carbon::parse($periodTo),
                ]);

                MagicStudioProjectEntry::create([
                    'magic_studio_project_id' => $project->id,
                    'journal_entry_ids' => implode(',', $journalEntryIds),
                ]);

                DB::commit();

                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['project_id' => $project->id],
                    'Project created successfully',
                    200
                );
            } catch (Exception $e) {
                DB::rollBack();
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => $e->getMessage()],
                    'Error creating project',
                    500
                );
            }
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        }
    }

    /**
     * @param $projectSlug
     * @return JsonResponse
     */
    public function getProjectWithEntries($projectSlug)
    {
        try {
            $project = MagicStudioProject::where('slug',$projectSlug)->firstOrFail();

            $projectEntry = MagicStudioProjectEntry::where('magic_studio_project_id', $project->id)->first();

            if (!$projectEntry) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Project entry not found for the given project ID'],
                    'Error fetching project',
                    404
                );
            }

            $journalEntryIds = explode(',', $projectEntry->journal_entry_ids);
            $journalEntries = JournalEntry::whereIn('id', $journalEntryIds)
                ->with(['pets','journalAttachments'])
                ->get();

            $result = [
                'project' => new MagicProjectResource($project),
                'project_entries' => JournalEntryResource::collection($journalEntries),
            ];

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $result,
                'Project and associated entries fetched successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error fetching project',
                500
            );
        }
    }

    /**
     * @param $savePdfRequest
     * @return JsonResponse
     */
    public function updateProjectContent($savePdfRequest)
    {
        try {
            $project = MagicStudioProject::findOrFail($savePdfRequest['project_id']);
            $project->content = $savePdfRequest['pdf_content'];
            $project->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['pdf_content' => $project->content],
                'Project pdf saved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error saving project pdf',
                500
            );
        }
    }

}
