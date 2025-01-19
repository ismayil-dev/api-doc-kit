<?php

use Illuminate\Foundation\Http\FormRequest;

class ExampleRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}