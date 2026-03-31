<?php
// Устанавливаем временную зону
date_default_timezone_set('Europe/Moscow');

// ========== НАСТРОЙКИ ОПЛАТЫ ==========
// Установите PAYMENT_ENABLED = false, чтобы отключить оплату (бесплатный доступ)
// Установите PAYMENT_ENABLED = true, чтобы включить оплату через YooKassa
define('PAYMENT_ENABLED', false);  // false - бесплатно, true - платно

// Настройки YooKassa (актуальны только если PAYMENT_ENABLED = true)
// Получите эти данные в личном кабинете YooKassa: https://yookassa.ru/my
// Для тестов: shopId = 000000, secretKey = 'test_...'
define('YOOKASSA_SHOP_ID', 'ВАШ_SHOP_ID');        // Замените на ваш shopId
define('YOOKASSA_SECRET_KEY', 'ВАШ_SECRET_KEY');  // Замените на ваш secret key
define('PAYMENT_AMOUNT', 100.00);                 // Стоимость доступа ко всем модулям (руб)
define('PAYMENT_DESCRIPTION', 'Доступ к опроснику "РЕСУРС" (все 4 модуля)');
// ================================================

// Настройки сессии для хранения 24 часа
$session_lifetime = 24 * 60 * 60;

ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), $_COOKIE[session_name()], time() + $session_lifetime, '/');
}

// Создаем папку для результатов
if (!is_dir('results')) {
    mkdir('results', 0777, true);
    file_put_contents('results/.htaccess', "Deny from all\n");
}

// Инициализируем статус оплаты в сессии, если его нет
if (!isset($_SESSION['payment_status'])) {
    $_SESSION['payment_status'] = 'pending'; // pending, paid, failed
}

if (!isset($_SESSION['payment_id'])) {
    $_SESSION['payment_id'] = null;
}

$module_titles = [
    1 => [
        "title" => "Модуль 1",
        "description" => "Актуальное психоэмоциональное состояние, особенности восприятия и мышления"
    ],
    2 => [
        "title" => "Модуль 2",
        "description" => "Диагностика психического напряжения и стрессовых расстройств"
    ],
    3 => [
        "title" => "Модуль 3",
        "description" => "Профориентация и адаптивность"
    ],
    4 => [
        "title" => "Модуль 4",
        "description" => "Выявление склонности к употреблению ПАВ"
    ]
];

$questions_per_module = [
    1 => 136,
    2 => 95,
    3 => 30,
    4 => 30
];

if (empty($_SESSION['survey_id'])) {
    $_SESSION['survey_id'] = uniqid('survey_', true);
}

if (!isset($_SESSION['responses'])) {
    $_SESSION['responses'] = [
        1 => [],
        2 => [],
        3 => [],
        4 => []
    ];
}

/**
 * Проверка, оплачен ли доступ
 * Если оплата отключена (PAYMENT_ENABLED = false), всегда возвращает true
 */
function isPaymentPaid() {
    if (!PAYMENT_ENABLED) {
        // Если оплата отключена, доступ всегда бесплатный
        return true;
    }
    return isset($_SESSION['payment_status']) && $_SESSION['payment_status'] === 'paid';
}

/**
 * Проверка, доступен ли модуль (только если оплачено или оплата отключена)
 */
function isModuleAccessible($module) {
    return isPaymentPaid();
}

/**
 * Получение всех файлов результатов ТОЛЬКО для текущего survey_id
 * ИСПРАВЛЕНО: Теперь строго фильтруем по survey_id из сессии
 */
function getAllResultsForSession() {
    $results = [];
    $current_survey_id = $_SESSION['survey_id'] ?? null;
    
    if ($current_survey_id) {
        // Ищем файлы с точным совпадением survey_id в имени
        $pattern = 'results/module_*_' . preg_quote($current_survey_id, '/') . '_*.json';
        $files = glob($pattern);
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['survey_id']) && $data['survey_id'] === $current_survey_id) {
                $data['filename'] = basename($file);
                $results[] = $data;
            }
        }
    }
    
    return $results;
}

/**
 * Проверка завершенности модуля
 */
function isModuleCompleted($module) {
    $results = getAllResultsForSession();
    foreach ($results as $result) {
        if ($result['module'] == $module) {
            return true;
        }
    }
    return false;
}

/**
 * Получение прогресса модуля
 */
function getModuleProgress($module) {
    $total = $GLOBALS['questions_per_module'][$module] ?? 0;
    $answered = count($_SESSION['responses'][$module] ?? []);
    $completed = isModuleCompleted($module);
    
    if ($completed) {
        $answered = $total;
        $percentage = 100;
    } else {
        $percentage = $total > 0 ? round(($answered / $total) * 100) : 0;
    }
    
    return [
        'total' => $total,
        'answered' => $answered,
        'percentage' => $percentage,
        'completed' => $completed
    ];
}

/**
 * Получение последних результатов модуля
 */
function getLastModuleResults($module) {
    $results = getAllResultsForSession();
    $module_results = [];
    
    foreach ($results as $result) {
        if ($result['module'] == $module) {
            $module_results[] = $result;
        }
    }
    
    if (empty($module_results)) {
        return null;
    }
    
    usort($module_results, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $module_results[0];
}

// Ключи для подсчета баллов (Модуль 1)
$module1_keys = [
    'lie' => [
        'true' => [24, 26, 39, 46, 51, 56, 69, 81, 104, 136],
        'false' => [95]
    ],
    'psychosomatic' => [
        'true' => [1, 17, 35, 50, 64, 78, 117, 127],
        'false' => [93, 106]
    ],
    'anxiety' => [
        'true' => [3, 19, 94, 108, 119],
        'false' => [36, 52, 65, 80, 128]
    ],
    'demonstrative' => [
        'true' => [5, 21, 38, 54, 66, 82],
        'false' => [96, 109, 120, 129]
    ],
    'frustration' => [
        'true' => [7, 23, 40, 55, 68, 84, 98, 110, 121, 130]
    ],
    'sensitivity' => [
        'true' => [9, 25, 42, 57, 70, 86, 100],
        'false' => [112, 122, 131]
    ],
    'distancing' => [
        'true' => [11, 27, 43, 58, 72, 87, 101, 113, 123, 132]
    ],
    'hypomania' => [
        'true' => [12, 29, 45, 60, 73, 89, 102, 114, 124, 133]
    ],
    'extraversion' => [
        'true' => [13, 31, 47, 61, 75, 90, 103, 115, 125, 134],
        'false' => []
    ],
    'emotional_intelligence' => [
        'true' => [15, 33, 49, 62, 76, 91, 105, 116, 126, 135]
    ],
    'suicidal' => [
        'true' => [2, 4, 6, 8, 14, 22, 28, 30, 32, 34, 41, 44, 53, 63, 74, 79, 83, 85, 88, 92, 97, 99, 107, 118],
        'false' => [10, 16, 18, 20, 37, 48, 59, 67, 71, 77, 111]
    ]
];
?>