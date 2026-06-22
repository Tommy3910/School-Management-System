<?php

require_once 'config.php';

$paymentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$payment = null;

if ($paymentId > 0 && $conn) {
    $stmt = $conn->prepare('SELECT * FROM payments WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();

    if ($payment && strtolower($payment['status']) !== 'paid') {
        $now = date('Y-m-d H:i:s');
        $stmt2 = $conn->prepare("UPDATE payments SET status='Paid', paid_at=? WHERE id=?");
        $stmt2->bind_param('si', $now, $paymentId);
        $stmt2->execute();
        $stmt2->close();

        // Notify the student, same as process_payment.php does for the web flow
        sendNotification($conn, $payment['user_id'], 'Payment Received',
            'We received your payment for invoice #' . $paymentId . '. Thank you.');

        $payment['status'] = 'Paid';
        $payment['paid_at'] = $now;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payment Successful</title>
<style>
  body{font-family:'Plus Jakarta Sans',Arial,sans-serif;background:#f7f3ec;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem}
  .card{background:#fff;border-radius:16px;box-shadow:0 2px 18px rgba(0,0,0,.08);padding:2rem;max-width:380px;width:100%;text-align:center}
  .check{width:56px;height:56px;border-radius:50%;background:#e8f5e9;color:#1b5e20;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:28px}
  h2{color:#0f2318;margin:0 0 .4rem;font-size:1.3rem}
  p{color:#5a6b60;font-size:.9rem;margin:.3rem 0}
  .amt{font-size:1.6rem;font-weight:700;color:#0f2318;margin:1rem 0}
  .note{font-size:.78rem;color:#5a6b60;margin-top:1.5rem;line-height:1.5}
</style>
</head>
<body>
  <div class="card">
    <div class="check">&#10003;</div>
    <?php if ($payment): ?>
      <h2>Payment Successful</h2>
      <p>Invoice #<?= e($payment['id']) ?></p>
      <div class="amt"><?= e($payment['currency'] . ' ' . number_format($payment['amount'], 2)) ?></div>
      <p>Thank you — your payment has been recorded.</p>
      <p class="note">You can close this tab and return to the Maplewood app to see your updated payment status.</p>
    <?php else: ?>
      <h2>Payment Recorded</h2>
      <p class="note">We couldn't find that invoice, but if Stripe confirmed your payment, please return to the app and refresh — it may take a moment to sync.</p>
    <?php endif; ?>
  </div>
</body>
</html>