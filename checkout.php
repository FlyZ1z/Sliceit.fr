\Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => 'Produit 1',
            ],
            'unit_amount' => 399,  // Prix en centimes
        ],
        'quantity' => 1,
    ]],
    'metadata' => [
        'product_id' => $product_id,  // ID du produit
    ],
    'mode' => 'payment',
    'success_url' => 'https://votre-site.com/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'https://votre-site.com/stripe_cancel.php',
]);
