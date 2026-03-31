<?php
/**
 * Проверка статуса оплаты
 */
include 'config.php';

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'paid' => isPaymentPaid(),
    'payment_status' => $_SESSION['payment_status'] ?? 'pending',
    'payment_enabled' => PAYMENT_ENABLED
]);
?>