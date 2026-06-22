<?php
// api_create_checkout.php
// JSON endpoint for the Flutter app — mirrors pay.php's logic but
// returns the Stripe Checkout URL as JSON instead of doing a redirect,
// since a mobile app calling over HTTP has no PHP session attached.

require_once 'config.php';
header('Content-Type: application/json');

$stripe_secret_key = "sk_test_51TWUKVRyNTo0JNBHUu8coRgaxIO73lqjhZuj7TAZQzGMvWzfX3jHArXFXZncfh8ERkl8sJDBOrQThjCZjY51q5kX00AsZKoNqp";

// Flutter sends the logged-in user's id directly (no session to rely on)
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
if (!$userId) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

// Confirm the user exists and is a student
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'student' LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$valid = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$valid) {
    echo json_encode(['error' => 'Invalid user']);
    exit;
}

$amount = 450.00;
$currency = 'RM';
$description = 'Maplewood Academy Fee';
$now = date('Y-m-d H:i:s');
$paymentId = 0;

// Reuse an existing unpaid invoice, or create a new one — same logic as pay.php
$stmt = $conn->prepare("SELECT id FROM payments WHERE user_id = ? AND status = 'Invoice Sent' ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    $paymentId = $existing['id'];
} else {
    $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, currency, description, status, created_at) VALUES (?, ?, ?, ?, 'Invoice Sent', ?)");
    $stmt->bind_param('idsss', $userId, $amount, $currency, $description, $now);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $paymentId = $stmt->insert_id;
    }
    $stmt->close();
}

if (!$paymentId) {
    echo json_encode(['error' => 'Unable to create invoice']);
    exit;
}

// Same Stripe Checkout Session creation as pay.php.
// success_url points at success_mobile.php (NOT success.php) — that
// version doesn't require a PHP session, since the browser completing
// checkout here is Chrome on the phone, not the Flutter app's session.
$baseUrl = 'http://10.0.2.2/School-Management-System'; // ← update if your folder name changes

$data = [
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'myr',
            'product_data' => ['name' => 'Maplewood Academy Fee'],
            'unit_amount' => 45000,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => $baseUrl . '/success_mobile.php?id=' . urlencode($paymentId),
    'cancel_url'  => $baseUrl . '/studentHomePage.php?tab=payment',
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
    echo json_encode(['url' => $session['url'], 'payment_id' => $paymentId]);
} else {
    echo json_encode(['error' => 'Stripe error', 'details' => $session]);
}