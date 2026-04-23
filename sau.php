<?php
/**
 * Страница общей статистики (административная)
 * Доступна только если PAYMENT_ENABLED = false или через отдельную авторизацию
 */

include 'config.php';
include 'questions.php';

// Простая проверка на административный доступ
// Для безопасности можно добавить базовую HTTP-авторизацию
$admin_password = 'admin123'; // Установите свой пароль
if (isset($_GET['admin_pass']) && $_GET['admin_pass'] === $admin_password) {
    $_SESSION['admin_access'] = true;
}

if (!isset($_SESSION['admin_access']) || $_SESSION['admin_access'] !== true) {
    // Если пароль не передан, показываем форму входа
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Статистика - Административный доступ</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .admin-login {
                max-width: 400px;
                margin: 100px auto;
                padding: 30px;
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                text-align: center;
            }
            .admin-login h2 {
                margin-bottom: 20px;
                color: var(--primary);
            }
            .admin-login input {
                width: 100%;
                padding: 12px;
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 16px;
            }
            .admin-login button {
                width: 100%;
                padding: 12px;
                background: var(--secondary);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
            }
            .admin-login button:hover {
                background: var(--primary);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="admin-login">
                <h2>Доступ к статистике</h2>
                <form method="GET">
                    <input type="password" name="admin_pass" placeholder="Введите пароль" required>
                    <button type="submit">Войти</button>
                </form>
                <p style="margin-top: 15px; font-size: 12px; color: #666;">Для доступа введите пароль: admin123</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Получаем все файлы результатов
$all_results = [];
$results_files = glob('results/*.json');

foreach ($results_files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data && isset($data['survey_id'])) {
        $all_results[] = $data;
    }
}

// Группируем по уникальным сессиям
$unique_sessions = [];
foreach ($all_results as $result) {
    if (!in_array($result['survey_id'], $unique_sessions)) {
        $unique_sessions[] = $result['survey_id'];
    }
}

// Статистика по модулям
$module_stats = [];
for ($i = 1; $i <= 4; $i++) {
    $module_stats[$i] = [
        'total_completed' => 0,
        'results' => []
    ];
    
    foreach ($all_results as $result) {
        if ($result['module'] == $i) {
            $module_stats[$i]['total_completed']++;
            $module_stats[$i]['results'][] = $result;
        }
    }
}

// Получаем выбранный модуль для детального просмотра
$selected_module = isset($_GET['module']) ? intval($_GET['module']) : 1;
if ($selected_module < 1 || $selected_module > 4) $selected_module = 1;

// Получаем вопросы выбранного модуля
$module_questions = getQuestions($selected_module);
$total_questions = count($module_questions);

// Анализируем ответы по каждому вопросу
$question_stats = [];
for ($q = 1; $q <= $total_questions; $q++) {
    $question_stats[$q] = [
        'text' => $module_questions[$q] ?? 'Вопрос ' . $q,
        'answers' => [],
        'total_answers' => 0
    ];
}

// Собираем статистику по ответам
foreach ($module_stats[$selected_module]['results'] as $result) {
    $responses = $result['responses'];
    foreach ($responses as $q_num => $answer) {
        if (isset($question_stats[$q_num])) {
            $answer_key = (string)$answer;
            if (!isset($question_stats[$q_num]['answers'][$answer_key])) {
                $question_stats[$q_num]['answers'][$answer_key] = 0;
            }
            $question_stats[$q_num]['answers'][$answer_key]++;
            $question_stats[$q_num]['total_answers']++;
        }
    }
}

// Получаем варианты ответов для модуля
$answer_options = [];
$sample_question = 1;
$answer_options = getAnswerOptions($selected_module, $sample_question);

// Преобразуем для отображения
$answer_labels = [];
if ($selected_module == 1) {
    $answer_labels = [
        'true' => 'Верно',
        'false' => 'Неверно'
    ];
} elseif ($selected_module == 2) {
    // Для модуля 2 нужно определить для каждого вопроса
    // Используем оба типа
    $answer_labels = [
        '0' => '0 - Никогда',
        '1' => '1 - Редко',
        '2' => '2 - Иногда',
        '3' => '3 - Часто',
        '4' => '4 - Постоянно',
        '1a' => '1 - Согласен',
        '2a' => '2 - Скорее согласен',
        '3a' => '3 - Иногда',
        '4a' => '4 - Скорее не согласен',
        '5a' => '5 - Не согласен'
    ];
} elseif ($selected_module == 3) {
    $answer_labels = [
        '1' => '1 - Категорически не согласен',
        '2' => '2 - Скорее не согласен',
        '3' => '3 - Затрудняюсь ответить',
        '4' => '4 - Скорее согласен',
        '5' => '5 - Полностью согласен'
    ];
} elseif ($selected_module == 4) {
    $answer_labels = [
        '0' => '0 - Не согласен',
        '1' => '1 - Согласен'
    ];
}

// Функция для форматирования ответа
function formatAnswer($module, $q_num, $answer) {
    if ($module == 1) {
        return $answer == 'true' ? 'Верно' : 'Неверно';
    } elseif ($module == 2) {
        if ($q_num <= 60) {
            $labels = ['0' => '0 - Никогда', '1' => '1 - Редко', '2' => '2 - Иногда', '3' => '3 - Часто', '4' => '4 - Постоянно'];
            return $labels[(string)$answer] ?? $answer;
        } else {
            $labels = ['1' => '1 - Согласен', '2' => '2 - Скорее согласен', '3' => '3 - Иногда', '4' => '4 - Скорее не согласен', '5' => '5 - Не согласен'];
            return $labels[(string)$answer] ?? $answer;
        }
    } elseif ($module == 3) {
        $labels = ['1' => '1 - Категорически не согласен', '2' => '2 - Скорее не согласен', '3' => '3 - Затрудняюсь ответить', '4' => '4 - Скорее согласен', '5' => '5 - Полностью согласен'];
        return $labels[(string)$answer] ?? $answer;
    } elseif ($module == 4) {
        $labels = ['0' => '0 - Не согласен', '1' => '1 - Согласен'];
        return $labels[(string)$answer] ?? $answer;
    }
    return $answer;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Общая статистика - Опросник "РЕСУРС"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style-media.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <style>
        .stats-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stats-header h1 {
            color: var(--primary);
            font-size: 28px;
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        
        .summary-card .number {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .summary-card .label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .module-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .module-stat-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .module-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .module-stat-card.active {
            border-color: var(--secondary);
            background: #e3f2fd;
        }
        
        .module-stat-card h3 {
            margin: 0 0 10px 0;
            color: var(--primary);
        }
        
        .module-stat-card .count {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary);
        }
        
        .module-stat-card .label {
            font-size: 12px;
            color: var(--gray);
        }
        
        .questions-stats {
            margin-top: 30px;
        }
        
        .question-stat-item {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--secondary);
        }
        
        .question-stat-item .question-text {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 16px;
            color: var(--primary);
        }
        
        .answers-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 10px;
        }
        
        .answer-stat {
            flex: 1;
            min-width: 150px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .answer-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .answer-bar {
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 8px 0;
        }
        
        .answer-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary), #5dade2);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            transition: width 0.5s ease;
        }
        
        .answer-count {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--secondary);
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .module-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .module-btn-stats {
            padding: 8px 20px;
            background: #f0f0f0;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .module-btn-stats.active {
            background: var(--secondary);
            color: white;
        }
        
        @media (max-width: 768px) {
            .module-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .answers-stats {
                flex-direction: column;
            }
            
            .stats-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="stats-container">
            <div class="stats-header">
                <h1><i class="fas fa-chart-line"></i> Общая статистика опросника "РЕСУРС"</h1>
                <a href="index.php" class="action-btn" style="background: var(--gray);">
                    <i class="fas fa-arrow-left"></i> На главную
                </a>
            </div>
            
            <!-- Общая статистика -->
            <div class="stats-summary">
                <div class="summary-card">
                    <div class="number"><?php echo count($unique_sessions); ?></div>
                    <div class="label">Всего уникальных пользователей</div>
                </div>
                <div class="summary-card">
                    <div class="number"><?php echo count($all_results); ?></div>
                    <div class="label">Всего завершенных модулей</div>
                </div>
                <div class="summary-card">
                    <div class="number">
                        <?php 
                        $total_completed = 0;
                        for ($i = 1; $i <= 4; $i++) {
                            $total_completed += $module_stats[$i]['total_completed'];
                        }
                        echo $total_completed;
                        ?>
                    </div>
                    <div class="label">Среднее прохождений на модуль</div>
                </div>
            </div>
            
            <!-- Статистика по модулям -->
            <h3 style="margin-bottom: 15px;">Статистика по модулям</h3>
            <div class="module-stats-grid">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="module-stat-card <?php echo $selected_module == $i ? 'active' : ''; ?>" onclick="location.href='?module=<?php echo $i; ?>&admin_pass=<?php echo htmlspecialchars($_GET['admin_pass'] ?? ''); ?>'">
                        <h3>Модуль <?php echo $i; ?></h3>
                        <div class="count"><?php echo $module_stats[$i]['total_completed']; ?></div>
                        <div class="label">прохождений</div>
                    </div>
                <?php endfor; ?>
            </div>
            
            <!-- Детальная статистика по вопросам выбранного модуля -->
            <h3 style="margin-bottom: 15px;">
                Детальная статистика по вопросам модуля <?php echo $selected_module; ?>
                <small style="font-size: 12px; color: var(--gray); margin-left: 10px;">
                    (<?php echo $module_stats[$selected_module]['total_completed']; ?> прохождений)
                </small>
            </h3>
            
            <?php if ($module_stats[$selected_module]['total_completed'] == 0): ?>
                <div class="no-data">
                    <i class="fas fa-database" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>Нет данных для этого модуля. Пока никто не прошел этот модуль.</p>
                </div>
            <?php else: ?>
                <div class="questions-stats">
                    <?php for ($q = 1; $q <= $total_questions; $q++): 
                        $stats = $question_stats[$q];
                        $total_answers = $stats['total_answers'];
                        $answers_data = $stats['answers'];
                        $question_text = $stats['text'];
                    ?>
                        <div class="question-stat-item">
                            <div class="question-text">
                                <span style="background: var(--secondary); color: white; padding: 2px 8px; border-radius: 15px; margin-right: 10px;"><?php echo $q; ?></span>
                                <?php echo htmlspecialchars($question_text); ?>
                            </div>
                            <div class="answers-stats">
                                <?php 
                                // Получаем все возможные варианты ответов для этого вопроса
                                $possible_answers = getAnswerOptions($selected_module, $q);
                                if (empty($possible_answers)) {
                                    $possible_answers = $answer_labels;
                                }
                                
                                foreach ($possible_answers as $value => $label): 
                                    $count = $answers_data[(string)$value] ?? 0;
                                    $percentage = $total_answers > 0 ? round(($count / $total_answers) * 100) : 0;
                                ?>
                                    <div class="answer-stat">
                                        <div class="answer-label"><?php echo htmlspecialchars($label); ?></div>
                                        <div class="answer-bar">
                                            <div class="answer-fill" style="width: <?php echo $percentage; ?>%;">
                                                <?php if ($percentage > 15): ?><?php echo $percentage; ?>%<?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="answer-count">
                                            <?php echo $count; ?> выборов (<?php echo $percentage; ?>%)
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            
            <!-- Дополнительная информация -->
            <div style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 10px;">
                <h4><i class="fas fa-info-circle"></i> Информация</h4>
                <p style="font-size: 14px; color: #666;">
                    • Статистика основана на <?php echo count($all_results); ?> сохраненных результатах.<br>
                    • Уникальных пользователей: <?php echo count($unique_sessions); ?><br>
                    • Данные обновляются в реальном времени по мере прохождения тестов.<br>
                    • Для просмотра статистики другого модуля нажмите на соответствующую карточку.
                </p>
            </div>
        </div>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
            <p>Статистическая страница администратора</p>
        </footer>
    </div>
</body>
</html>