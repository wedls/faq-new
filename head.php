<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личностный опросник "РЕСУРС"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style-media.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <i class="fas fa-brain"></i>
                <div>
                    <h1>Личностный опросник "РЕСУРС"</h1>
                    <p class="subtitle">Комплексная оценка личности, психоэмоционального состояния и профессиональной направленности</p>
                </div>
            </div>
            <div class="session-info">
                <small>Сессия: <?php echo substr($_SESSION['survey_id'], 0, 10); ?>...</small>
            </div>
        </header>

        <nav class="module-nav">
            <button class="module-btn active" data-module="1">
                <i class="fas fa-heartbeat"></i>
                <span>Модуль 1</span>
                <small>Психоэмоциональное состояние</small>
            </button>
            <button class="module-btn" data-module="2">
                <i class="fas fa-stress-ball"></i>
                <span>Модуль 2</span>
                <small>Стресс и напряжение</small>
            </button>
            <button class="module-btn" data-module="3">
                <i class="fas fa-briefcase"></i>
                <span>Модуль 3</span>
                <small>Профориентация</small>
            </button>
        </nav>