<?php

use Illuminate\Database\Eloquent\Relations\MorphMany;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Models\Payment;

trait HasTautPayment
{
    public function tautPayments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'source');
    }

    public function pendingPayment()
    {
        return $this->tautPayments()->where('status', PaymentStatusEnum::Pending->value)->first();
    }
}
