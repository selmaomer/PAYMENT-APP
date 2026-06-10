<?php
namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;


class StripePaymentService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Order $order): array
    {
        Log::info('Initiating payment request for order: ' . $order->order_number);

        if ($order->payment_status === 'Paid') {
            throw new Exception('This order has already been paid.');
        }

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->currency),
                        'product_data' => [
                            'name' => 'Order #' . $order->order_number,
                        ],
                        'unit_amount' => (int) ($order->amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/api/payment/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/api/payment/cancel'),
                'metadata' => [
                    'order_number' => $order->order_number,
                ],
            ]);

            $this->orderRepository->update($order, [
                'transaction_reference' => $session->id,
            ]);

            Log::info('Payment session created successfully for order: ' . $order->order_number, ['session_id' => $session->id]);

            return ['payment_url' => $session->url, 'transaction_reference' => $session->id];

        } catch (Exception $e) {
            Log::error('Payment initiation failed for order: ' . $order->order_number, ['error' => $e->getMessage()]);
            throw new Exception('Payment Gateway Error: ' . $e->getMessage());
        }
    }

   public function handleWebhook(string $payload, string $signatureHeader): bool
{
    Log::info('Incoming Webhook received.');
    
    $event = json_decode($payload);

    $session = $event->data->object ?? null;
  
    $orderNumber = $session->metadata->order_number ?? null;

    if (!$orderNumber) {
        Log::warning('Webhook processing skipped: No order number in metadata.');

        return true; 
    }

    $order = $this->orderRepository->findByOrderNumber($orderNumber);

    if (!$order) {
        Log::error('Webhook error: Order not found.', [
            'order_number' => $orderNumber
        ]);

        throw new Exception('Order Not Found', 404);
    }

    if ($order->payment_status === 'Paid') {
        Log::info('Webhook processing skipped: Order already marked as Paid.', [
            'order_number' => $orderNumber
        ]);

        return true;
    }

    switch ($event->type) {
        case 'checkout.session.completed':
            $this->orderRepository->update($order, [
                'payment_status' => 'Paid'
            ]);
            break;

        case 'checkout.session.async_payment_failed':
            $this->orderRepository->update($order, [
                'payment_status' => 'Failed'
            ]);
            break;
    }

    return true;
}
}