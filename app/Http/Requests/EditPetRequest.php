<?php

namespace App\Http\Requests;

use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditPetRequest extends FormRequest
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
            'name' => 'required|string|min:2',
            'nickname' => 'nullable|string|min:2',
            'species' => 'required|string|min:2',
            'breed' => 'nullable|string|min:2',
            'description' => 'required|string|min:6',
            'date_of_birth' => 'nullable',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseHelpers::ConvertToJsonResponseWrapper(
            $validator->errors(),
            "Pet profile update failed due to validation errors",
            422
        ));
    }
}
