# PAYMENT-APP

payment integration module using Laravel that simulates a real-world e-commerce payment workflow with webhook processing.

##  Table of Contents
- [Installation Steps](#-installation-steps)
- [Environment Configuration](#-environment-configuration)
- [API Documentation](#-api-documentation)
- [Payment Gateway Setup](#-payment-gateway-setup)

##  Installation Steps

Follow these instructions to get a local development copy of the project up and running.

1. **Clone the repository:**
   ```bash
   git clone https://github.com
   cd your-repo-name
   ```
2. **Install dependencies:**
   ```
   composer install  
   ```
3. **Run database migrations:**
   ```
   php artisan migrate  
   ```
4. **Start the development server:**
   ```bash
   php artisan serve 
   ```

##  Environment Configuration

This project requires certain environment variables to function correctly. Copy the example file and fill in the required details:

```bash
cp .env.example .env
```


##  API Documentation

The following are the primary endpoints available in this application. For a complete collection, view the [Postman API ]:
(https://documenter.getpostman.com/view/33198032/2sBXwsKpG5)
### 1. get order 
- **URL:** `http://127.0.0.1:8000/api/orders/1`
- **Method:** `GET`
- **Description:** get order detalis
- **Response Example:**
  ```json
  {
    "success": true,
    "data": {
        "id": 1,
        "order_number": "ORD-4MLQVLMUUQ",
        "customer_name": "Ahmed adam",
        "customer_email": "ahmed@gamil.com",
        "amount": "44.33",
        "currency": "USD",
        "payment_status": "pending",
        "transaction_reference": null,
        "created_at": "2026-06-09T10:09:07.000000Z",
        "updated_at": "2026-06-09T10:09:07.000000Z"
    }
}
  
### 2. create order 
- **URL:** `http://127.0.0.1:8000/api/orders`
- **Method:** `POST`
- **Description:** create order
**Body Parameters:** 
 `customer_name`(string, required),
  `customer_email` ,
  `amount`,
  `currency`(string, required)
 
- **Response Example:**
  ```json
  {
    "success": true,
    "message": "Order created successfully.",
    "data": {
        "customer_name": "Dalia Ali",
        "customer_email": "Dalia@gamil.com",
        "amount": "900.33",
        "currency": "USD",
        "order_number": "ORD-OFHXDBD6FX",
        "payment_status": "Pending",
        "updated_at": "2026-06-10T14:45:44.000000Z",
        "created_at": "2026-06-10T14:45:44.000000Z",
        "id": 6
    }
}
 
  ### 3. Initiate Payment
- **URL:** `http://127.0.0.1:8000/api/orders/3/pay`
- **Method:** `POST`
**Body Parameters:** 
 `customer_name`(string, required),
  `customer_email` ,
  `amount`(e.g., 200.20),
  `currency`(string, required)
 
**Response Example:**
  ```json
  {
    "success": true,
    "message": "Payment initiated.",
    "data": {
        "payment_url": "https://checkout.stripe.com/c/pay/cs_test_a1UpRZuMLpjHY5pNGrKzo0QvILmPyurLuTDwPn928AdPX0r7O2b7Wikjtt#fidnandhYHdWcXxpYCc%2FJ2FgY2RwaXEnKSdicGRmZGhqaWBTZHdsZGtxJz8nZmprcXdqaScpJ2R1bE5gfCc%2FJ3VuWnFgdnFaMDRUQF1oYUA3cUhdUHVKNH90RkZGc1RUQXVocWR0b1BVV1JySUhjR25gY3FqdFJNSEdfcTx3PXddcE91UVdqXzw1RmxDX0pDQk0xRHwxYzZEQXA3PUMzUXc1NXJnc008V1YwJyknY3dqaFZgd3Ngdyc%2FcXdwYCknZ2RmbmJ3anBrYUZqaWp3Jz8nJmNjY2NjYycpJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl",
        "transaction_reference": "cs_test_a1UpRZuMLpjHY5pNGrKzo0QvILmPyurLuTDwPn928AdPX0r7O2b7Wikjtt"
    }
}
```
 ### 4. ًWhebhookPayment
- **URL:** `http://127.0.0.1:8000/api/webhooks/payment`
- **Method:** `POST`
- **Description:** create order
**Body Parameters:**
{
  "type": "checkout.session.completed",
  "data": {
    "object": {
      "id": "4",
      "metadata": {
        "order_number": "ORD-TUBNOAAUJE"
      }
    }
  }
- **Response Example:**
  ```json
  {
    "success": true,
    "message": "Webhook handled successfully."
}

##  Payment Gateway Setup Instructions

Tpayment settings:

1. **Stripe Integration**
   - Create an account on [Stripe](https://stripe.com/resources/more/how-to-integrate-a-payment-gateway-into-a-website).
   - Navigate to **Developers > API Keys** to obtain your Publishable and Secret keys.
   - Add these keys to your `.env` file as `STRIPE_KEY` and `STRIPE_SECRET`.
   - Add these webhookkeys to your `.env` file as `STRIPE_WEBHOOK_SECRET` .