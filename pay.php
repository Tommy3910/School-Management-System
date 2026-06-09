<?php
require_once 'config.php';
requireAuth('student');

$stripe_secret_key = "sk_test_51TWUKVRyNTo0JNBHUu8coRgaxIO73lqjhZuj7TAZQzGMvWzfX3jHArXFXZncfh8ERkl8sJDBOrQThjCZjY51q5kX00AsZKoNqp";

$userId = $_SESSION['user']['id'] ?? 0;
if (!$userId) {
    redirect('login.php');
}

$amount = 450.00; // RM 450.00
$currency = 'RM';
$description = 'Maplewood Academy Fee';
$status = 'Invoice Sent';
$now = date('Y-m-d H:i:s');

$paymentId = 0;
if ($conn) {
    $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, currency, description, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('idssss', $userId, $amount, $currency, $description, $status, $now);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $paymentId = $stmt->insert_id;
    }
    $stmt->close();
}

if (!$paymentId) {
    flash('toast', 'Unable to create invoice. Please try again.');
    redirect('studentHomePage.php?tab=payment');
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$baseUrl = $scheme . '://' . $host . ($path === '/' ? '' : $path);

$data = [
    'payment_method_types' => ['card'],
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => 'Maplewood Academy Fee'
                ],
                'unit_amount' => 45000,
            ],
            'quantity' => 1,
        ]
    ],
    'mode' => 'payment',
    'success_url' => $baseUrl . '/success.php?id=' . urlencode($paymentId),
    'cancel_url' => $baseUrl . '/studentHomePage.php?tab=payment',
];

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret_key . ':');

$response = curl_exec($ch);
curl_close($ch);

$session = json_decode($response, true);

if (isset($session['url'])) {
    header("Location: " . $session['url']);
    exit();
}

echo "Error connecting to Stripe: ";
print_r($session);
?>