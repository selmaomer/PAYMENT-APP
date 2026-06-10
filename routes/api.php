<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;


Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);

// Webhook 
Route::post('/webhooks/payment', [OrderController::class, 'handleWebhook']);