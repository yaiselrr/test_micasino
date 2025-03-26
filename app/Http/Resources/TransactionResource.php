<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction_id' => $this->transaction_id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency->name,
            'payment_system' => $this->payment_system,
            'status' => $this->status
        ];
    }
}
