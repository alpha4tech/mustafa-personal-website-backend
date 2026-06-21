<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicesRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
        'title_ar'        => 'required|string|max:255',
        'title_en'        => 'required|string|max:255',

        'desc_service_ar' => 'required|string',
        'desc_service_en' => 'required|string',

        'list_desc_ar'    => 'nullable|array',
        'list_desc_en'    => 'nullable|array',

        'icon'            => 'nullable|string|max:255',
        'sort_order'      => 'nullable|integer',
        'is_active'       => 'nullable|boolean',
    ];
    }
     public function messages(): array
        {
            return [
                'title_ar.required' => 'عنوان الخدمة بالعربية مطلوب',
                'title_en.required' => 'Service title in English is required',
            ];
        }
}
