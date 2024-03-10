<?php

namespace App\Http\Requests;

use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateJournalEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:8',
            'event' => 'required|string|min:2',
            'content' => 'required|string|min:30',
            'location' => 'nullable|string|min:2',
            'mood' => 'nullable|string|min:2',
            'tags' => 'nullable|string',
            'pet_ids' =>'required'
        ];
    }

    /**
     * @param Validator $validator
     * @return mixed
     */
    public function failedValidation(Validator $validator): mixed
    {
        throw new HttpResponseException(ResponseHelpers::ConvertToJsonResponseWrapper(
            $validator->errors(),
            "Journal entry creation failed due to validation errors",
            422
        ));
    }
}
