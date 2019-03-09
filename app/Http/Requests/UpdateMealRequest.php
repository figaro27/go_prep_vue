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
        if($this->request->get('validate_all', false)) {
          return [
              'title' => 'required',
              'price' => 'required|numeric|between:0.01,999.99', // todo: update price limits
              'category_ids' => 'required',
          ];
        }
        else {
          return [
              'title' => 'filled',
              'price' => 'filled',
              'category_ids' => 'filled',
          ];
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {

        });
    }
}
