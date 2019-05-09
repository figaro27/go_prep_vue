<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'price' => 'required|numeric|between:0.01,999.99', // todo: update price limits
            'category_ids' => 'required',
            'default_size_title' => '',
            'sizes.*.title' => 'required|distinct',
            'sizes.*.price' => 'required|gte:0.1|lte:1000',
            'sizes.*.multiplier' => 'required|gte:0.1|lte:20'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {});
    }
}
