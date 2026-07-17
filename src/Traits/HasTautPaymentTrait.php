<?php

namespace TautId\Payment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Models\Payment;

trait HasTautPaymentTrait
{
    public function tautPayments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'source');
    }

    public function pendingPayment()
    {
        return $this->tautPayments->where('status', PaymentStatusEnum::Pending->value)->first();
    }
}
