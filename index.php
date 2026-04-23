<?php
include 'config.php';

// Проверяем оплату - если оплата включена и не оплачено, показываем страницу оплаты
if (PAYMENT_ENABLED && !isPaymentPaid()) {
    header('Location: payment.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личностный опросник "РЕСУРС"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style-media.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
</head>
<body>
    <div class="container">
        <header class="header" style="height: 240px;">
            
            <div class="logo">
                <div class="text-logo">
                    <h1>Личностный опросник "РЕСУРС"</h1>
                    <p class="subtitle">Комплексная оценка личности, психоэмоционального состояния и профессиональной направленности</p>
                </div>
                <div class="hide-mobile">
                    <div class="logo-item">
                        <img src="img/logo.png" alt="Логотип" class="header-logo-img"/>
                        <div class="logo-text">ВИТА-КОУЧНИГ</div>
                    </div>
                </div>
            </div>
            <div class="header-logo-section">
                <div class="header-main-image">
                    <img src="img/main-i.png" alt="Основное изображение" class="main-img"/>
                </div>
            </div>

            <div class="hide-desktop position-logo-mob">
                <div class="logo-item">
                    <img src="img/logo.png" alt="Логотип" class="header-logo-img"/>
                    <div class="logo-text">ВИТА-КОУЧНИГ</div>
                </div>
            </div>


        </header>

        <nav class="module-nav">
            <?php 
            $progress1 = getModuleProgress(1);
            $progress2 = getModuleProgress(2);
            $progress3 = getModuleProgress(3);
            $progress4 = getModuleProgress(4);
            ?>
            <button class="module-btn active" data-module="1">
                <div class="icon-b <?php echo $progress1['completed'] ? 'on-completed' : 'off-completed'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_249_1135)">
                    <path d="M13.9964 14.8637C14.4847 15.3521 14.4847 16.1437 13.9964 16.6312C13.508 17.1196 12.7164 17.1196 12.2289 16.6312L7.38219 11.7846L6.20385 12.9629L10.698 17.4571C11.1864 17.9454 11.1864 18.7371 10.698 19.2246C10.2097 19.7129 9.41802 19.7129 8.93052 19.2246L5.03635 15.3304C2.57219 12.9262 0.0546875 9.72289 0.0546875 6.67122C0.0546875 3.45456 2.48552 0.837891 5.47135 0.837891C6.94885 0.837891 8.08052 1.38956 8.94219 2.00206L5.90969 5.00872C5.35802 5.55956 5.05469 6.29289 5.05469 7.07206C5.05469 7.85122 5.35802 8.58372 5.90802 9.13372C6.45885 9.68539 7.19135 9.98872 7.97135 9.98872C8.32635 9.98872 8.66885 9.91789 8.99219 9.79789L13.9964 14.8637ZM12.073 7.10872L10.3297 8.84039L15.4214 13.8554C15.9097 14.3437 16.7014 14.3437 17.1889 13.8554C17.9355 13.1087 17.2964 12.2821 17.2964 12.2821L12.0722 7.10872H12.073ZM14.603 0.886224C13.2414 0.894557 11.948 1.48622 10.9514 2.41456L7.08719 6.25289C6.59885 6.74122 6.59885 7.53289 7.08719 8.02039C7.57552 8.50872 8.36719 8.50872 8.85469 8.02039L12.1097 4.78706L18.5547 11.1854C19.5472 9.58372 20.0539 8.08372 20.0539 6.71956C20.0539 3.50289 17.5889 0.886224 14.6022 0.886224H14.603Z" fill="white"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_249_1135">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                </div>
                <span>Психоэмоциональное состояние</span>
                <small>Модуль 1</small>
                <div class="module-progress <?php echo $progress1['completed'] ? 'on-completed-percent' : 'off-completed-percent'; ?>" id="progress-module-1">
                    <?php echo $progress1['completed'] ? '100%' : ($progress1['percentage'] . '%'); ?>
                </div>
            </button>
            
            <button class="module-btn" data-module="2">
                <div class="icon-b <?php echo $progress2['completed'] ? 'on-completed' : 'off-completed'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_249_1144)">
                    <path d="M7.49502 5.32408C7.51335 5.44908 7.47752 5.57575 7.39669 5.67325C7.19085 5.91992 6.80002 5.87408 6.65669 5.58658L5.70502 3.65325L4.56919 4.85075C4.32835 5.09158 3.92335 5.03158 3.76252 4.73158L2.25252 1.71158C2.13002 1.46491 2.17919 1.16825 2.37335 0.973248L3.19002 0.157415C3.45085 -0.103418 3.89169 -0.0292517 4.05169 0.303248L4.85669 2.16992L6.04669 0.979915C6.32752 0.699082 6.80835 0.844082 6.88752 1.23325L7.49502 5.32408ZM12.5042 5.32408C12.4859 5.44908 12.5217 5.57575 12.6025 5.67325C12.8084 5.91992 13.1992 5.87408 13.3425 5.58658L14.2942 3.65325L15.43 4.85075C15.6709 5.09158 16.0759 5.03158 16.2367 4.73158L17.7467 1.71158C17.8692 1.46491 17.82 1.16825 17.6259 0.973248L16.81 0.157415C16.5492 -0.103418 16.1084 -0.0292517 15.9484 0.303248L15.1434 2.16992L13.9534 0.979915C13.6725 0.699082 13.1917 0.844082 13.1125 1.23325L12.5042 5.32408ZM19.0584 14.2832C18.6209 16.1999 16.8209 17.4991 14.8542 17.4991H10.8325V14.9991C10.8325 14.3966 10.7375 13.8041 10.5575 13.2399C10.3917 12.7207 9.75335 12.5241 9.29919 12.8266C8.99169 13.0316 8.85252 13.4141 8.96669 13.7657C9.09502 14.1616 9.16585 14.5749 9.16585 14.9991V19.1658C9.16585 19.6257 8.79252 19.9991 8.33252 19.9991C7.87252 19.9991 7.49919 19.6257 7.49919 19.1658V18.3082C6.82669 18.8149 6.00335 19.1324 5.10335 19.1549C2.74002 19.0982 0.83252 17.1666 0.83252 14.7907C0.83252 13.3791 1.52752 12.0466 2.67002 11.2307C3.16002 9.37325 4.70585 7.94908 6.60335 7.60158C7.37835 6.50158 8.64419 5.83325 10 5.83325C11.3559 5.83325 12.6159 6.50325 13.3925 7.60658C14.5017 7.82408 15.4792 8.43408 16.1559 9.33075C18.215 9.92408 19.5692 12.0457 19.0584 14.2832ZM15.8334 13.3324C15.8334 12.8716 15.46 12.4991 15 12.4991C14.54 12.4991 14.1667 12.1257 14.1667 11.6657C14.1667 11.2057 13.7934 10.8324 13.3334 10.8324C12.8734 10.8324 12.5 11.2049 12.5 11.6657C12.5 13.0441 13.6217 14.1657 15 14.1657C15.46 14.1657 15.8334 13.7932 15.8334 13.3324Z" fill="white"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_249_1144">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                </div>
                <span>Стресс и напряжение</span>
                <small>Модуль 2</small>
                <div class="module-progress <?php echo $progress2['completed'] ? 'on-completed-percent' : 'off-completed-percent'; ?>" id="progress-module-2">
                    <?php echo $progress2['completed'] ? '100%' : ($progress2['percentage'] . '%'); ?>
                </div>
            </button>
            
            <button class="module-btn" data-module="3">
                <div class="icon-b <?php echo $progress3['completed'] ? 'on-completed' : 'off-completed'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_249_1153)">
                    <path d="M19.39 8.52608L16.265 5.40108L14.5 7.16608L16.1166 8.78191H11.25V3.92191L12.8333 5.50525L14.6016 3.73691L11.4733 0.609412C11.2798 0.415898 11.0502 0.262392 10.7974 0.15766C10.5446 0.0529287 10.2736 -0.000976562 9.99997 -0.000976562C9.72634 -0.000976563 9.45539 0.0529287 9.20259 0.15766C8.94979 0.262392 8.7201 0.415898 8.52664 0.609412L5.40164 3.73441L7.16664 5.49941L8.74997 3.91608V8.77941H3.92247L5.50581 7.19608L3.73497 5.43691L0.610805 8.56108C0.417167 8.75456 0.263555 8.9843 0.15875 9.23717C0.0539443 9.49005 0 9.7611 0 10.0348C0 10.3086 0.0539443 10.5796 0.15875 10.8325C0.263555 11.0854 0.417167 11.3151 0.610805 11.5086L3.73581 14.6336L5.49997 12.8652L3.91664 11.2819H8.74997V16.0736L7.16664 14.4994L5.40164 16.2644L8.52664 19.3894C8.7201 19.5829 8.94979 19.7364 9.20259 19.8412C9.45539 19.9459 9.72634 19.9998 9.99997 19.9998C10.2736 19.9998 10.5446 19.9459 10.7974 19.8412C11.0502 19.7364 11.2798 19.5829 11.4733 19.3894L14.5983 16.2644L12.8333 14.4994L11.25 16.0827V11.2911H16.0416L14.5 12.8327L16.2683 14.6011L19.3933 11.4761C19.5871 11.2823 19.7408 11.0523 19.8455 10.799C19.9503 10.5458 20.004 10.2744 20.0037 10.0004C20.0034 9.72636 19.949 9.45508 19.8437 9.20209C19.7384 8.94911 19.5842 8.71938 19.39 8.52608Z" fill="white"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_249_1153">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                </div>
                <span>Профориентация</span>
                <small>Модуль 3</small>
                <div class="module-progress <?php echo $progress3['completed'] ? 'on-completed-percent' : 'off-completed-percent'; ?>" id="progress-module-3">
                    <?php echo $progress3['completed'] ? '100%' : ($progress3['percentage'] . '%'); ?>
                </div>
            </button>

            <button class="module-btn" data-module="4">
                <div class="icon-b <?php echo $progress4['completed'] ? 'on-completed' : 'off-completed'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 3C11.66 3 13 4.34 13 6C13 7.66 11.66 9 10 9C8.34 9 7 7.66 7 6C7 4.34 8.34 3 10 3ZM10 17.2C7.5 17.2 5.29 15.92 4 13.98C4.03 11.99 8 10.9 10 10.9C11.99 10.9 15.97 11.99 16 13.98C14.71 15.92 12.5 17.2 10 17.2Z" fill="white"/>
                    </svg>
                </div>
                <span>Склонность к ПАВ</span>
                <small>Модуль 4</small>
                <div class="module-progress <?php echo $progress4['completed'] ? 'on-completed-percent' : 'off-completed-percent'; ?>" id="progress-module-4">
                    <?php echo $progress4['completed'] ? '100%' : ($progress4['percentage'] . '%'); ?>
                </div>
            </button>
        </nav>

        <div id="module-container" class="module-container">
            <div class="loading-message">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Загрузка модуля...</p>
            </div>
        </div>

        <div class="progress-container">
            <div class="progress-header">
                <span class="progress-info" id="progressInfo">Выполнено: 0 из 0 вопросов</span>
                <span class="page-indicator" id="pageIndicator">Страница 1 из 1 (вопросы 1-10 из 0)</span>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>

        <div class="navigation-buttons">
            <div class="nav-left">
                <div class="pagination"></div>
            </div>
            
            <div class="nav-right">
                <button class="nav-btn next" id="nextBtn">
                    Следующая страница
                </button>
                <button class="nav-btn complete" id="completeBtn" style="display: none;">
                    Завершить модуль
                </button>
            </div>
        </div>
    </div>

    <div id="resultsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Результаты модуля</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button class="action-btn" onclick="window.location.href='view_results.php'">
                    <i class="fas fa-chart-bar"></i> Все результаты
                </button>
                <button class="action-btn">
                    <i class="fas fa-eye"></i> Записи на консультацию
                </button>
                <button class="action-btn" onclick="continueToNextModule()">
                    <i class="fas fa-forward"></i> Следующий модуль
                </button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
        <p>Опросник предназначен для профессионального использования психологами.</p>
        <p>Версия 2.0 | Сессия: <?php echo substr($_SESSION['survey_id'], 0, 10); ?>...</p>
        <?php if (PAYMENT_ENABLED && isPaymentPaid()): ?>
            <p style="color: #27ae60;"><i class="fas fa-check-circle"></i> Доступ оплачен</p>
        <?php elseif (!PAYMENT_ENABLED): ?>
            <p style="color: #27ae60;"><i class="fas fa-unlock-alt"></i> Бесплатный доступ</p>
        <?php endif; ?>
    </footer>

    <script src="script.js"></script>
</body>
</html>