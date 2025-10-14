<?php

namespace TautId\Payment\Models;

use TautId\Payment\Transitions;
use Illuminate\Database\Eloquent\Model;
use TautId\Payment\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TautId\Payment\Traits\HasTransitionStatusTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasTransitionStatusTrait, SoftDeletes;

    public $stateConfigs = [
        PaymentStatusEnum::Pending->value => Transitions\ToPending::class,
        PaymentStatusEnum::Due->value => Transitions\ToDue::class,
        PaymentStatusEnum::Canceled->value => Transitions\ToCanceled::class,
        PaymentStatusEnum::Completed->value => Transitions\ToCompleted::class
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
        return $this->belongsTo(PaymentMethod::class,'method_id');
    }
}
