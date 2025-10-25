<?php

namespace TautId\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Traits\HasTransitionStatusTrait;
use TautId\Payment\Transitions;

class Payment extends Model
{
    use HasTransitionStatusTrait, SoftDeletes;

    public $stateConfigs = [
        PaymentStatusEnum::Pending->value => Transitions\ToPending::class,
        PaymentStatusEnum::Due->value => Transitions\ToDue::class,
        PaymentStatusEnum::Canceled->value => Transitions\ToCanceled::class,
        PaymentStatusEnum::Completed->value => Transitions\ToCompleted::class,
        PaymentStatusEnum::Failed->value => Transitions\ToFailed::class
    ];

    protected $table = 'taut_payments';

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta' => 'array',
        'payload' => 'array',
        'response' => 'array',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }
}
