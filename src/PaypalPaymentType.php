<?php

namespace Lancodev\LunarPaypal;

use Illuminate\Support\Facades\DB;
use Lancodev\LunarPaypal\Models\Paypal;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;

class PaypalPaymentType extends AbstractPayment
{
    public Paypal $paypal;

    public function __construct()
    {
        $this->paypal = new Paypal();
    }

    /**
     * Authorize the payment for processing.
     */
    public function authorize(): PaymentAuthorize
    {
        if ($this->paypal->authorize($this->order->cart, $this->order) === false) {
            return new PaymentAuthorize(
                success: true,
            );
        }

        return new PaymentAuthorize(
            success: false,
            message: 'Unable to authorize payment',
        );
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        if ($this->paypal->capture($transaction) === false) {
            return new PaymentCapture(
                success: false,
                message: 'Unable to capture payment',
            );
        }

        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        if ($this->paypal->refund($transaction, $amount, $notes) === false) {
            return new PaymentRefund(
                success: false,
                message: 'Unable to refund payment',
            );
        }

        return new PaymentRefund(
            success: true
        );
    }

    private function releaseSuccess()
    {
        DB::transaction(function () {
            $this->order->update([
                'status' => $this->config['released'] ?? 'paid',
                'placed_at' => now(),
            ]);

            $transactions = [];

            $type = 'capture';

            if ($this->policy == 'manual') {
                $type = 'intent';
            }

            $transactions[] = [
                'success' => true,
                'type' => 'charge',
                'driver' => 'paypal',
                'amount' => $this->order->total,
                'reference' => 'paypal',
                'status' => 'succeeded',
                'notes' => null,
                'card_type' => 'visa',
                'last_four' => '4242',
                'captured_at' => now(),
            ];
            $this->order->transactions()->createMany($transactions);
        });

        return new PaymentAuthorize(success: true);
    }
}
