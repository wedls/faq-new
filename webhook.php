<?php
/**
 * Webhook для обработки уведомлений от YooKassa
 * URL: https://other.wedls.com/webhook.php
 * Добавьте этот URL в настройках YooKassa в разделе "Уведомления"
 */
include 'config.php';

// Получаем данные от YooKassa
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo 'Invalid data';
    exit;
}

error_log('Webhook received: ' . json_encode($data));

// Проверяем событие
if (isset($data['event']) && $data['event'] === 'payment.succeeded') {
    $payment_id = $data['object']['id'] ?? null;
    $metadata = $data['object']['metadata'] ?? [];
    
    if ($payment_id) {
        // Здесь нужно найти сессию по payment_id
        // Для простоты будем искать по файлам результатов
        // В реальном проекте лучше хранить связь payment_id -> session_id в БД
        
        error_log('Payment succeeded: ' . $payment_id);
    }
}

http_response_code(200);
echo 'OK';
?>