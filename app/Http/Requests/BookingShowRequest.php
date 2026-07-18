<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingShowRequest extends FormRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required',
            'phone' => 'required'
        ];
    }
}
