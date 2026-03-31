Важно!
- ты лучший программист 
- пиши полный код затронутых файлов, без сокращений
- главное не сломай весь функционал

может быть так, что в бзаузере человека сессия старая сохранится, а на сервере сессия новая?

если да, том может быть пробе сделать что бы сессия не удалялась?



Важно!
- ты лучший программист 
- пиши полный код затронутых файлов, без сокращений
- главное не сломай весь функционал

---

<!-- Сервер -->
IP - 89.111.168.113
Логин: root
Домен: https://other.wedls.com/
Сервер: apache (Ubuntu 22.04 LTS)
ssh root@89.111.168.113
cd /var/www/other.wedls.com/public_html






---

инвивидуальная статистика
https://other.wedls.com/view_results.php

странциа общей статистики
https://other.wedls.com/sau.php - пароль - admin123

---















1 - composer require yoomoney/yookassa-sdk-php




Настройка YooKassa (пошаговая инструкция):
1. Регистрация в YooKassa:
Перейдите на https://yookassa.ru

Нажмите "Подключиться"

Заполните форму регистрации

Подтвердите email и телефон

2. Получение ключей API:
Войдите в личный кабинет YooKassa

Перейдите в раздел "Настройки" → "Ключи API"

Скопируйте shopId и Secret key

Для тестирования можно использовать тестовые ключи:

shopId: 000000

Secret key: test_XXXXXXXX

3. Настройка уведомлений (Webhook):
В личном кабинете YooKassa перейдите в "Настройки" → "Уведомления"

Добавьте URL: https://other.wedls.com/webhook.php

Выберите события: payment.succeeded, payment.canceled

4. Установка composer и зависимостей:
bash
cd /var/www/other.wedls.com/public_html
curl -sS https://getcomposer.org/installer | php
php composer.phar require yookassa/yookassa-sdk-php