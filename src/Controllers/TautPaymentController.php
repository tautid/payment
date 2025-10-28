<?php

namespace TautId\Payment\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Routing\Controller;
use TautId\Payment\Actions\BayarindAction;
use TautId\Payment\Services\PaymentService;

class TautPaymentController extends Controller
{
    public function __construct(
        public PaymentService $service
    ) {}

    public function show(string $trx_id): View
    {
        try {
            $payment = $this->service->getPaymentByTrxId($trx_id);

            $result = match ($payment->method->driver) {
                'bayarind' => (new BayarindAction)->run($payment),
                default => null
            };

            if (empty($result)) {
                throw new \Exception('Empty result');
            }

            return $result;
        } catch (RecordNotFoundException $e) {
            abort(404);
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
