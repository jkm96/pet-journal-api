<?php

namespace App\Http\Resources;

use App\Utils\Helpers\ModelCrudHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class JournalEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attachments = $this->whenLoaded('journalAttachments');
        $journalAttachments = [];
        if ($attachments) {
            foreach ($attachments as $attachment) {
                list($extension, $imageContents) = ModelCrudHelpers::getImageBuffer($attachment->source_url);

                $journalBuffer = [
                    'image_buffer' => $imageContents,
                    'image_type' => $extension,
                ];
                $attachmentBody = [
                    'id'=>$attachment->id,
                    'journal_entry_id'=>$attachment->journal_id,
                    'source_url'=>$attachment->source_url,
                    'type'=>$attachment->type,
                    'buffer'=>$journalBuffer,
                ];
                $journalAttachments[] = $attachmentBody;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'event' => $this->event,
            'slug' => $this->slug,
            'content' => $this->content,
            'location' => $this->location,
            'mood' => $this->mood,
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'journal_attachments' => $journalAttachments,
            'pets' => $this->whenLoaded('pets'),
        ];
    }
}
