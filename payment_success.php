<?php
/**
 * Страница успешной оплаты
 * Сюда пользователь возвращается после оплаты
 */
include 'config.php';

// Если оплата отключена, перенаправляем на опросник
if (!PAYMENT_ENABLED) {
    header('Location: index.php');
    exit;
}

// Проверяем статус платежа
$payment_id = $_SESSION['payment_id'] ?? null;

if ($payment_id) {
    require_once 'vendor/autoload.php';
    use YooKassa\Client;
    
    try {
        $client = new Client();
        $client->setAuth(YOOKASSA_SHOP_ID, YOOKASSA_SECRET_KEY);
        
        $payment = $client->getPaymentInfo($payment_id);
        
        if ($payment->getStatus() === 'succeeded') {
            $_SESSION['payment_status'] = 'paid';
            $payment_success = true;
        } else {
            $_SESSION['payment_status'] = 'failed';
            $payment_success = false;
        }
    } catch (Exception $e) {
        error_log('Payment check error: ' . $e->getMessage());
        $payment_success = false;
    }
} else {
    $payment_success = false;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат оплаты - Опросник "РЕСУРС"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style-media.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <style>
        .payment-result {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .payment-result .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .payment-result.success .icon {
            color: #27ae60;
        }
        .payment-result.failed .icon {
            color: #e74c3c;
        }
        .payment-result h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .payment-result p {
            margin-bottom: 15px;
            color: #666;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #2c3e50;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header" style="min-height: 140px;">
            <div class="logo">
                <div>
                    <h1>Оплата доступа</h1>
                </div>
            </div>
        </header>
        
        <?php if ($payment_success): ?>
            <div class="payment-result success">
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Оплата прошла успешно!</h2>
                <p>Доступ ко всем 4 модулям опросника "РЕСУРС" открыт.</p>
                <p>Вы можете приступить к прохождению тестирования прямо сейчас.</p>
                <a href="index.php" class="btn">
                    <i class="fas fa-arrow-right"></i> Перейти к опроснику
                </a>
            </div>
        <?php else: ?>
            <div class="payment-result failed">
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2>Оплата не подтверждена</h2>
                <p>К сожалению, не удалось подтвердить оплату.</p>
                <p>Пожалуйста, попробуйте еще раз или обратитесь в службу поддержки.</p>
                <a href="payment.php" class="btn">
                    <i class="fas fa-credit-card"></i> Попробовать снова
                </a>
            </div>
        <?php endif; ?>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
        </footer>
    </div>
</body>
</html>