<?php

use TautId\Payment\Models\Payment;
use TautId\Payment\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTautPayment
{
    public function tautPayments() : MorphMany
    {
        return $this->morphMany(Payment::class,'source');
    }

    public function pendingPayment()
    {
        return $this->tautPayments()->where('status',PaymentStatusEnum::Pending->value)->first();
    }
}
