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
                'price' => 'required|numeric|between:0.01,6000', // todo: update price limits
                'category_ids' => 'required',
                'default_size_title' => '',
                'sizes.*.title' => 'required',
                'sizes.*.price' => 'required|gte:0.1|lte:6000',
                'sizes.*.meals' => 'array',
                'components.*.title' => 'required',
                'components.*.options.*.title' =>
                    'required_if:components.*.options.*.preset,0',
                'components.*.options.*.price' => 'required|gte:0|lte:6000',
                'components.*.options.*.selectable' => 'filled',
                'addons.*.price' => 'required|gte:0|lte:6000',
                'addons.*.title' => 'required'
            ];
        } else {
            return [
                    // 'title' => 'filled',
                    // 'price' => 'filled',
                    // 'category_ids' => 'filled'
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
            'title.required' => 'Please add a title for the meal package.',
            'price.*' => 'Please add the price of the meal package.',
            'category_ids.required' =>
                'Please select at least one category to show the meal package on your menu.',
            'sizes.*.title.required' =>
                'Please add a title for the size variation.',
            'sizes.*.price.*' => 'Please add a price for the size variation.',
            'sizes.*.multiplier.required' =>
                'Please add a multiplier for the size variation.',
            'components.*.title.required' =>
                'Please add a title for the component variation.',
            'components.*.options.*.title.*' =>
                'Please add a title for the component option variation.',
            'components.*.options.*.price.*' =>
                'Please add a price for the component option variation.',
            'addons.*.title.required' =>
                'Please add a title for the addon variation.',
            'addons.*.price.*' => 'Please add a price for the addon variation.'
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
