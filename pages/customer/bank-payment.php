<?php
/**
 * Bank Transfer Payment Page — displays dynamic VietQR and polls for payment confirmation.
 */

$pageTitle = 'Bank Transfer Payment';
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/sepay_functions.php';

require_customer();

$db = Database::getInstance();
$userId = (int) ($_SESSION['user_id'] ?? 0);
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderId <= 0) {
    set_flash('warning', 'No order was found to pay.');
    redirect(BASE_URL . 'pages/customer/order-history.php');
}

$order = $db->queryRow(
    'SELECT * FROM `orders` WHERE `id` = ? AND `user_id` = ? LIMIT 1',
    [$orderId, $userId]
);

if (!$order) {
    set_flash('warning', 'The selected order could not be found.');
    redirect(BASE_URL . 'pages/customer/order-history.php');
}

if ($order['payment_method'] !== 'bank_transfer') {
    redirect(BASE_URL . 'pages/customer/order-success.php?id=' . $orderId);
}

if ($order['payment_status'] === 'paid') {
    redirect(BASE_URL . 'pages/customer/order-success.php?id=' . $orderId);
}

$orderCode = (string) ($order['order_code'] ?? '');
$transferDescription = $orderCode !== '' ? get_sepay_transfer_description($orderCode) : '';

try {
    $qrUrl = build_sepay_qr_url((float) $order['total_amount'], $orderCode);
} catch (RuntimeException $e) {
    set_flash('danger', 'Bank transfer is not configured yet. Please contact support.');
    redirect(BASE_URL . 'pages/customer/order-detail.php?id=' . $orderId);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-2"><i class="bi bi-qr-code-scan text-danger me-2"></i>Complete Bank Transfer</h2>
                    <p class="text-muted mb-0">Scan the QR code below with your banking app. Payment is confirmed automatically once the transfer is received.</p>
                </div>

                <div id="paymentPending">
                    <div class="text-center mb-4">
                        <img
                            src="<?php echo e($qrUrl); ?>"
                            alt="VietQR payment code for order <?php echo e($orderCode); ?>"
                            class="img-fluid rounded-4 border shadow-sm"
                            style="max-width: 320px;"
                        >
                    </div>

                    <div class="p-4 rounded-4 bg-light border mb-4">
                        <div class="row g-3 small">
                            <div class="col-sm-6">
                                <div class="text-muted">Order Code</div>
                                <div class="fw-bold text-dark user-select-all"><?php echo e($orderCode); ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted">Amount</div>
                                <div class="fw-bold text-danger fs-5"><?php echo format_currency($order['total_amount']); ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted">Bank</div>
                                <div class="fw-semibold"><?php echo e(SEPAY_BANK_NAME); ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted">Account Number</div>
                                <div class="fw-semibold user-select-all"><?php echo e(SEPAY_BANK_ACCOUNT); ?></div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted">Transfer Description</div>
                                <div class="fw-semibold user-select-all"><?php echo e($transferDescription); ?></div>
                                <div class="text-muted mt-1">Keep this description exactly as shown so SePay can match your payment.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-center gap-2 text-muted mb-4">
                        <div class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"></div>
                        <span>Waiting for payment confirmation…</span>
                    </div>
                </div>

                <div id="paymentSuccess" class="d-none text-center">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-check2-circle text-success" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="fw-bold text-success mb-2">Payment Received</h4>
                    <p class="text-muted">Your order has been confirmed. Redirecting…</p>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $orderId; ?>" class="btn btn-outline-secondary px-4">View Order</a>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-dark px-4">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderId = <?php echo (int) $orderId; ?>;
    const statusUrl = <?php echo json_encode(BASE_URL . 'pages/customer/payment-status.php?id=' . $orderId); ?>;
    const successUrl = <?php echo json_encode(BASE_URL . 'pages/customer/order-success.php?id=' . $orderId); ?>;
    let pollTimer = null;

    async function checkPaymentStatus() {
        try {
            const response = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (data.paid) {
                document.getElementById('paymentPending').classList.add('d-none');
                document.getElementById('paymentSuccess').classList.remove('d-none');

                if (pollTimer) {
                    clearInterval(pollTimer);
                }

                setTimeout(() => {
                    window.location.href = successUrl;
                }, 1500);
            }
        } catch (error) {
            // Keep polling silently on transient network errors.
        }
    }

    checkPaymentStatus();
    pollTimer = setInterval(checkPaymentStatus, 3000);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
