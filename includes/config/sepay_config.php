<?php
/**
 * SePay / VietQR integration settings.
 *
 * Fill in your values before enabling bank-transfer payments.
 * Do not commit real secrets to version control.
 */

/** API Key configured in SePay webhook security (Authorization: Apikey ...) */
define('SEPAY_SECRET_KEY', '877fa5025c94fd37ca22cf1f75bbaa543aebf757605a7317');

/** Linked bank account number (acc parameter) */
define('SEPAY_BANK_ACCOUNT', '0364035076');

/** Bank short name, alias, or bin — see https://vietqr.app/banks.json */
define('SEPAY_BANK_NAME', 'MBBank');

/** Order code prefix for transfer memo matching (e.g. DH) */
define('SEPAY_ORDER_CODE_PREFIX', 'DH');

/**
 * Optional extra text prepended to transfer memo (des).
 * Examples: 'SEVQR' for VietinBank personal, 'TKP001' for memo-based VA.
 */
define('SEPAY_TRANSFER_MEMO_PREFIX', '');

/** VietQR image layout: compact | qronly | standee | (empty for default) */
define('SEPAY_QR_TEMPLATE', 'compact');
/** USD to VND exchange rate used for QR/payment amount conversion. Update as needed. */
define('SEPAY_USD_TO_VND_RATE', 25000);