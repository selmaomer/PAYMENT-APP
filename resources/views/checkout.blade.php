<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">

<div class="max-w-md w-full bg-gray-800 p-6 rounded">
    <h2 class="text-xl mb-4">Pay $49.99</h2>

    <form id="payment-form">
        <input type="text" id="customer_name" placeholder="Full Name" required class="w-full p-2 mb-4 bg-gray-700 rounded">
        <input type="email" id="email" placeholder="Email" required class="w-full p-2 mb-4 bg-gray-700 rounded">
        <div id="payment-element" style="min-height: 200px;"></div>
        <button id="submit" type="submit" class="bg-indigo-600 px-4 py-2 rounded w-full mt-4">
            Pay Now
        </button>
        <div id="error-message" class="text-red-400 mt-2"></div>
    </form>
</div>

<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', async function() {
    // Initialize Stripe
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    
    // Get DOM elements
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit');
    const customerNameInput = document.getElementById('customer_name');
    const emailInput = document.getElementById('email');
    const errorDiv = document.getElementById('error-message');
    const paymentElementDiv = document.getElementById('payment-element');
    
    let elements = null;
    
    // Function to initialize payment element
    async function initializePaymentElement(customerName, email) {
        try {
            // Clear any previous error
            errorDiv.innerText = '';
            
            // Get client secret from server
            const response = await fetch("{{ route('checkout.process') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    customer_name: customerName,
                    email: email
                })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to initialize payment');
            }
            
            const data = await response.json();
            
            if (!data.clientSecret) {
                throw new Error('No client secret received from server');
            }
            
            // Create Elements instance with client secret
            elements = stripe.elements({
                clientSecret: data.clientSecret,
                appearance: {
                    theme: 'night',
                    variables: {
                        colorPrimary: '#6366f1',
                        colorBackground: '#1f2937',
                        colorText: '#ffffff',
                        colorDanger: '#ef4444',
                        borderRadius: '8px',
                        fontFamily: 'system-ui, -apple-system, sans-serif',
                    },
                },
            });
            
            // Create and mount payment element
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');
            
            return true;
        } catch (error) {
            console.error('Initialization error:', error);
            errorDiv.innerText = error.message;
            return false;
        }
    }
    
    // Initialize on first input blur when both fields have values
    let initialized = false;
    
    async function tryInitialize() {
        if (!initialized && customerNameInput.value && emailInput.value) {
            const success = await initializePaymentElement(customerNameInput.value, emailInput.value);
            if (success) {
                initialized = true;
            }
        }
    }
    
    customerNameInput.addEventListener('blur', tryInitialize);
    emailInput.addEventListener('blur', tryInitialize);
    
    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Disable button and show loading
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';
        
        const customerName = customerNameInput.value;
        const email = emailInput.value;
        
        // Validate fields
        if (!customerName || !email) {
            errorDiv.innerText = 'Please fill in all fields';
            submitButton.disabled = false;
            submitButton.textContent = 'Pay Now';
            return;
        }
        
        // Initialize if not already done
        if (!initialized) {
            const success = await initializePaymentElement(customerName, email);
            if (!success) {
                submitButton.disabled = false;
                submitButton.textContent = 'Pay Now';
                return;
            }
            initialized = true;
        }
        
        // Confirm payment
        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: "{{ route('payment.success') }}",
                payment_method_data: {
                    billing_details: {
                        name: customerName,
                        email: email,
                    }
                }
            }
        });
        
        if (error) {
            errorDiv.innerText = error.message;
            submitButton.disabled = false;
            submitButton.textContent = 'Pay Now';
        }
    });
});
</script>

</body>
</html>