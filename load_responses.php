<?php
include 'config.php';

header('Content-Type: application/json');

// Получаем статус завершённости каждого модуля
$completed_modules = [
    1 => isModuleCompleted(1),
    2 => isModuleCompleted(2),
    3 => isModuleCompleted(3),
    4 => isModuleCompleted(4)
];

// Если модуль завершен, убедимся, что ответы в сессии очищены
foreach ($completed_modules as $module => $completed) {
    if ($completed && !empty($_SESSION['responses'][$module])) {
        $_SESSION['responses'][$module] = [];
    }
}

echo json_encode([
    'success' => true,
    'responses' => $_SESSION['responses'],
    'completed_modules' => $completed_modules
]);
?>