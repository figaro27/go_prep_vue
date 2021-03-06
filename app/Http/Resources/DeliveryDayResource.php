<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\DeliveryDates;
class DeliveryDayResource extends JsonResource
{
    use DeliveryDates;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function toArray($request)
    {
        // $day_friendly = $this->getDeliveryDateMultipleDelivery($this->day);
        return [
            'active' => $this->active,
            'applyFee' => $this->applyFee,
            'created_at' => $this->created_at,
            'cutoff_days' => $this->cutoff_days,
            'cutoff_hours' => $this->cutoff_hours,
            'cutoff_type' => $this->cutoff_type,
            'day_friendly' => $this->day_friendly,
            'day_long' => $this->day_long,
            'day_short' => $this->day_short,
            'day' => $this->day,
            'fee' => $this->fee,
            'feeType' => $this->feeType,
            'id' => $this->id,
            'instructions' => $this->instructions,
            'mileageBase' => $this->mileageBase,
            'mileagePerMile' => $this->mileagePerMile,
            'store_id' => $this->store_id,
            'type' => $this->type,
            'updated_at' => $this->updated_at,
            'has_items' => $this->has_items,
            'minimum' => $this->minimum
        ];
    }
}
