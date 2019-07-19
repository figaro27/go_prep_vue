<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMealPackageRequest extends FormRequest
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
                'price' => 'required|numeric|between:0.01,2000', // todo: update price limits
                'default_size_title' => '',
                'sizes.*.title' => 'required',
                'sizes.*.price' => 'required|gte:0.1|lte:2000',
                'sizes.*.meals' => 'array',
                'components.*.title' => 'required',
                'components.*.options.*.title' =>
                    'required_if:components.*.options.*.selectable,0',
                'components.*.options.*.price' => 'required|gte:0|lte:1000',
                'components.*.options.*.selectable' => 'filled',
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
