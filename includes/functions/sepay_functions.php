<?php
/**
 * SePay / VietQR helpers — QR URL generation and webhook processing.
 * QR format: https://docs.sepay.vn/tao-qr-code-vietqr-dong.html
 */

require_once __DIR__ . '/../config/sepay_config.php';
require_once __DIR__ . '/../database/Database.php';

/**
 * Generate a unique, non-sequential payment code for bank transfer matching.
 */
function generate_sepay_order_code(): string
{
    $prefix = preg_replace('/[^A-Za-z0-9]/', '', SEPAY_ORDER_CODE_PREFIX) ?: 'DH';
    return $prefix . time() . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
}

/**
 * Build transfer memo (des) sent with the QR code.
 * Must contain the order code so SePay can extract it from the webhook `code` field.
 */
function get_sepay_transfer_description(string $orderCode): string
{
    $orderCode = preg_replace('/[^A-Za-z0-9]/', '', $orderCode);
    $memoPrefix = trim(SEPAY_TRANSFER_MEMO_PREFIX);

    if ($memoPrefix !== '') {
        return $memoPrefix . ' ' . $orderCode;
    }

    return $orderCode;
}

/**
 * Build a dynamic VietQR image URL per official SePay documentation.
 *
 * @see https://docs.sepay.vn/tao-qr-code-vietqr-dong.html
 * @see https://vietqr.app/img
 */
function build_sepay_qr_url(float $amount, string $orderCode): string
{
    if (SEPAY_BANK_ACCOUNT === '' || SEPAY_BANK_NAME === '') {
        throw new RuntimeException('SePay bank account settings are not configured.');
    }

   $amountVnd = (int) round($amount * SEPAY_USD_TO_VND_RATE);

    $params = [
        'acc' => SEPAY_BANK_ACCOUNT,
        'bank' => SEPAY_BANK_NAME,
        'amount' => $amountVnd,
        'des' => get_sepay_transfer_description($orderCode),
    ];

    if (SEPAY_QR_TEMPLATE !== '') {
        $params['template'] = SEPAY_QR_TEMPLATE;
    }

    return 'https://vietqr.app/img?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

/**
 * Verify SePay webhook Authorization header (API Key method).
 *
 * @see https://docs.sepay.vn/tich-hop-webhooks.html
 */
function verify_sepay_webhook_auth(): bool
{
    if (SEPAY_SECRET_KEY === '') {
        return false;
    }

    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($auth === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $key => $value) {
            if (strtolower((string) $key) === 'authorization') {
                $auth = $value;
                break;
            }
        }
    }

    return hash_equals('Apikey ' . SEPAY_SECRET_KEY, $auth);
}

/**
 * Process an incoming SePay webhook payload and mark the matching order as paid.
 */
function process_sepay_webhook(array $data): bool
{
    $sepayId = (int) ($data['id'] ?? 0);
    if ($sepayId <= 0) {
        return false;
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    try {
        $conn->prepare(
            'INSERT INTO `sepay_webhook_logs` (`sepay_transaction_id`, `payload`) VALUES (?, ?)'
        )->execute([$sepayId, json_encode($data, JSON_UNESCAPED_UNICODE)]);
    } catch (PDOException $e) {
        // Duplicate transaction — already handled (SePay retry/idempotency).
        return true;
    }

    $transferType = (string) ($data['transferType'] ?? '');
    if ($transferType !== 'in') {
        return true;
    }

    $code = extract_sepay_order_code_from_text((string) ($data['content'] ?? ''));
if ($code === '') {
    $code = trim((string) ($data['code'] ?? ''));
    }

    if ($code === '') {
        return true;
    }

    $order = $db->queryRow(
        'SELECT * FROM `orders` WHERE `order_code` = ? LIMIT 1',
        [$code]
    );

    if (!$order || $order['payment_method'] !== 'bank_transfer') {
        return true;
    }

    if ($order['payment_status'] === 'paid') {
        $conn->prepare(
            'UPDATE `sepay_webhook_logs` SET `order_id` = ? WHERE `sepay_transaction_id` = ?'
        )->execute([(int) $order['id'], $sepayId]);
        return true;
    }

    $transferAmount = (float) ($data['transferAmount'] ?? 0);
$expectedAmountVnd = (float) $order['total_amount'] * SEPAY_USD_TO_VND_RATE;

// Allow a small tolerance (e.g. 1000 VND) to account for rounding differences.
if ($transferAmount < ($expectedAmountVnd - 1000)) {
    return true;
    }

    $conn->beginTransaction();

    try {
        $conn->prepare(
            'UPDATE `orders` SET `payment_status` = ? WHERE `id` = ? AND `payment_status` != ?'
        )->execute(['paid', (int) $order['id'], 'paid']);

        $conn->prepare(
            'UPDATE `payments` SET `payment_status` = ?, `transaction_id` = ? WHERE `order_id` = ?'
        )->execute(['paid', (string) $sepayId, (int) $order['id']]);

        $conn->prepare(
            'UPDATE `sepay_webhook_logs` SET `order_id` = ? WHERE `sepay_transaction_id` = ?'
        )->execute([(int) $order['id'], $sepayId]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Try to locate the configured order-code prefix inside free-text transfer content.
 */
function extract_sepay_order_code_from_text(string $text): string
{
    $prefix = preg_quote(preg_replace('/[^A-Za-z0-9]/', '', SEPAY_ORDER_CODE_PREFIX) ?: 'DH', '/');
    if (preg_match('/(' . $prefix . '[A-Za-z0-9]+)/', $text, $matches)) {
        return $matches[1];
    }

    return '';
}
