<?php
include 'config.php';
include 'questions.php';

// ИСПРАВЛЕНО: используем универсальную функцию для получения результатов
// Теперь она возвращает только результаты текущего пользователя по survey_id
$results = getAllResultsForSession();

// Сортируем по дате (новые сверху)
usort($results, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты опросника "РЕСУРС"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style-media.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .header {
            min-height: 140px;
            position: relative;
            overflow: hidden;
        }
        
        .logo {
            position: relative;
            z-index: 2;
        }
        
        .cont-flag {
            position: relative;
            z-index: 2;
        }
        
        .cont-flag img {
            width: 100%;
            max-width: 169px;
            display: block;
        }
        
        /* Декоративные круги */
        .circle-decor {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }
        
        .circle-decor-outer {
            width: 331px;
            height: 359px;
            background: rgba(205, 163, 151, 1);
            top: -70px;
            right: -50px;
        }
        
        .circle-decor-inner {
            width: 254px;
            height: 291px;
            background: rgba(229, 184, 171, 1);
            top: -35px;
            right: -50px;
        }
        
        /* Адаптивность для мобильных устройств */
        @media (max-width: 768px) {
            .cont-flag img {
                max-width: 300px;
            }
        }
        
        .module-status {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
        }
        
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .status-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #eee;
        }
        
        .status-card.completed {
            border-color: var(--success);
            background: #d4edda;
        }
        
        .status-card.pending {
            border-color: var(--warning);
            background: #fff3cd;
        }
        
        .status-card h4 {
            margin: 0 0 10px 0;
            color: var(--primary);
        }
        
        .status-card p {
            margin: 0;
            font-weight: bold;
        }
        
        .completion-message {
            text-align: center;
            padding: 50px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }
        
        .completion-icon {
            font-size: 5em;
            margin-bottom: 20px;
            color: #f1c40f;
        }
        
        .completion-message h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        
        .completion-message p {
            font-size: 1.2em;
            margin-bottom: 15px;
            opacity: 0.9;
            line-height: 1.8;
        }
        
        /* Стили для кнопки скачивания */
        .download-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .download-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .result-info {
            flex-grow: 1;
        }
        
        .result-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .file-link {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }
        
        /* Стили для отображения интерпретаций */
        .interpretation-text {
            font-style: italic;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.5;
            border-left: 3px solid var(--secondary);
        }
        
        .scale-result {
            margin-bottom: 12px;
            padding: 12px 15px;
        }
        
        .scale-result p {
            margin-top: 8px;
            margin-bottom: 0;
        }
        
        .scale-result em {
            font-style: normal;
            display: block;
            margin-top: 5px;
        }
        
        .scale-result.suicidal {
            border-left-color: var(--danger);
            background: #fee;
        }
        
        .scale-result.ptsd {
            border-left-color: var(--warning);
            background: #fff3cd;
        }
        
        .scale-result.total {
            border-left-color: var(--primary);
            background: #e2e3e5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <!-- Декоративные круги -->
            <div class="circle-decor circle-decor-outer"></div>
            <div class="circle-decor circle-decor-inner"></div>
            
            <div class="logo">
                <div>
                    <h1>Результаты опросника "РЕСУРС"</h1>
                </div>
            </div>

            <div class="cont-flag">
                <img src="img/flag.png" alt="Флаг">
            </div>
        </header>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="index.php" class="action-btn" style="display: inline-block;">
                <i class="fas fa-arrow-left"></i> Вернуться к опроснику
            </a>
        </div>
        
        <?php if (empty($results)): ?>
            <div class="completion-message">
                <div class="completion-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h2>Результаты не найдены</h2>
                <p>Вы еще не завершили ни одного модуля опросника.</p>
                <p>Пройдите опросник для сохранения результатов.</p>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 30px;">
                <h2 style="color: var(--primary); margin-bottom: 20px;">
                    <i class="fas fa-list-check"></i> Сохраненные результаты (<?php echo count($results); ?>)
                </h2>
                
                <?php 
                $modules_completed = [1 => false, 2 => false, 3 => false, 4 => false];
                foreach ($results as $result) {
                    $modules_completed[$result['module']] = true;
                }
                ?>
                
                <div class="module-status">
                    <h3>Статус прохождения:</h3>
                    <div class="status-cards">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="status-card <?php echo $modules_completed[$i] ? 'completed' : 'pending'; ?>">
                                <h4>Модуль <?php echo $i; ?></h4>
                                <p><?php echo $modules_completed[$i] ? '✓ Завершен' : '⏳ Не завершен'; ?></p>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <?php foreach ($results as $index => $result): ?>
                    <div class="question-container" style="margin-bottom: 20px;">
                        <div class="result-header">
                            <div class="result-info">
                                <div class="question-header">
                                    <div class="question-number"><?php echo $index + 1; ?></div>
                                    <div style="flex-grow: 1;">
                                        <h3 style="margin-bottom: 10px;">
                                            <?php 
                                            $module_title = isset($module_titles[$result['module']]['title']) 
                                                ? $module_titles[$result['module']]['title'] 
                                                : 'Модуль ' . $result['module'];
                                            echo $module_title; 
                                            ?>
                                            <small style="color: var(--gray);">(<?php echo $result['timestamp']; ?>)</small>
                                        </h3>
                                        <p style="margin-bottom: 10px;">
                                            <strong>Статистика:</strong> 
                                            <?php echo $result['answered_questions']; ?> из <?php echo $result['total_questions']; ?> вопросов 
                                            (<?php echo round(($result['answered_questions'] / $result['total_questions']) * 100); ?>%)
                                        </p>
                                        <div class="file-link">
                                            <i class="fas fa-file-alt"></i> Файл: <?php echo basename($result['filename'] ?? ''); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="result-actions">
                                <a href="download_pdf.php?file=<?php echo urlencode($result['filename'] ?? ''); ?>" class="download-btn" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Скачать PDF
                                </a>
                            </div>
                        </div>
                        
                        <details style="margin-top: 15px;">
                            <summary style="cursor: pointer; color: var(--secondary); font-weight: 600; padding: 10px;">
                                <i class="fas fa-chart-line"></i> Показать результаты тестирования
                            </summary>
                            <div style="margin-top: 15px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                                <?php if (isset($result['results'])): ?>
                                    <?php 
                                    $results_data = $result['results'];
                                    if ($result['module'] == 1) {
                                        echo '<h4>Результаты модуля 1: Актуальное психоэмоциональное состояние</h4>';
                                        
                                        // Шкала лжи
                                        if (isset($results_data['lie_raw_score']) && isset($results_data['lie_index'])) {
                                            echo '<div class="scale-result">';
                                            echo '<strong>Шкала искренности (L):</strong><br>';
                                            echo 'Сырой балл: ' . $results_data['lie_raw_score'] . '/11<br>';
                                            echo 'Индекс (L = N/11): ' . $results_data['lie_index'] . '<br>';
                                            echo $results_data['lie_index'] < 0.6 ? '✅ Результаты относительно достоверны' : '⚠️ Тенденция к искажению действительности';
                                            if (isset($results_data['lie_interpretation'])) {
                                                echo '<div class="interpretation-text">' . $results_data['lie_interpretation'] . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                        
                                        // Основные шкалы
                                        $scales = [
                                            'psychosomatic' => 'Психосоматические риски',
                                            'anxiety' => 'Тревога',
                                            'demonstrative' => 'Демонстративность',
                                            'frustration' => 'Фрустрационные установки',
                                            'sensitivity' => 'Сензитивность',
                                            'distancing' => 'Дистанцирование',
                                            'hypomania' => 'Гипомания',
                                            'extraversion' => 'Экстраверсия-Интроверсия',
                                            'emotional_intelligence' => 'Эмоциональный интеллект'
                                        ];
                        
                                        foreach ($scales as $key => $name) {
                                            if (isset($results_data[$key])) {
                                                $score = $results_data[$key];
                                                $level = '';
                                                if ($score >= 9) $level = 'Высокий уровень';
                                                elseif ($score >= 7) $level = 'Тенденция к высокому';
                                                elseif ($score >= 4) $level = 'Средний уровень';
                                                else $level = 'Низкий уровень';
                                                
                                                echo '<div class="scale-result">';
                                                echo '<strong>' . $name . ':</strong> ' . $score . '/10 баллов - ' . $level;
                                                if (isset($results_data[$key . '_interpretation'])) {
                                                    echo '<div class="interpretation-text">' . $results_data[$key . '_interpretation'] . '</div>';
                                                }
                                                echo '</div>';
                                            }
                                        }
                                        
                                        // Суицидальная шкала
                                        if (isset($results_data['suicidal'])) {
                                            $score = $results_data['suicidal'];
                                            echo '<div class="scale-result suicidal">';
                                            echo '<strong>Склонность к суицидальному поведению:</strong> ' . $score . '/100 баллов<br>';
                                            if (isset($results_data['suicidal_interpretation'])) {
                                                echo '<div class="interpretation-text">' . $results_data['suicidal_interpretation'] . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                        
                                    } elseif ($result['module'] == 2) {
                                        echo '<h4>Результаты модуля 2: Диагностика психического напряжения и стрессовых расстройств</h4>';
                                        
                                        if (isset($results_data['part1'])) {
                                            echo '<h5>Часть 1: Индекс психического напряжения (ИПН)</h5>';
                                            
                                            $blocks = [
                                                'emotional' => 'Эмоциональный дискомфорт',
                                                'cognitive' => 'Когнитивные трудности',
                                                'physical' => 'Физиологическое напряжение',
                                                'behavioral' => 'Поведенческие нарушения'
                                            ];
                                            
                                            foreach ($blocks as $key => $name) {
                                                if (isset($results_data['part1'][$key])) {
                                                    $score = $results_data['part1'][$key];
                                                    $level = $score <= 20 ? 'Низкий' : ($score <= 40 ? 'Средний' : 'Высокий');
                                                    echo '<div class="scale-result">';
                                                    echo '<strong>' . $name . ':</strong> ' . $score . '/60 баллов - ' . $level . ' уровень';
                                                    if (isset($results_data['part1'][$key . '_interpretation'])) {
                                                        echo '<div class="interpretation-text">' . $results_data['part1'][$key . '_interpretation'] . '</div>';
                                                    }
                                                    echo '</div>';
                                                }
                                            }
                                            
                                            if (isset($results_data['part1']['total'])) {
                                                $total = $results_data['part1']['total'];
                                                $level = $total <= 80 ? 'Низкий' : ($total <= 160 ? 'Средний' : 'Высокий');
                                                echo '<div class="scale-result total">';
                                                echo '<strong>Общий индекс ИПН:</strong> ' . $total . '/240 баллов - ' . $level . ' уровень напряжения';
                                                if (isset($results_data['part1']['total_interpretation'])) {
                                                    echo '<div class="interpretation-text">' . $results_data['part1']['total_interpretation'] . '</div>';
                                                }
                                                echo '</div>';
                                            }
                                        }
                                        
                                        if (isset($results_data['part2'])) {
                                            $score = $results_data['part2'];
                                            echo '<div class="scale-result ptsd">';
                                            echo '<strong>Часть 2: Оценка посттравматического стресса:</strong> ' . $score . ' баллов<br>';
                                            if (isset($results_data['part2_interpretation'])) {
                                                echo '<div class="interpretation-text">' . $results_data['part2_interpretation'] . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                        
                                    } elseif ($result['module'] == 3) {
                                        echo '<h4>Результаты модуля 3: Профессиональные профили и адаптивность</h4>';
                                        
                                        $profiles = [
                                            'organizational' => 'Организационный профиль',
                                            'analytical' => 'Аналитический профиль',
                                            'creative' => 'Творческий профиль',
                                            'communicative' => 'Коммуникативный профиль',
                                            'scientific' => 'Научный профиль',
                                            'technical' => 'Практико-технический профиль',
                                            'self_development' => 'Саморазвитие и обучение'
                                        ];
                                        
                                        foreach ($profiles as $key => $name) {
                                            if (isset($results_data[$key])) {
                                                $score = $results_data[$key];
                                                $level = '';
                                                $color = '';
                                                
                                                if ($score <= 8) {
                                                    $level = 'Низкая выраженность';
                                                    $color = '#e74c3c';
                                                } elseif ($score <= 15) {
                                                    $level = 'Средняя выраженность';
                                                    $color = '#f39c12';
                                                } else {
                                                    $level = 'Высокая выраженность';
                                                    $color = '#27ae60';
                                                }
                                                
                                                echo '<div class="scale-result">';
                                                echo '<strong>' . $name . ':</strong> ';
                                                echo '<span style="color: ' . $color . '">' . $score . ' баллов - ' . $level . '</span>';
                                                if (isset($results_data[$key . '_interpretation'])) {
                                                    echo '<div class="interpretation-text">' . $results_data[$key . '_interpretation'] . '</div>';
                                                }
                                                echo '</div>';
                                            }
                                        }
                                        
                                        if (isset($results_data['recommendations']) && is_array($results_data['recommendations'])) {
                                            echo '<h5>Рекомендации:</h5>';
                                            foreach ($results_data['recommendations'] as $rec) {
                                                echo '<p style="margin-bottom: 8px;">• ' . $rec . '</p>';
                                            }
                                        }

                                    } elseif ($result['module'] == 4) {
                                        echo '<h4>Результаты модуля 4: Склонность к употреблению ПАВ</h4>';
                                        
                                        if (isset($results_data['total_score'])) {
                                            $score = $results_data['total_score'];
                                            $interpretation = $results_data['interpretation'] ?? '';
                                            $recommendation = $results_data['recommendation'] ?? '';
                                            
                                            echo '<div style="font-size: 18px; text-align: center; margin-bottom: 20px; padding: 10px; background: #e9ecef; border-radius: 8px;">';
                                            echo '<strong>Общий балл: ' . $score . ' из 30</strong>';
                                            echo '</div>';
                                            
                                            $bg_class = '';
                                            $border_color = '';
                                            if ($score <= 13) {
                                                $bg_class = '#d4edda';
                                                $border_color = '#27ae60';
                                            } elseif ($score <= 16) {
                                                $bg_class = '#fff3cd';
                                                $border_color = '#f39c12';
                                            } else {
                                                $bg_class = '#fee';
                                                $border_color = '#e74c3c';
                                            }
                                            
                                            echo '<div class="scale-result" style="border-left-color: ' . $border_color . '; background: ' . $bg_class . ';">';
                                            echo '<h4 style="margin-bottom: 10px; color: #333;">' . $interpretation . ' (' . $score . ' баллов)</h4>';
                                            echo '<div class="interpretation-text">' . nl2br($recommendation) . '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                <?php else: ?>
                                    <p>Результаты обработки не найдены в этом файле.</p>
                                <?php endif; ?>
                            </div>
                        </details>
                        
                        <details style="margin-top: 10px;">
                            <summary style="cursor: pointer; color: var(--gray); font-weight: 500; padding: 8px;">
                                <i class="fas fa-eye"></i> Показать ответы на вопросы
                            </summary>
                            <div style="margin-top: 10px; padding: 15px; background: #f0f0f0; border-radius: 8px; overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                    <thead>
                                        <tr style="background: var(--primary); color: white;">
                                            <th style="padding: 10px; text-align: left; width: 50px;">№</th>
                                            <th style="padding: 10px; text-align: left;">Вопрос</th>
                                            <th style="padding: 10px; text-align: left; width: 150px;">Ответ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $module_questions = getQuestions($result['module']);
                                        $display_responses = $result['responses'];
                                        if (is_array($display_responses)) {
                                            ksort($display_responses);
                                        }
                                        
                                        foreach ($display_responses as $q_num => $answer): 
                                            $question_text = $module_questions[$q_num] ?? 'Вопрос ' . $q_num;
                                        ?>
                                        <tr style="border-bottom: 1px solid #ddd;">
                                            <td style="padding: 10px; vertical-align: top;"><?php echo $q_num; ?></td>
                                            <td style="padding: 10px; vertical-align: top;"><?php echo htmlspecialchars($question_text); ?></td>
                                            <td style="padding: 10px; vertical-align: top;">
                                                <?php 
                                                $display_answer = $answer;
                                                if ($result['module'] == 1) {
                                                    $display_answer = ($answer == 'true') ? 'Верно' : 'Неверно';
                                                } elseif ($result['module'] == 2) {
                                                    if ($q_num <= 60) {
                                                        $labels = ['0' => '0 - Никогда', '1' => '1 - Редко', '2' => '2 - Иногда', '3' => '3 - Часто', '4' => '4 - Постоянно'];
                                                        $display_answer = $labels[(string)$answer] ?? $answer;
                                                    } else {
                                                        $labels = ['1' => 'Согласен', '2' => 'Скорее согласен', '3' => 'Иногда', '4' => 'Скорее не согласен', '5' => 'Не согласен'];
                                                        $display_answer = $labels[(string)$answer] ?? $answer;
                                                    }
                                                } elseif ($result['module'] == 3) {
                                                    $labels = ['1' => '1 - Категорически не согласен', '2' => '2 - Скорее не согласен', '3' => '3 - Затрудняюсь ответить', '4' => '4 - Скорее согласен', '5' => '5 - Полностью согласен'];
                                                    $display_answer = $labels[(string)$answer] ?? $answer;
                                                } elseif ($result['module'] == 4) {
                                                    $labels = ['0' => '0 - Не согласен', '1' => '1 - Согласен'];
                                                    $display_answer = $labels[(string)$answer] ?? $answer;
                                                }
                                                
                                                echo htmlspecialchars($display_answer ?? '');
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
            <a href="index.php" class="action-btn" style="display: inline-block;">
                <i class="fas fa-redo"></i> Вернуться к опроснику
            </a>
        </div>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
            <p>Опросник предназначен для профессионального использования психологами.</p>
            <p>Для интерпретации результатов рекомендуется консультация психолога.</p>
        </footer>
    </div>
</body>
</html>