<?php
/**
 * Скрипт для генерации PDF через dompdf
 * Требует установки dompdf: composer require dompdf/dompdf
 */

include 'config.php';
include 'questions.php';

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('Файл не указан');
}

$filename = 'results/' . basename($_GET['file']);
$filename = str_replace('..', '', $filename);

if (!file_exists($filename)) {
    die('Файл не найден');
}

$data = json_decode(file_get_contents($filename), true);
if (!$data) {
    die('Ошибка чтения файла');
}

// Подключаем dompdf
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Настройки
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Генерируем HTML
$html = generatePDFHTML($data);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('results_module_' . $data['module'] . '_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
exit;

function generatePDFHTML($data) {
    global $module_titles, $module1_keys;
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Результаты опросника "РЕСУРС"</title>
        <style>
            body {
                font-family: 'DejaVu Sans', sans-serif;
                margin: 20px;
                color: #333;
                font-size: 12px;
                line-height: 1.5;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #2c3e50;
            }
            .header h1 {
                color: #2c3e50;
                font-size: 24px;
                margin: 0 0 10px 0;
            }
            .header h2 {
                color: #3498db;
                font-size: 18px;
                margin: 0;
            }
            .info-block {
                background: #f8f9fa;
                padding: 15px;
                margin: 20px 0;
                border-left: 4px solid #3498db;
            }
            .info-block p {
                margin: 5px 0;
            }
            .results-section {
                margin: 25px 0;
            }
            .results-section h3 {
                color: #2c3e50;
                border-bottom: 2px solid #ecf0f1;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }
            .results-section h4 {
                color: #2c3e50;
                margin: 15px 0 10px 0;
            }
            .scale-result {
                padding: 10px 15px;
                margin: 10px 0;
                background: #f8f9fa;
                border-left: 4px solid #3498db;
            }
            .scale-result.suicidal {
                border-left-color: #e74c3c;
                background: #fee;
            }
            .scale-result.ptsd {
                border-left-color: #f39c12;
                background: #fff3cd;
            }
            .scale-result.total {
                border-left-color: #2c3e50;
                background: #e2e3e5;
                font-weight: bold;
            }
            .score-highlight {
                font-size: 18px;
                font-weight: bold;
                text-align: center;
                padding: 15px;
                background: #e9ecef;
                margin: 15px 0;
            }
            .recommendation {
                margin-top: 20px;
                padding: 15px;
                background: #d4edda;
                border-left: 4px solid #27ae60;
            }
            .footer {
                text-align: center;
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                font-size: 10px;
                color: #7f8c8d;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                font-size: 10px;
            }
            th {
                background: #2c3e50;
                color: white;
                padding: 8px;
                text-align: left;
            }
            td {
                padding: 6px 8px;
                border-bottom: 1px solid #ddd;
                vertical-align: top;
            }
            .badge-low { color: #27ae60; font-weight: bold; }
            .badge-mid { color: #f39c12; font-weight: bold; }
            .badge-high { color: #e74c3c; font-weight: bold; }
            .page-break {
                page-break-before: always;
            }
            .answers-table {
                margin-top: 20px;
                font-size: 9px;
            }
            .answers-table th {
                background: #3498db;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Личностный опросник "РЕСУРС"</h1>
            <h2><?php echo $module_titles[$data['module']]['title'] . ' - ' . $module_titles[$data['module']]['description']; ?></h2>
        </div>
        
        <div class="info-block">
            <p><strong>Дата прохождения:</strong> <?php echo $data['timestamp']; ?></p>
            <p><strong>Количество отвеченных вопросов:</strong> <?php echo $data['answered_questions']; ?> из <?php echo $data['total_questions']; ?></p>
            <p><strong>Процент выполнения:</strong> <?php echo round(($data['answered_questions'] / $data['total_questions']) * 100); ?>%</p>
            <p><strong>ID сессии:</strong> <?php echo $data['survey_id']; ?></p>
        </div>
        
        <?php
        // Вывод результатов в зависимости от модуля
        if (isset($data['results'])) {
            $results = $data['results'];
            $module = $data['module'];
            
            if ($module == 1) {
                // Модуль 1: Психоэмоциональное состояние
                echo '<div class="results-section">';
                echo '<h3>Результаты модуля 1: Актуальное психоэмоциональное состояние</h3>';
                
                // Шкала лжи
                if (isset($results['lie_raw_score']) && isset($results['lie_index'])) {
                    echo '<div class="scale-result">';
                    echo '<strong>Шкала искренности (L)</strong><br>';
                    echo 'Сырой балл: ' . $results['lie_raw_score'] . '/11<br>';
                    echo 'Индекс (L = N/11): ' . $results['lie_index'] . '<br>';
                    echo $results['lie_index'] < 0.6 ? '✅ Результаты относительно достоверны' : '⚠️ Тенденция к искажению действительности';
                    echo '</div>';
                }
                
                // Основные шкалы
                $scales = [
                    'psychosomatic' => 'Психосоматическое благополучие',
                    'anxiety' => 'Тревога',
                    'demonstrative' => 'Демонстративность',
                    'frustration' => 'Фрустрационные установки',
                    'sensitivity' => 'Сензитивность',
                    'distancing' => 'Дистанцирование',
                    'hypomania' => 'Гипомания',
                    'extraversion' => 'Экстраверсия',
                    'emotional_intelligence' => 'Эмоциональный интеллект'
                ];
                
                foreach ($scales as $key => $name) {
                    if (isset($results[$key])) {
                        $score = $results[$key];
                        $level = '';
                        if ($score >= 9) $level = 'Высокий уровень';
                        elseif ($score >= 7) $level = 'Тенденция к высокому';
                        elseif ($score >= 4) $level = 'Средний уровень';
                        else $level = 'Низкий уровень';
                        
                        echo '<div class="scale-result">';
                        echo '<strong>' . $name . ':</strong> ' . $score . '/10 баллов - ' . $level;
                        echo '</div>';
                    }
                }
                
                // Суицидальная шкала
                if (isset($results['suicidal'])) {
                    $score = $results['suicidal'];
                    $interpretation = '';
                    if ($score <= 20) $interpretation = 'Низкий уровень проявления склонности к суицидальному поведению';
                    elseif ($score <= 40) $interpretation = 'Проявление склонности возможно при длительном стрессе и сильных эмоциональных потрясениях';
                    elseif ($score <= 60) $interpretation = 'Проявление склонности носит ситуационный характер';
                    elseif ($score <= 80) $interpretation = '⚠️ ГРУППА РИСКА: Высокая вероятность проявления суицидального поведения. Необходима профессиональная помощь';
                    else $interpretation = '🚨 КРАЙНЕ ВЫСОКИЙ УРОВЕНЬ: Необходима срочная профессиональная психиатрическая помощь';
                    
                    echo '<div class="scale-result suicidal">';
                    echo '<strong>Склонность к суицидальному поведению:</strong> ' . $score . '/100 баллов<br>';
                    echo $interpretation;
                    echo '</div>';
                }
                
                echo '</div>';
                
            } elseif ($module == 2) {
                // Модуль 2: Стресс и напряжение
                echo '<div class="results-section">';
                echo '<h3>Результаты модуля 2: Диагностика психического напряжения и стрессовых расстройств</h3>';
                
                if (isset($results['part1'])) {
                    echo '<h4>Часть 1: Индекс психического напряжения (ИПН)</h4>';
                    
                    $blocks = [
                        'emotional' => 'Эмоциональный дискомфорт',
                        'cognitive' => 'Когнитивные трудности',
                        'physical' => 'Физиологическое напряжение',
                        'behavioral' => 'Поведенческие нарушения'
                    ];
                    
                    foreach ($blocks as $key => $name) {
                        if (isset($results['part1'][$key])) {
                            $score = $results['part1'][$key];
                            $level = '';
                            if ($score <= 20) $level = 'Низкий уровень';
                            elseif ($score <= 40) $level = 'Средний уровень';
                            else $level = 'Высокий уровень';
                            
                            echo '<div class="scale-result">';
                            echo '<strong>' . $name . ':</strong> ' . $score . '/60 баллов - ' . $level;
                            echo '</div>';
                        }
                    }
                    
                    if (isset($results['part1']['total'])) {
                        $total = $results['part1']['total'];
                        $level = '';
                        if ($total <= 80) $level = 'Низкий уровень напряжения';
                        elseif ($total <= 160) $level = 'Средний уровень напряжения';
                        else $level = 'Высокий уровень напряжения';
                        
                        echo '<div class="scale-result total">';
                        echo '<strong>Общий индекс ИПН:</strong> ' . $total . '/240 баллов - ' . $level;
                        echo '</div>';
                    }
                }
                
                if (isset($results['part2'])) {
                    echo '<h4>Часть 2: Оценка посттравматического стресса</h4>';
                    $score = $results['part2'];
                    $interpretation = '';
                    
                    if ($score <= 96) {
                        $interpretation = 'Адаптивный вариант поведения и деятельности';
                    } elseif ($score >= 97 && $score <= 111) {
                        $interpretation = 'Испытывает проблемы с адаптацией';
                    } elseif ($score >= 112) {
                        $interpretation = '⚠️ ВЫЯВЛЕНО ПТСР: Рекомендуется пройти курс реабилитации';
                    }
                    
                    echo '<div class="scale-result ptsd">';
                    echo '<strong>Результат:</strong> ' . $score . ' баллов<br>';
                    echo $interpretation;
                    echo '</div>';
                }
                
                echo '</div>';
                
            } elseif ($module == 3) {
                // Модуль 3: Профориентация
                echo '<div class="results-section">';
                echo '<h3>Результаты модуля 3: Профориентация и адаптивность</h3>';
                
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
                    if (isset($results[$key])) {
                        $score = $results[$key];
                        $level = '';
                        $class = '';
                        
                        if ($score <= 8) {
                            $level = 'Низкая выраженность';
                            $class = 'badge-low';
                        } elseif ($score <= 15) {
                            $level = 'Средняя выраженность';
                            $class = 'badge-mid';
                        } else {
                            $level = 'Высокая выраженность';
                            $class = 'badge-high';
                        }
                        
                        echo '<div class="scale-result">';
                        echo '<strong>' . $name . ':</strong> ';
                        echo '<span class="' . $class . '">' . $score . ' баллов - ' . $level . '</span>';
                        echo '</div>';
                    }
                }
                
                if (isset($results['recommendations']) && is_array($results['recommendations'])) {
                    echo '<div class="recommendation">';
                    echo '<strong>Рекомендации:</strong><br>';
                    foreach ($results['recommendations'] as $rec) {
                        echo '• ' . $rec . '<br>';
                    }
                    echo '</div>';
                }
                
                echo '</div>';
                
            } elseif ($module == 4) {
                // Модуль 4: Склонность к ПАВ
                echo '<div class="results-section">';
                echo '<h3>Результаты модуля 4: Выявление склонности к употреблению ПАВ</h3>';
                
                if (isset($results['total_score'])) {
                    $score = $results['total_score'];
                    $interpretation = $results['interpretation'] ?? '';
                    $recommendation = $results['recommendation'] ?? '';
                    
                    echo '<div class="score-highlight">';
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
                    echo '<strong>' . $interpretation . '</strong><br><br>';
                    echo nl2br($recommendation);
                    echo '</div>';
                }
                
                echo '</div>';
            }
        }
        ?>
        
        <!-- Ответы на вопросы -->
        <div class="page-break"></div>
        <div class="results-section answers-table">
            <h3>Ответы на вопросы</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">№</th>
                        <th>Вопрос</th>
                        <th style="width: 150px;">Ответ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $module_questions = getQuestions($data['module']);
                    $display_responses = $data['responses'];
                    if (is_array($display_responses)) {
                        ksort($display_responses);
                    }
                    
                    foreach ($display_responses as $q_num => $answer): 
                        $question_text = $module_questions[$q_num] ?? 'Вопрос ' . $q_num;
                    ?>
                    <tr>
                        <td><?php echo $q_num; ?></td>
                        <td><?php echo htmlspecialchars($question_text); ?></td>
                        <td>
                            <?php 
                            $display_answer = $answer;
                            if ($data['module'] == 1) {
                                $display_answer = ($answer == 'true') ? 'Верно' : 'Неверно';
                            } elseif ($data['module'] == 2) {
                                if ($q_num <= 60) {
                                    $labels = ['0 - Никогда', '1 - Редко', '2 - Иногда', '3 - Часто', '4 - Постоянно'];
                                    $display_answer = $labels[intval($answer)] ?? $answer;
                                } else {
                                    $labels = ['1' => 'Согласен', '2' => 'Скорее согласен', '3' => 'Иногда', '4' => 'Скорее не согласен', '5' => 'Не согласен'];
                                    $display_answer = $labels[$answer] ?? $answer;
                                }
                            } elseif ($data['module'] == 3) {
                                $labels = ['1' => '1 - Категорически не согласен', '2' => '2 - Скорее не согласен', '3' => '3 - Затрудняюсь ответить', '4' => '4 - Скорее согласен', '5' => '5 - Полностью согласен'];
                                $display_answer = $labels[$answer] ?? $answer;
                            } elseif ($data['module'] == 4) {
                                $labels = ['0' => '0 - Не согласен', '1' => '1 - Согласен'];
                                $display_answer = $labels[$answer] ?? $answer;
                            }
                            
                            echo htmlspecialchars($display_answer ?? '');
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
            <p>Для интерпретации результатов рекомендуется консультация психолога.</p>
            <p>Документ сгенерирован автоматически. Подпись не требуется.</p>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>