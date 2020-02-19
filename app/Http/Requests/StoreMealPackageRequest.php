<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMealPackageRequest extends FormRequest
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
            'price' => 'required|numeric|between:0.01,6000', // todo: update price limits
            'category_ids' => 'required',
            // 'meals' => 'required|array', Removing this to allow meal package variations to contain the meals without a requirement for the base package to have any.
            'meals.*.id' => 'required|numeric|gt:0',
            'meals.*.quantity' => 'required|numeric|gt:0'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Please add a title for the meal package.',
            'category_ids.required' =>
                'Please select at least one category to show the meal package on your menu.',
            'price.*' => 'Please add the price of the meal package.'
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
