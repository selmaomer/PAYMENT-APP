<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Repositories\Contracts\OrderRepositoryInterface;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\StripePaymentService;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected StripePaymentService $paymentService
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
            $data['payment_status'] = 'Pending';

            $order = $this->orderRepository->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => $order
            ], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create order.'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function pay(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        try {
            $paymentData = $this->paymentService->createCheckoutSession($order);
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated.',
                'data' => $paymentData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signatureHeader = $request->header('Stripe-Signature') ?? '';

        try {
            $this->paymentService->handleWebhook($payload, $signatureHeader);
            return response()->json(['success' => true, 'message' => 'Webhook handled successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}