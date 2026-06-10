<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
      protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'amount',
        'currency',
        'payment_status',
        'transaction_reference',
    ];
   
}
