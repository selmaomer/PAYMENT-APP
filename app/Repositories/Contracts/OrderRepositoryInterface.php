<?php

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function create(array$data): Order;
    public function find(int $id): ?Order;
    public function findByOrderNumber(string $orderNumber): ?Order;
    public function findByTransactionReference(string $reference): ?Order;
    public function update(Order $order, array $data): bool;
}