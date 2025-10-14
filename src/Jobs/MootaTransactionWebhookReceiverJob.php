<?php

namespace TautId\Payment\Jobs;

use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Models\Payment;
use TautId\Payment\Services\PaymentService;

class MootaTransactionWebhookReceiverJob extends ProcessWebhookJob
{
    public function handle()
    {
        $payload = collect($this->webhookCall['payload']);

        $payment_order_ids = $payload->where('payment_detail.status', 'SUCCESS')
            ->map(fn ($moota) => data_get($moota, 'payment_detail.order_id'))
            ->unique()
            ->toArray();

        Payment::query()
            ->where('status', PaymentStatusEnum::Pending->value)
            ->whereHas('method', fn ($subq) => $subq->where('driver', 'moota-transaction'))
            ->whereIn('trx_id', $payment_order_ids)
            ->get()
            ->each(function ($record) {
                try {
                    DB::beginTransaction();

                    app(PaymentService::class)->changePaymentToCompleted($record->id);

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                }
            });
    }
}
