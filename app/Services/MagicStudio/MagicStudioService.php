<?php

namespace App\Services\MagicStudio;

use App\Http\Resources\JournalEntryResource;
use App\Http\Resources\MagicProjectResource;
use App\Models\MagicStudioProject;
use App\Models\User;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class MagicStudioService
{
    use DateFilterTrait;
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
            $journalEntries = $query->get();
            //create the project
            //return the selected entries

            $project = MagicStudioProject::create([
                'user_id' => $user->id,
                'title' => $createRequest['title'],
                'period_from' => Carbon::parse($periodFrom),
                'period_to' => Carbon::parse($periodTo),
            ]);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                [
                    'project' => new MagicProjectResource($project),
                    'project_entries' => JournalEntryResource::collection($journalEntries)
                ],
                'Project created successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error creating project',
                500
            );
        }
    }
}
