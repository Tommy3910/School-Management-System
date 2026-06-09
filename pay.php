<?php
// Your Stripe Secret Key (sk_test_...)
$stripe_secret_key = "sk_test_51TWUKVRyNTo0JNBHUu8coRgaxIO73lqjhZuj7TAZQzGMvWzfX3jHArXFXZncfh8ERkl8sJDBOrQThjCZjY51q5kX00AsZKoNqp";

// Data we are sending to Stripe to create a checkout session
$data = [
    'payment_method_types' => ['card'],

    'line_items' => [
        [
            'price_data' => [
                'currency' => 'myr', // Malaysian Ringgit

                'product_data' => [
                    'name' => 'Maplewood Academy Fee'
                ],

                'unit_amount' => 45000, // Amount in CENTS (RM 100.00 = 10000 cents)
            ],

            'quantity' => 1,
        ]
    ],

    'mode' => 'payment',

    'success_url' => 'http://localhost/School-Management-system/success.php',
    'cancel_url' => 'http://localhost/School-Management-System/studentHomePage.php?tab=payment',
];

// Initialize cURL to connect to Stripe API
$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret_key . ':');

// Execute the API call
$response = curl_exec($ch);

curl_close($ch);

// Decode the JSON response from Stripe
$session = json_decode($response, true);

// If successful, redirect the user to Stripe's secure payment page
if (isset($session['url'])) {
    header("Location: " . $session['url']);
    exit();
} else {
    echo "Error connecting to Stripe: ";
    print_r($session);
}
?>