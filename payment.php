<?php
include 'config.php';

// Если оплата отключена, перенаправляем на опросник
if (!PAYMENT_ENABLED) {
    header('Location: index.php');
    exit;
}

// Если уже оплачено, перенаправляем на опросник
if (isPaymentPaid()) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата доступа - Опросник "РЕСУРС"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style-media.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <style>
        .payment-container {
            max-width: 500px;
            margin: 30px auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .payment-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .payment-header h2 {
            margin: 0 0 10px;
            font-size: 28px;
        }
        .payment-header p {
            margin: 0;
            opacity: 0.9;
        }
        .payment-body {
            padding: 30px;
        }
        .price {
            text-align: center;
            margin-bottom: 30px;
        }
        .price .amount {
            font-size: 48px;
            font-weight: bold;
            color: #2c3e50;
        }
        .price .currency {
            font-size: 24px;
        }
        .price .description {
            color: #7f8c8d;
            margin-top: 10px;
        }
        .modules-list {
            margin: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        .modules-list h4 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .modules-list ul {
            list-style: none;
            padding: 0;
        }
        .modules-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modules-list li i {
            color: #27ae60;
            width: 20px;
        }
        .pay-btn {
            width: 100%;
            padding: 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .pay-btn:hover {
            background: #219a52;
            transform: translateY(-2px);
        }
        .pay-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        .payment-methods {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .payment-methods i {
            font-size: 20px;
            margin: 0 5px;
            color: #95a5a6;
        }
        .error-message {
            background: #fee;
            color: #e74c3c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-container">
            <div class="payment-header">
                <h2>Доступ к опроснику</h2>
                <p>Личностный опросник "РЕСУРС"</p>
            </div>
            <div class="payment-body">
                <div class="price">
                    <div class="amount"><?php echo number_format(PAYMENT_AMOUNT, 0, ',', ' '); ?> <span class="currency">₽</span></div>
                    <div class="description">разовый доступ ко всем модулям</div>
                </div>
                
                <div class="modules-list">
                    <h4>В стоимость входит:</h4>
                    <ul>
                        <li><i class="fas fa-brain"></i> Модуль 1: Психоэмоциональное состояние (136 вопросов)</li>
                        <li><i class="fas fa-heartbeat"></i> Модуль 2: Стресс и напряжение (95 вопросов)</li>
                        <li><i class="fas fa-chart-line"></i> Модуль 3: Профориентация (30 вопросов)</li>
                        <li><i class="fas fa-exclamation-triangle"></i> Модуль 4: Склонность к ПАВ (30 вопросов)</li>
                    </ul>
                </div>
                
                <div class="error-message" id="errorMessage"></div>
                
                <button class="pay-btn" id="payBtn">
                    <i class="fas fa-credit-card"></i> Оплатить <?php echo number_format(PAYMENT_AMOUNT, 0, ',', ' '); ?> ₽
                </button>
                
                <div class="payment-methods">
                    <p>Способы оплаты:</p>
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-mir"></i>
                    <i class="fas fa-mobile-alt"></i> СБП
                    <i class="fab fa-apple"></i> Apple Pay
                    <i class="fab fa-google"></i> Google Pay
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #7f8c8d; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Вернуться к опроснику
            </a>
        </div>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
            <p>Оплата производится через платежную систему YooKassa.</p>
        </footer>
    </div>
    
    <script>
        document.getElementById('payBtn').addEventListener('click', function() {
            const btn = this;
            const errorDiv = document.getElementById('errorMessage');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Создание платежа...';
            errorDiv.style.display = 'none';
            
            const formData = new FormData();
            formData.append('action', 'create_payment');
            formData.append('return_url', window.location.origin + '/payment_success.php');
            
            fetch('create_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.already_paid) {
                        window.location.href = 'index.php';
                    } else if (data.confirmation_url) {
                        window.location.href = data.confirmation_url;
                    } else {
                        throw new Error('Не получен URL для оплаты');
                    }
                } else {
                    throw new Error(data.message || 'Ошибка при создании платежа');
                }
            })
            .catch(error => {
                errorDiv.textContent = error.message;
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-credit-card"></i> Оплатить <?php echo number_format(PAYMENT_AMOUNT, 0, ',', ' '); ?> ₽';
            });
        });
    </script>
</body>
</html>