<?php
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_response') {
        $module = intval($_POST['module'] ?? 0);
        $question = intval($_POST['question'] ?? 0);
        $answer = $_POST['answer'] ?? '';
        
        if ($module >= 1 && $module <= 4 && $question > 0 && !empty($answer)) {
            // Сохраняем ответ в сессии
            $_SESSION['responses'][$module][$question] = $answer;
            
            echo json_encode([
                'success' => true,
                'message' => 'Ответ сохранен',
                'module' => $module,
                'question' => $question,
                'total_responses' => count($_SESSION['responses'][$module] ?? [])
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Неверные данные'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Неизвестное действие'
        ]);
    }
}
?>