<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSettings extends FormRequest
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
            'cutoff_type' => 'required|in:timed,single_day',
            'mealPlanDiscount' =>
                'required_if:applyMealPlanDiscount,true|nullable|max:99|numeric',
            'deliveryFee' => 'required_if:deliveryFeeType,flat|nullable',
            'mileageBase' =>
                'required_if:deliveryFeeType,mileage|nullable|numeric',
            'mileagePerMile' =>
                'required_if:deliveryFeeType,mileage|nullable|numeric',
            'processingFee' =>
                'required_if:applyProcessingFee,true|nullable|numeric',
            'minimumPrice' => 'required_if:minimumOption,price|numeric',
            'minimumMeals' => 'required_if:minimumOption,meals|numeric',
            'delivery_days' => 'required|min:1',
            'meal_packages' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'cutoff_type.*' => 'Please choose a cutoff type.',
            'mealPlanDiscount.required' =>
                'Please add a subscription discount.',
            'mealPlanDiscount.numeric' =>
                'Please add a correct number for the subscription discount (no characters).',
            'deliveryFee.*' => 'Please add a delivery fee amount.',
            'mileageBase.required' =>
                'Please add a base mileage fee amount for your delivery fee.',
            'mileageBase.numeric' =>
                'Please add a correct number for the base mileage fee amount for your delivery fee (no characters).',
            'mileagePerMile.required' =>
                'Please add a per mile fee amount for your delivery fee.',
            'mileagePerMile.numeric' =>
                'Please add a correct number for the per mile fee amount for your delivery fee (no characters).',
            'processingFee.required' => 'Please add a processing fee amount.',
            'processingFee.numeric' =>
                'Please add a correct number for the processing fee (no characters).',
            'minimumPrice.required' =>
                'Please add a minimum price to allow a customer to order.',
            'minimumPrice.numeric' =>
                'Please type a correct number for the minimum price (no characters).',
            'minimumMeals.required' =>
                'Please add a minimum number of meals for the customer to order.',
            'minimumMeals.numeric' =>
                'Please add a correct number for the minimum number of meals (no characters).',
            'delivery_days.*' =>
                'Please select at least one delivery/pickup day.'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {});
    }
}
