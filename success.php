<?php
require_once 'config.php';
require_once 'layout.php';
requireAuth('student');

// This page was a generic e-commerce sample. Update to use this project's DB and payments table.
$user_id = $_SESSION['user']['id'] ?? 0;

// Amount can be passed via session, GET, or fallback to RM 450.00
$amount = isset($_SESSION['paid_amount']) ? (float) $_SESSION['paid_amount'] :
    (isset($_GET['amount']) ? (float) $_GET['amount'] : 450.00);
$currency = 'RM';
$description = 'Online payment';
$status = 'Paid';

$paymentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$insertId = 0;
$payment = null;

if ($paymentId > 0 && $conn) {
    $stmt = $conn->prepare('SELECT * FROM payments WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $paymentId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();
    if ($payment) {
        $insertId = (int) $payment['id'];
        if (strtolower($payment['status']) !== 'paid') {
            $now = date('Y-m-d H:i:s');
            $conn->query("UPDATE payments SET status='Paid', paid_at='$now' WHERE id=$paymentId");
            $payment['status'] = 'Paid';
            $payment['paid_at'] = $now;
        }
    }
}

if (!$payment && $user_id && $conn) {
    $uid = (int) $user_id;
    $amt = (float) $amount;
    $desc = $conn->real_escape_string($description);
    $now = date('Y-m-d H:i:s');
    $insert = $conn->query("INSERT INTO payments (user_id, amount, currency, description, status, created_at, paid_at)
                  VALUES ($uid, $amt, '$currency', '$desc', '$status', '$now', '$now')");
    if ($insert) {
        $insertId = $conn->insert_id;
        $paymentId = $insertId;
        $payment = [
            'id' => $insertId,
            'user_id' => $uid,
            'amount' => $amt,
            'currency' => $currency,
            'description' => $description,
            'status' => $status,
            'created_at' => $now,
            'paid_at' => $now,
        ];
    }
}

pageHead('Payment Successful');
?>
<div style="max-width:720px;margin:2rem auto;padding:1.2rem">
    <div class="crd">
        <div class="crdhdr">
            <h3>Payment Successful</h3>
        </div>
        <div style="padding:1rem">
            <p style="font-size:1.1rem;color:var(--forest)">&#10003; Thank you — your payment was successful.</p>
            <p><strong>Amount:</strong> <?= e($currency . ' ' . number_format($amount, 2)) ?></p>
            <?php if ($insertId): ?>
                <p><strong>Invoice/Record ID:</strong> <?= e($insertId) ?></p>
                <p><a href="studentHomePage.php?tab=payment&invoice=<?= (int) $insertId ?>">View Invoice</a> &nbsp;|&nbsp; <a
                    href="studentHomePage.php?tab=payment&refresh=1">Back to Payments</a></p>
            <?php else: ?>
                <p style="color:var(--MT)">No database record was created (not logged in or DB unavailable).</p>
                <p><a href="studentHomePage.php?tab=payment">Back to Payments</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php pageFoot(); ?>