<?php

namespace TautId\Payment\Commands;

use Illuminate\Console\Command;
use TautId\Payment\Models\Payment;
use TautId\Payment\Enums\PaymentStatusEnum;
use TautId\Payment\Services\PaymentService;

class PaymentDueCommand extends Command
{
    public $signature = 'taut-payment:due';

    public $description = 'Change payment to due after exceeding due_at';

    public function handle()
    {
        Payment::where('status',PaymentStatusEnum::Pending->value)
                ->where('due_at',"<",now())
                ->each(100,fn($chunk) =>
                    $chunk->each(function($record){
                        try{
                            if($record->status != PaymentStatusEnum::Pending->value)
                                throw new \Exception('Payment is not pending');

                            if($record->due_at > now())
                                throw new \Exception('Payment still not expired yet');

                            app(PaymentService::class)->changePaymentToDue($record->id);
                        }catch(\Exception $e){

                        }
                    })
                );
    }
}
