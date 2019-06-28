<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMealRequest extends FormRequest
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
        if ($this->request->get('validate_all', false)) {
            return [
                'title' => 'required',
                'price' => 'required|numeric|between:0.01,999.99', // todo: update price limits
                'category_ids' => 'required',
                'default_size_title' => '',
                'sizes.*.title' => 'required',
                'sizes.*.price' => 'required|gte:0.1|lte:1000',
                'sizes.*.multiplier' => 'required|gte:0.1|lte:20',
                'components.*.title' => 'required',
                'components.*.options.*.title' => 'required',
                'components.*.options.*.price' => 'required|gte:0|lte:1000',
                'addons.*.price' => 'required|gte:0|lte:1000',
                'addons.*.title' => 'required'
            ];
        } else {
            return [
                'title' => 'filled',
                'price' => 'filled',
                'category_ids' => 'filled'
            ];
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'sizes.*.title' => [
                'required' => 'The meal size title is required'
            ],
            'sizes.*.price' => [
                'required' => 'The meal size price is required'
            ],
            'sizes.*.multiplier' => [
                'required' => 'The meal size price is required'
            ]
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
