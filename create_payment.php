<?php
/**
 * Создание платежа через YooKassa
 */
include 'config.php';

header('Content-Type: application/json');

// Подключаем YooKassa SDK
require_once 'vendor/autoload.php';

use YooKassa\Client;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_payment') {
        // Проверяем, не оплачено ли уже
        if (isPaymentPaid()) {
            echo json_encode([
                'success' => true,
                'message' => 'Доступ уже оплачен',
                'already_paid' => true
            ]);
            exit;
        }
        
        // Получаем URL для возврата
        $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'https://' . $_SERVER['HTTP_HOST'] . '/payment_success.php';
        
        try {
            $client = new Client();
            $client->setAuth(YOOKASSA_SHOP_ID, YOOKASSA_SECRET_KEY);
            
            // Создаем уникальный ID платежа
            $idempotenceKey = uniqid('payment_', true);
            
            $payment = $client->createPayment(
                array(
                    'amount' => array(
                        'value' => PAYMENT_AMOUNT,
                        'currency' => 'RUB',
                    ),
                    'confirmation' => array(
                        'type' => 'redirect',
                        'return_url' => $return_url,
                    ),
                    'capture' => true,
                    'description' => PAYMENT_DESCRIPTION,
                    'metadata' => array(
                        'survey_id' => $_SESSION['survey_id'],
                        'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    )
                ),
                $idempotenceKey
            );
            
            // Сохраняем ID платежа в сессии
            $_SESSION['payment_id'] = $payment->getId();
            $_SESSION['payment_status'] = 'pending';
            
            // Получаем URL для оплаты
            $confirmationUrl = $payment->getConfirmation()->getConfirmationUrl();
            
            echo json_encode([
                'success' => true,
                'payment_id' => $payment->getId(),
                'confirmation_url' => $confirmationUrl
            ]);
            
        } catch (Exception $e) {
            error_log('YooKassa error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при создании платежа: ' . $e->getMessage()
            ]);
        }
    }
}
?>