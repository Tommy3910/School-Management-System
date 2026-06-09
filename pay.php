<?php
require_once 'config.php';
requireAuth('student');

$userId = $_SESSION['user']['id'] ?? null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch payment
$payment = null;
if ($id > 0 && $conn) {
    $res = $conn->query("SELECT p.*, u.name AS student_name FROM payments p LEFT JOIN users u ON p.user_id=u.id WHERE p.id=$id LIMIT 1");
    if ($res)
        $payment = $res->fetch_assoc();
}

if (!$payment || (int) $payment['user_id'] !== (int) $userId) {
    flash('toast', 'Invoice not found.');
    redirect('studentHomePage.php?tab=payment');
}

pageHead('Pay Invoice');
?>
<div style="max-width:720px;margin:2rem auto;padding:1.2rem">
    <div class="crd">
        <div class="crdhdr">
            <h3>Invoice #<?= e($payment['id']) ?></h3>
        </div>
        <div style="padding:1rem">
            <p><strong>Student:</strong> <?= e($payment['student_name']) ?></p>
            <p><strong>Description:</strong> <?= e($payment['description'] ?? 'School fees') ?></p>
            <p><strong>Amount:</strong>
                <?= e(($payment['currency'] ?? 'RM') . ' ' . number_format((float) $payment['amount'], 2)) ?></p>
            <p><strong>Status:</strong> <?= e($payment['status']) ?></p>

            <?php if ($payment['status'] !== 'Paid'): ?>
                <form method="POST" action="process_payment.php">
                    <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                    <button type="submit" class="bpr bsm"><i class="fas fa-credit-card"></i> Pay Now (Simulated)</button>
                </form>
            <?php else: ?>
                <div style="color:var(--MT)">This invoice was paid on <?= e($payment['paid_at']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php pageFoot(); ?>