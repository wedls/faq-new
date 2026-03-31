class Questionnaire {
    constructor() {
        this.currentModule = 1;
        this.currentPage = 1;
        this.responses = {};
        this.completedModules = {
            1: false,
            2: false,
            3: false,
            4: false
        };
        this.questionsPerModule = {
            1: 136,
            2: 95,
            3: 30,
            4: 30
        };
        this.questionsPerPage = 10;
        this.paymentStatus = 'pending'; // pending, paid
        this.init();
    }

    init() {
        console.log('Инициализация полного опросника "РЕСУРС"...');
        this.setupModuleNavigation();
        this.setupQuestionNavigation();
        this.setupPagination();
        this.setupOptionSelection();
        this.updateProgress();
        this.loadSavedResponses();
        // Проверяем статус оплаты (этот метод загрузит модуль, если доступ открыт)
        this.checkPaymentStatus();
    }

    checkPaymentStatus() {
        // Проверяем статус оплаты через сервер
        fetch('check_payment.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.payment_enabled && !data.paid) {
                        this.paymentStatus = 'pending';
                        this.showPaymentRequired();
                    } else {
                        this.paymentStatus = 'paid';
                        // Если оплата отключена или оплачена, загружаем модуль
                        this.loadModule(this.currentModule, this.currentPage);
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка проверки оплаты:', error);
                // При ошибке всё равно пробуем загрузить модуль
                this.loadModule(this.currentModule, this.currentPage);
            });
    }

    showPaymentRequired() {
        const container = document.getElementById('module-container');
        container.innerHTML = `
            <div class="module-content" style="text-align: center; padding: 50px 30px;">
                <i class="fas fa-lock" style="font-size: 70px; color: #e74c3c; margin-bottom: 20px;"></i>
                <h2 style="color: #2c3e50; margin-bottom: 20px;">Доступ к опроснику требует оплаты</h2>
                <p style="margin-bottom: 20px; color: #666; max-width: 500px; margin-left: auto; margin-right: auto;">
                    Для прохождения полного опросника "РЕСУРС" необходимо оплатить доступ ко всем 4 модулям.
                </p>
                <div style="margin: 30px 0;">
                    <div style="font-size: 36px; font-weight: bold; color: #27ae60;">
                        <?php echo number_format(PAYMENT_AMOUNT, 0, ',', ' '); ?> ₽
                    </div>
                    <p style="color: #7f8c8d;">разовый доступ ко всем модулям</p>
                </div>
                <button class="action-btn" onclick="window.location.href='payment.php'" style="background: #27ae60; padding: 15px 40px; font-size: 18px;">
                    <i class="fas fa-credit-card"></i> Оплатить доступ
                </button>
                <p style="margin-top: 30px; font-size: 12px; color: #95a5a6;">
                    Оплата через YooKassa. Поддерживаются карты, СБП, Apple Pay, Google Pay.
                </p>
            </div>
        `;
        
        // Скрываем кнопки навигации
        const nextBtn = document.getElementById('nextBtn');
        const completeBtn = document.getElementById('completeBtn');
        if (nextBtn) nextBtn.style.display = 'none';
        if (completeBtn) completeBtn.style.display = 'none';
        
        // Скрываем пагинацию
        const paginationContainer = document.querySelector('.pagination');
        if (paginationContainer) paginationContainer.innerHTML = '';
        
        // Сбрасываем прогресс
        const progressFill = document.getElementById('progressFill');
        if (progressFill) progressFill.style.width = '0%';
        const progressInfo = document.getElementById('progressInfo');
        if (progressInfo) progressInfo.textContent = 'Выполнено: 0 из 0 вопросов';
        const pageIndicator = document.getElementById('pageIndicator');
        if (pageIndicator) pageIndicator.textContent = 'Доступ к опроснику требует оплаты';
    }

    setupModuleNavigation() {
        document.querySelectorAll('.module-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const moduleNum = parseInt(e.target.closest('.module-btn').dataset.module);
                console.log('Переключение на модуль:', moduleNum);
                
                // Проверяем, не завершен ли модуль
                if (this.completedModules[moduleNum]) {
                    // Показываем сообщение с кнопками для завершенного модуля
                    this.showCompletedModuleMessage(moduleNum);
                    return;
                }
                
                this.currentModule = moduleNum;
                this.currentPage = 1;
                this.loadModule(moduleNum, 1);
                this.updateProgress();
                this.updateModuleProgressIndicators();
            });
        });
    }

    setupQuestionNavigation() {
        document.getElementById('nextBtn').addEventListener('click', () => this.navigatePage(1));
        document.getElementById('completeBtn').addEventListener('click', () => this.completeQuestionnaire());
    }

    setupPagination() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('pagination-item') && !e.target.classList.contains('disabled') && !e.target.classList.contains('dots')) {
                const page = parseInt(e.target.dataset.page);
                // Проверяем, можно ли перейти на эту страницу
                if (this.canNavigateToPage(page)) {
                    this.currentPage = page;
                    this.loadModule(this.currentModule, page);
                }
            }
            
            // Обработка клика на стрелки пагинации
            if (e.target.closest('.pagination-item.prev') && !e.target.closest('.pagination-item').classList.contains('disabled')) {
                this.navigatePage(-1);
            }
            
            if (e.target.closest('.pagination-item.next') && !e.target.closest('.pagination-item').classList.contains('disabled')) {
                this.navigatePage(1);
            }
        });
    }

    setupOptionSelection() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('option-btn') || e.target.closest('.option-btn')) {
                // Проверяем, не завершен ли модуль
                if (this.completedModules[this.currentModule]) {
                    alert(`Модуль ${this.currentModule} уже завершен. Вы не можете изменять ответы.`);
                    return;
                }
                
                const optionBtn = e.target.classList.contains('option-btn') ? e.target : e.target.closest('.option-btn');
                const questionNum = parseInt(optionBtn.dataset.question);
                const moduleNum = this.currentModule;
                const optionValue = optionBtn.dataset.value;
                
                console.log(`Ответ сохранен: Модуль ${moduleNum}, Вопрос ${questionNum}, Ответ: ${optionValue}`);
                
                // Сохраняем ответ
                if (!this.responses[moduleNum]) {
                    this.responses[moduleNum] = {};
                }
                this.responses[moduleNum][questionNum] = optionValue;
                
                // Сохраняем на сервере
                this.saveResponse(moduleNum, questionNum, optionValue);
                
                // Обновляем отображение кнопок
                const container = optionBtn.closest('.question-container');
                if (container) {
                    // Снимаем выделение со всех кнопок в этом вопросе
                    container.querySelectorAll('.option-btn').forEach(btn => {
                        btn.classList.remove('selected');
                    });
                    // Добавляем выделение к выбранной кнопке
                    optionBtn.classList.add('selected');
                }
                
                this.updateProgress();
                this.updateModuleProgressIndicators();
                this.updateNavigationButtons();
                this.updatePagination();
            }
        });
    }

    canNavigateToPage(targetPage) {
        // Если модуль завершен - нельзя переходить
        if (this.completedModules[this.currentModule]) {
            alert(`Модуль ${this.currentModule} завершен. Для просмотра результатов перейдите в раздел "Все результаты".`);
            return false;
        }
        
        // Если это текущая страница - можно
        if (targetPage === this.currentPage) return true;
        
        // Если пытаемся перейти назад - всегда можно
        if (targetPage < this.currentPage) return true;
        
        // Если пытаемся перейти вперед - проверяем все ли вопросы текущей страницы отвечены
        const currentPageQuestions = this.getQuestionsOnCurrentPage();
        const answeredQuestions = this.getAnsweredQuestionsOnCurrentPage();
        
        // Если все вопросы на текущей странице отвечены - можно переходить
        if (answeredQuestions >= currentPageQuestions) {
            return true;
        }
        
        // Показываем сообщение пользователю
        const unanswered = currentPageQuestions - answeredQuestions;
        alert(`Чтобы перейти дальше, нужно ответить на все вопросы текущей страницы.\nОсталось ответить на ${unanswered} вопрос(ов).`);
        return false;
    }

    getQuestionsOnCurrentPage() {
        // Возвращает количество вопросов на текущей странице
        const totalQuestions = this.questionsPerModule[this.currentModule];
        const totalPages = Math.ceil(totalQuestions / this.questionsPerPage);
        
        if (this.currentPage === totalPages) {
            // Последняя страница
            const fullPages = Math.floor(totalQuestions / this.questionsPerPage);
            const lastPageQuestions = totalQuestions - (fullPages * this.questionsPerPage);
            return lastPageQuestions > 0 ? lastPageQuestions : this.questionsPerPage;
        } else {
            return this.questionsPerPage;
        }
    }

    getAnsweredQuestionsOnCurrentPage() {
        if (!this.responses[this.currentModule]) return 0;
        
        const startQuestion = (this.currentPage - 1) * this.questionsPerPage + 1;
        const endQuestion = Math.min(this.currentPage * this.questionsPerPage, this.questionsPerModule[this.currentModule]);
        
        let answeredCount = 0;
        for (let q = startQuestion; q <= endQuestion; q++) {
            if (this.responses[this.currentModule][q]) {
                answeredCount++;
            }
        }
        
        return answeredCount;
    }

    saveResponse(module, question, answer) {
        const formData = new FormData();
        formData.append('action', 'save_response');
        formData.append('module', module);
        formData.append('question', question);
        formData.append('answer', answer);
        
        fetch('save_response.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Ошибка сохранения:', result.message);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
    }

    loadSavedResponses() {
        fetch('load_responses.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.responses) {
                    this.responses = data.responses;
                    // Обновляем статус завершённости модулей
                    if (data.completed_modules) {
                        this.completedModules = data.completed_modules;
                    }
                    console.log('Ответы загружены:', this.responses);
                    console.log('Завершённые модули:', this.completedModules);
                    this.updateModuleProgressIndicators();
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки ответов:', error);
            });
    }

    loadModule(moduleNum, page = 1) {
        console.log(`Загрузка модуля ${moduleNum}, страница ${page}`);
        
        // Проверяем, завершен ли модуль
        if (this.completedModules[moduleNum]) {
            // Если модуль завершен, показываем сообщение с кнопками
            this.showCompletedModuleMessage(moduleNum);
            return;
        }
        
        // Обновляем активные кнопки модулей
        document.querySelectorAll('.module-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const moduleBtn = document.querySelector(`.module-btn[data-module="${moduleNum}"]`);
        if (moduleBtn) {
            moduleBtn.classList.add('active');
        }
        
        // Показываем загрузку
        const container = document.getElementById('module-container');
        container.innerHTML = `
            <div class="loading-message">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Загрузка модуля ${moduleNum}...</p>
            </div>
        `;
        
        // Загружаем модуль
        fetch(`load_module.php?module=${moduleNum}&page=${page}`)
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                
                // Обновляем сохраненные ответы в интерфейсе
                if (this.responses[moduleNum]) {
                    Object.keys(this.responses[moduleNum]).forEach(qNum => {
                        const questionEl = container.querySelector(`.question-container[data-question="${qNum}"]`);
                        if (questionEl) {
                            const answer = this.responses[moduleNum][qNum];
                            const optionBtn = questionEl.querySelector(`.option-btn[data-value="${answer}"]`);
                            if (optionBtn) {
                                questionEl.querySelectorAll('.option-btn').forEach(btn => {
                                    btn.classList.remove('selected');
                                });
                                optionBtn.classList.add('selected');
                            }
                        }
                    });
                }
                
                this.updateNavigationButtons();
                this.updatePagination();
                // Обновляем индикатор страницы после загрузки модуля
                this.updatePageIndicator();
                this.updateProgress();
            })
            .catch(error => {
                console.error('Ошибка загрузки модуля:', error);
                container.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Ошибка загрузки модуля</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            });
    }
    
    showCompletedModuleMessage(moduleNum) {
        const container = document.getElementById('module-container');
        const moduleBtn = document.querySelector(`.module-btn[data-module="${moduleNum}"]`);
        
        // Обновляем активные кнопки модулей
        document.querySelectorAll('.module-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        if (moduleBtn) {
            moduleBtn.classList.add('active');
        }
        
        // Получаем описание модуля
        const moduleDescriptions = {
            1: 'Актуальное психоэмоциональное состояние, особенности восприятия и мышления',
            2: 'Диагностика психического напряжения и стрессовых расстройств',
            3: 'Профориентация и адаптивность',
            4: 'Выявление склонности к употреблению ПАВ'
        };
        
        container.innerHTML = `
            <div class="module-content" data-module="${moduleNum}">
                <h2 class="module-title">
                    <span class="module-content-b-t">
                        <span class="module-content-b-t-t">Модуль ${moduleNum}</span>
                        <span class="module-content-b-t-d">${moduleDescriptions[moduleNum]}</span>
                    </span>
                    <span class="module-content-b-i">
                        ${this.getModuleIcon(moduleNum)}
                    </span>
                </h2>
                <div class="completed-module-message" style="text-align: center; padding: 40px 30px;">
                    <i class="fas fa-check-circle" style="font-size: 70px; color: #27ae60; margin-bottom: 20px;"></i>
                    <h3 style="color: #27ae60; margin-bottom: 15px;">Модуль ${moduleNum} успешно завершен!</h3>
                    <p style="margin-bottom: 30px; color: #666;">Вы уже прошли этот модуль. Результаты сохранены.</p>
                    <p class="progress-info" style="margin-bottom: 30px; color: #666; text-align: justify;">
                        После прохождения психологического тестирования рекомендуется обратиться к психологу-консультанту для обсуждения результатов и получения рекомендаций. Если результаты теста оказались противоречивыми или вызвали сомнения в достоверности интерпретаций, когда результаты не совпадают с собственными ощущениями или представлениями о себе, специалист поможет Вам выявить индивидуальные особенности, уточнить проблему и предложить меры по её решению. Совместно с психологом-консультантом Вы обсудите практические рекомендации по решению проблемы. Уточните, проясните, конкретизируете во всех существенных деталях результаты тестирования. Координаты ВИТА-КОУЧИНГ
                    </p>
                    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                        <button class="action-btn" onclick="window.questionnaire.retakeModule(${moduleNum})" style="background: #3498db;">
                            <i class="fas fa-redo"></i> Пройти еще раз
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-eye"></i> Записи на консультацию
                        </button>
                        <button class="action-btn" onclick="window.location.href='view_results.php'" style="background: #27ae60;">
                            <i class="fas fa-chart-bar"></i> Посмотреть результаты
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Скрываем кнопки навигации
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('completeBtn').style.display = 'none';
        
        // Обновляем прогресс
        this.updateProgress();
    }
    
    getModuleIcon(moduleNum) {
        const icons = {
            1: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_249_1135)">
                    <path d="M13.9964 14.8637C14.4847 15.3521 14.4847 16.1437 13.9964 16.6312C13.508 17.1196 12.7164 17.1196 12.2289 16.6312L7.38219 11.7846L6.20385 12.9629L10.698 17.4571C11.1864 17.9454 11.1864 18.7371 10.698 19.2246C10.2097 19.7129 9.41802 19.7129 8.93052 19.2246L5.03635 15.3304C2.57219 12.9262 0.0546875 9.72289 0.0546875 6.67122C0.0546875 3.45456 2.48552 0.837891 5.47135 0.837891C6.94885 0.837891 8.08052 1.38956 8.94219 2.00206L5.90969 5.00872C5.35802 5.55956 5.05469 6.29289 5.05469 7.07206C5.05469 7.85122 5.35802 8.58372 5.90802 9.13372C6.45885 9.68539 7.19135 9.98872 7.97135 9.98872C8.32635 9.98872 8.66885 9.91789 8.99219 9.79789L13.9964 14.8637ZM12.073 7.10872L10.3297 8.84039L15.4214 13.8554C15.9097 14.3437 16.7014 14.3437 17.1889 13.8554C17.9355 13.1087 17.2964 12.2821 17.2964 12.2821L12.0722 7.10872H12.073ZM14.603 0.886224C13.2414 0.894557 11.948 1.48622 10.9514 2.41456L7.08719 6.25289C6.59885 6.74122 6.59885 7.53289 7.08719 8.02039C7.57552 8.50872 8.36719 8.50872 8.85469 8.02039L12.1097 4.78706L18.5547 11.1854C19.5472 9.58372 20.0539 8.08372 20.0539 6.71956C20.0539 3.50289 17.5889 0.886224 14.6022 0.886224H14.603Z" fill="white"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_249_1135">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                </svg>`,
            2: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_249_1144)">
                    <path d="M7.49502 5.32408C7.51335 5.44908 7.47752 5.57575 7.39669 5.67325C7.19085 5.91992 6.80002 5.87408 6.65669 5.58658L5.70502 3.65325L4.56919 4.85075C4.32835 5.09158 3.92335 5.03158 3.76252 4.73158L2.25252 1.71158C2.13002 1.46491 2.17919 1.16825 2.37335 0.973248L3.19002 0.157415C3.45085 -0.103418 3.89169 -0.0292517 4.05169 0.303248L4.85669 2.16992L6.04669 0.979915C6.32752 0.699082 6.80835 0.844082 6.88752 1.23325L7.49502 5.32408ZM12.5042 5.32408C12.4859 5.44908 12.5217 5.57575 12.6025 5.67325C12.8084 5.91992 13.1992 5.87408 13.3425 5.58658L14.2942 3.65325L15.43 4.85075C15.6709 5.09158 16.0759 5.03158 16.2367 4.73158L17.7467 1.71158C17.8692 1.46491 17.82 1.16825 17.6259 0.973248L16.81 0.157415C16.5492 -0.103418 16.1084 -0.0292517 15.9484 0.303248L15.1434 2.16992L13.9534 0.979915C13.6725 0.699082 13.1917 0.844082 13.1125 1.23325L12.5042 5.32408ZM19.0584 14.2832C18.6209 16.1999 16.8209 17.4991 14.8542 17.4991H10.8325V14.9991C10.8325 14.3966 10.7375 13.8041 10.5575 13.2399C10.3917 12.7207 9.75335 12.5241 9.29919 12.8266C8.99169 13.0316 8.85252 13.4141 8.96669 13.7657C9.09502 14.1616 9.16585 14.5749 9.16585 14.9991V19.1658C9.16585 19.6257 8.79252 19.9991 8.33252 19.9991C7.87252 19.9991 7.49919 19.6257 7.49919 19.1658V18.3082C6.82669 18.8149 6.00335 19.1324 5.10335 19.1549C2.74002 19.0982 0.83252 17.1666 0.83252 14.7907C0.83252 13.3791 1.52752 12.0466 2.67002 11.2307C3.16002 9.37325 4.70585 7.94908 6.60335 7.60158C7.37835 6.50158 8.64419 5.83325 10 5.83325C11.3559 5.83325 12.6159 6.50325 13.3925 7.60658C14.5017 7.82408 15.4792 8.43408 16.1559 9.33075C18.215 9.92408 19.5692 12.0457 19.0584 14.2832ZM15.8334 13.3324C15.8334 12.8716 15.46 12.4991 15 12.4991C14.54 12.4991 14.1667 12.1257 14.1667 11.6657C14.1667 11.2057 13.7934 10.8324 13.3334 10.8324C12.8734 10.8324 12.5 11.2049 12.5 11.6657C12.5 13.0441 13.6217 14.1657 15 14.1657C15.46 14.1657 15.8334 13.7932 15.8334 13.3324Z" fill="white"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_249_1144">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                </svg>`,
            3: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_249_1153)">
                    <path d="M19.39 8.52608L16.265 5.40108L14.5 7.16608L16.1166 8.78191H11.25V3.92191L12.8333 5.50525L14.6016 3.73691L11.4733 0.609412C11.2798 0.415898 11.0502 0.262392 10.7974 0.15766C10.5446 0.0529287 10.2736 -0.000976562 9.99997 -0.000976562C9.72634 -0.000976563 9.45539 0.0529287 9.20259 0.15766C8.94979 0.262392 8.7201 0.415898 8.52664 0.609412L5.40164 3.73441L7.16664 5.49941L8.74997 3.91608V8.77941H3.92247L5.50581 7.19608L3.73497 5.43691L0.610805 8.56108C0.417167 8.75456 0.263555 8.9843 0.15875 9.23717C0.0539443 9.49005 0 9.7611 0 10.0348C0 10.3086 0.0539443 10.5796 0.15875 10.8325C0.263555 11.0854 0.417167 11.3151 0.610805 11.5086L3.73581 14.6336L5.49997 12.8652L3.91664 11.2819H8.74997V16.0736L7.16664 14.4994L5.40164 16.2644L8.52664 19.3894C8.7201 19.5829 8.94979 19.7364 9.20259 19.8412C9.45539 19.9459 9.72634 19.9998 9.99997 19.9998C10.2736 19.9998 10.5446 19.9459 10.7974 19.8412C11.0502 19.7364 11.2798 19.5829 11.4733 19.3894L14.5983 16.2644L12.8333 14.4994L11.25 16.0827V11.2911H16.0416L14.5 12.8327L16.2683 14.6011L19.3933 11.4761C19.5871 11.2823 19.7408 11.0523 19.8455 10.799C19.9503 10.5458 20.004 10.2744 20.0037 10.0004C20.0034 9.72636 19.949 9.45508 19.8437 9.20209C19.7384 8.94911 19.5842 8.71938 19.39 8.52608Z" fill="white"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_249_1153">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                </svg>`,
            4: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 3C11.66 3 13 4.34 13 6C13 7.66 11.66 9 10 9C8.34 9 7 7.66 7 6C7 4.34 8.34 3 10 3ZM10 17.2C7.5 17.2 5.29 15.92 4 13.98C4.03 11.99 8 10.9 10 10.9C11.99 10.9 15.97 11.99 16 13.98C14.71 15.92 12.5 17.2 10 17.2Z" fill="white"/>
                </svg>`
        };
        return icons[moduleNum] || '';
    }

    navigatePage(direction) {
        const moduleContainer = document.querySelector('.module-content');
        if (!moduleContainer) return;
        
        // Если модуль завершен - не навигируем
        if (this.completedModules[this.currentModule]) return;
        
        const totalPages = parseInt(moduleContainer.dataset.totalPages) || 1;
        const newPage = this.currentPage + direction;
        
        if (newPage >= 1 && newPage <= totalPages) {
            // Проверяем, можно ли перейти на эту страницу
            if (this.canNavigateToPage(newPage)) {
                this.currentPage = newPage;
                this.loadModule(this.currentModule, newPage);
            }
        }
    }

    updateProgress() {
        const totalQuestions = this.questionsPerModule[this.currentModule] || 0;
        // Если модуль завершен, показываем 100%
        const answeredQuestions = this.completedModules[this.currentModule] 
            ? totalQuestions 
            : this.getAnsweredQuestionsCount();
        const percentage = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;
        
        const progressFill = document.getElementById('progressFill');
        const progressInfo = document.getElementById('progressInfo');
        
        if (progressFill) {
            progressFill.style.width = `${percentage}%`;
            
            // Создаем или обновляем элемент процента внутри линии прогресса
            let percentElement = progressFill.querySelector('.progress-percent');
            if (!percentElement) {
                percentElement = document.createElement('div');
                percentElement.className = 'progress-percent';
                progressFill.appendChild(percentElement);
            }
            percentElement.textContent = `${percentage}%`;
            
            // Если прогресс 0%, перемещаем проценты в начало линии
            if (percentage === 0) {
                percentElement.style.left = '0';
                percentElement.style.right = 'auto';
                percentElement.style.transform = 'translateY(-50%)';
                percentElement.style.marginLeft = '5px';
                percentElement.style.marginRight = '0';
            } else {
                percentElement.style.left = 'auto';
                percentElement.style.right = '0';
                percentElement.style.transform = 'translateY(-50%)';
                percentElement.style.marginLeft = '0';
                percentElement.style.marginRight = '0px';
            }
        }
        
        if (progressInfo) {
            progressInfo.textContent = `Выполнено: ${answeredQuestions} из ${totalQuestions} вопросов`;
            if (this.completedModules[this.currentModule]) {
                progressInfo.textContent += ` (Модуль завершен)`;
            }
        }
        
        console.log(`Прогресс модуля ${this.currentModule}: ${answeredQuestions}/${totalQuestions} (${percentage}%)`);
    }

    updatePageIndicator() {
        const totalQuestions = this.questionsPerModule[this.currentModule] || 0;
        const startQuestion = totalQuestions > 0 ? (this.currentPage - 1) * this.questionsPerPage + 1 : 0;
        const endQuestion = totalQuestions > 0 ? Math.min(this.currentPage * this.questionsPerPage, totalQuestions) : 0;
        const totalPages = totalQuestions > 0 ? Math.ceil(totalQuestions / this.questionsPerPage) : 1;
        
        const pageIndicator = document.getElementById('pageIndicator');
        if (pageIndicator) {
            if (totalQuestions > 0) {
                pageIndicator.textContent = `Страница ${this.currentPage} из ${totalPages} (вопросы ${startQuestion}-${endQuestion} из ${totalQuestions})`;
                if (this.completedModules[this.currentModule]) {
                    pageIndicator.textContent += ` [Модуль завершен]`;
                }
            } else {
                pageIndicator.textContent = `Страница ${this.currentPage} из ${totalPages}`;
            }
        }
    }

    updatePagination() {
        const totalQuestions = this.questionsPerModule[this.currentModule] || 0;
        const totalPages = totalQuestions > 0 ? Math.ceil(totalQuestions / this.questionsPerPage) : 1;
        
        // Получаем контейнер пагинации
        const paginationContainer = document.querySelector('.pagination');
        if (!paginationContainer) return;
        
        // Очищаем контейнер
        paginationContainer.innerHTML = '';
        
        // Если модуль завершен или всего 1 страница, не показываем пагинацию
        if (this.completedModules[this.currentModule] || totalPages <= 1) {
            return;
        }
        
        // Создаем стрелку "назад"
        const prevArrow = document.createElement('div');
        prevArrow.className = `pagination-item prev ${this.currentPage === 1 ? 'disabled' : ''}`;
        prevArrow.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
        paginationContainer.appendChild(prevArrow);
        
        // Определяем диапазон страниц для отображения
        let startPage = 1;
        let endPage = totalPages;
        
        if (totalPages > 6) {
            if (this.currentPage <= 4) {
                // Показать первые 5 страниц
                endPage = 5;
            } else if (this.currentPage >= totalPages - 3) {
                // Показать последние 5 страниц
                startPage = totalPages - 4;
            } else {
                // Показать 2 страницы слева и 2 справа от текущей
                startPage = this.currentPage - 2;
                endPage = this.currentPage + 2;
            }
        }
        
        // Создаем кнопки страниц
        for (let i = startPage; i <= endPage; i++) {
            const pageItem = document.createElement('div');
            pageItem.className = `pagination-item ${i === this.currentPage ? 'active' : ''}`;
            pageItem.dataset.page = i;
            pageItem.textContent = i;
            paginationContainer.appendChild(pageItem);
        }
        
        // Если страниц больше 6 и мы не показываем последние страницы
        if (totalPages > 6 && endPage < totalPages - 1) {
            // Добавляем "..."
            const dotsItem = document.createElement('div');
            dotsItem.className = 'pagination-item dots';
            dotsItem.textContent = '...';
            paginationContainer.appendChild(dotsItem);
            
            // Добавляем последнюю страницу
            const lastPageItem = document.createElement('div');
            lastPageItem.className = 'pagination-item';
            lastPageItem.dataset.page = totalPages;
            lastPageItem.textContent = totalPages;
            paginationContainer.appendChild(lastPageItem);
        }
        
        // Создаем стрелку "вперед"
        const nextArrow = document.createElement('div');
        nextArrow.className = `pagination-item next ${this.currentPage === totalPages ? 'disabled' : ''}`;
        nextArrow.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
        paginationContainer.appendChild(nextArrow);
    }

    updateModuleProgressIndicators() {
        // Обновляем прогресс на кнопках модулей
        [1, 2, 3, 4].forEach(moduleNum => {
            const totalQuestions = this.questionsPerModule[moduleNum] || 0;
            // Если модуль завершен, показываем 100%
            const answeredQuestions = this.completedModules[moduleNum] 
                ? totalQuestions 
                : this.getAnsweredQuestionsCount(moduleNum);
            const percentage = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;
            const isCompleted = this.completedModules[moduleNum];
            
            // Обновляем текст прогресса
            const progressEl = document.getElementById(`progress-module-${moduleNum}`);
            if (progressEl) {
                progressEl.textContent = isCompleted ? '100%' : `${percentage}%`;
                
                // Обновляем классы прогресса в зависимости от процента выполнения
                if (isCompleted || percentage === 100) {
                    progressEl.classList.remove('off-completed-percent');
                    progressEl.classList.add('on-completed-percent');
                } else {
                    progressEl.classList.remove('on-completed-percent');
                    progressEl.classList.add('off-completed-percent');
                }
            }
            
            // Обновляем класс иконки
            const iconEl = document.querySelector(`.module-btn[data-module="${moduleNum}"] .icon-b`);
            if (iconEl) {
                if (isCompleted) {
                    iconEl.classList.remove('off-completed');
                    iconEl.classList.add('on-completed');
                } else {
                    iconEl.classList.remove('on-completed');
                    iconEl.classList.add('off-completed');
                }
            }
            
            // Обновляем класс границы кнопки модуля
            const moduleBtnEl = document.querySelector(`.module-btn[data-module="${moduleNum}"]`);
            if (moduleBtnEl) {
                if (isCompleted) {
                    moduleBtnEl.classList.add('on-completed-border');
                } else {
                    moduleBtnEl.classList.remove('on-completed-border');
                }
            }
        });
    }

    getAnsweredQuestionsCount(module = null) {
        const moduleNum = module || this.currentModule;
        if (!this.responses[moduleNum]) return 0;
        return Object.keys(this.responses[moduleNum]).length;
    }

    updateNavigationButtons() {
        const moduleContainer = document.querySelector('.module-content');
        if (!moduleContainer) return;
        
        // Если модуль завершен - скрываем кнопки
        if (this.completedModules[this.currentModule]) {
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('completeBtn').style.display = 'none';
            return;
        }
        
        const totalPages = parseInt(moduleContainer.dataset.totalPages) || 1;
        const nextBtn = document.getElementById('nextBtn');
        const completeBtn = document.getElementById('completeBtn');
        
        // Проверяем, можно ли перейти на следующую страницу
        const questionsOnCurrentPage = this.getQuestionsOnCurrentPage();
        const answeredOnCurrentPage = this.getAnsweredQuestionsOnCurrentPage();
        
        // Проверяем, является ли текущая страница последней
        const isLastPage = this.currentPage === totalPages;
        
        // Проверяем, отвечены ли все вопросы на текущей странице
        const allAnsweredOnPage = answeredOnCurrentPage >= questionsOnCurrentPage;
        
        // Проверяем, отвечены ли все вопросы во всем модуле
        const totalQuestions = this.questionsPerModule[this.currentModule] || 0;
        const answeredQuestions = this.getAnsweredQuestionsCount();
        const allAnsweredInModule = answeredQuestions >= totalQuestions;
        
        // Логика отображения кнопок:
        // - На последней странице с отвеченными всеми вопросами: показываем "Завершить модуль"
        // - На последней странице, но не все вопросы отвечены: показываем "Следующая страница" (disabled)
        // - Не последняя страница: показываем "Следующая страница"
        
        if (isLastPage && allAnsweredOnPage && allAnsweredInModule) {
            // Последняя страница, все вопросы отвечены - показываем "Завершить модуль"
            nextBtn.style.display = 'none';
            completeBtn.style.display = 'flex';
        } else {
            // Не последняя страница или не все вопросы отвечены - показываем "Следующая страница"
            nextBtn.style.display = 'flex';
            completeBtn.style.display = 'none';
            
            // Блокируем кнопку "Следующая страница", если не все вопросы отвечены
            nextBtn.disabled = !allAnsweredOnPage;
        }
    }

    completeQuestionnaire() {
        // Проверяем, не завершен ли уже модуль
        if (this.completedModules[this.currentModule]) {
            alert(`Модуль ${this.currentModule} уже завершен.`);
            return;
        }
        
        const totalQuestions = this.questionsPerModule[this.currentModule] || 0;
        const answeredQuestions = this.getAnsweredQuestionsCount();
        
        if (answeredQuestions < totalQuestions) {
            const unanswered = totalQuestions - answeredQuestions;
            if (!confirm(`Вы ответили на ${answeredQuestions} из ${totalQuestions} вопросов.\nОсталось ${unanswered} вопросов без ответа.\nВсе равно завершить модуль?`)) {
                return;
            }
        }
        
        console.log('Завершение модуля...', this.responses[this.currentModule]);
        
        // Отправляем результаты на обработку
        this.calculateResults();
    }

    calculateResults() {
        const formData = new FormData();
        formData.append('action', 'calculate_results');
        formData.append('module', this.currentModule);
        formData.append('responses', JSON.stringify(this.responses[this.currentModule] || {}));
        
        fetch('process_results.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Отмечаем модуль как завершенный
                this.completedModules[this.currentModule] = true;
                this.updateModuleProgressIndicators();
                
                this.showResults(result.results);
                // Очищаем локальные ответы для этого модуля
                delete this.responses[this.currentModule];
            } else {
                alert('Ошибка при обработке результатов: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Ошибка соединения с сервером');
        });
    }

    /**
     * Повторное прохождение модуля
     */
    retakeModule(moduleNum) {
        if (confirm(`Вы уверены, что хотите пройти модуль ${moduleNum} заново?`)) {
            const formData = new FormData();
            formData.append('action', 'retake_module');
            formData.append('module', moduleNum);
            
            fetch('process_results.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Обновляем статус завершенности модуля
                    this.completedModules[moduleNum] = false;
                    // Очищаем локальные ответы для этого модуля
                    if (this.responses[moduleNum]) {
                        delete this.responses[moduleNum];
                    }
                    // Обновляем прогресс на кнопках
                    this.updateModuleProgressIndicators();
                    // Переключаемся на модуль для повторного прохождения
                    this.currentModule = moduleNum;
                    this.currentPage = 1;
                    this.loadModule(moduleNum, 1);
                    this.updateProgress();
                } else {
                    alert('Ошибка при удалении результатов: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Ошибка соединения с сервером');
            });
        }
    }

    showResults(results) {
        const modal = document.getElementById('resultsModal');
        const modalBody = document.getElementById('modalBody');
        
        let resultsHtml = `<h3>Результаты модуля ${this.currentModule}</h3>`;
        
        if (this.currentModule === 1) {
            resultsHtml += this.formatModule1Results(results);
        } else if (this.currentModule === 2) {
            resultsHtml += this.formatModule2Results(results);
        } else if (this.currentModule === 3) {
            resultsHtml += this.formatModule3Results(results);
        } else if (this.currentModule === 4) {
            resultsHtml += this.formatModule4Results(results);
        }
        
        modalBody.innerHTML = resultsHtml;
        modal.style.display = 'block';
    }

    formatModule1Results(results) {
        let html = `<div class="results-section">`;
        
        // Шкала лжи с сырым баллом и индексом
        if (results.lie_raw_score !== undefined && results.lie_index !== undefined) {
            html += `
                <div class="scale-result">
                    <strong>Шкала искренности (L)</strong>
                    <p><strong>Сырой балл:</strong> ${results.lie_raw_score}/11</p>
                    <p><strong>Индекс (L = N/11):</strong> ${results.lie_index}</p>
                    <p>${results.lie_index < 0.6 ? '✅ Результаты относительно достоверны' : '⚠️ Тенденция к искажению действительности'}</p>
                    ${results.lie_interpretation ? `<p><em>${results.lie_interpretation}</em></p>` : ''}
                </div>
            `;
        }
        
        // Основные шкалы
        const scales = [
            { key: 'psychosomatic', name: 'Психосоматические риски' },
            { key: 'anxiety', name: 'Тревога' },
            { key: 'demonstrative', name: 'Демонстративность' },
            { key: 'frustration', name: 'Фрустрационные установки' },
            { key: 'sensitivity', name: 'Сензитивность' },
            { key: 'distancing', name: 'Дистанцирование' },
            { key: 'hypomania', name: 'Гипомания' },
            { key: 'extraversion', name: 'Экстраверсия-Интроверсия' },
            { key: 'emotional_intelligence', name: 'Эмоциональный интеллект' }
        ];
        
        scales.forEach(scale => {
            if (results[scale.key] !== undefined) {
                const score = results[scale.key];
                let level = '';
                if (score >= 9) level = 'Высокий уровень';
                else if (score >= 7) level = 'Тенденция к высокому';
                else if (score >= 4) level = 'Средний уровень';
                else level = 'Низкий уровень';
                
                html += `
                    <div class="scale-result">
                        <strong>${scale.name}:</strong> ${score}/10 баллов - ${level}
                        ${results[scale.key + '_interpretation'] ? `<p><em>${results[scale.key + '_interpretation']}</em></p>` : ''}
                    </div>
                `;
            }
        });
        
        // Суицидальная шкала
        if (results.suicidal !== undefined) {
            const score = results.suicidal;
            let interpretation = results.suicidal_interpretation || '';
            
            html += `
                <div class="scale-result suicidal">
                    <strong>Склонность к суицидальному поведению:</strong> ${score}/100 баллов
                    <p>${interpretation}</p>
                </div>
            `;
        }
        
        html += `</div>`;
        return html;
    }

    formatModule2Results(results) {
        let html = `<div class="results-section">`;
        
        // Часть 1: Индекс психического напряжения
        if (results.part1) {
            html += `<h4>Часть 1: Индекс психического напряжения (ИПН)</h4>`;
            
            const blocks = [
                { key: 'emotional', name: 'Эмоциональный дискомфорт' },
                { key: 'cognitive', name: 'Когнитивные трудности' },
                { key: 'physical', name: 'Физиологическое напряжение' },
                { key: 'behavioral', name: 'Поведенческие нарушения' }
            ];
            
            blocks.forEach(block => {
                if (results.part1[block.key] !== undefined) {
                    const score = results.part1[block.key];
                    let level = '';
                    if (score <= 20) level = 'Низкий уровень';
                    else if (score <= 40) level = 'Средний уровень';
                    else level = 'Высокий уровень';
                    
                    html += `
                        <div class="scale-result">
                            <strong>${block.name}:</strong> ${score}/60 баллов - ${level}
                            ${results.part1[block.key + '_interpretation'] ? `<p><em>${results.part1[block.key + '_interpretation']}</em></p>` : ''}
                        </div>
                    `;
                }
            });
            
            if (results.part1.total !== undefined) {
                const total = results.part1.total;
                let level = '';
                if (total <= 80) level = 'Низкий уровень напряжения';
                else if (total <= 160) level = 'Средний уровень напряжения';
                else level = 'Высокий уровень напряжения';
                
                html += `
                    <div class="scale-result total">
                        <strong>Общий индекс ИПН:</strong> ${total}/240 баллов - ${level}
                        ${results.part1.total_interpretation ? `<p><em>${results.part1.total_interpretation}</em></p>` : ''}
                    </div>
                `;
            }
        }
        
        // Часть 2: ПТСР
        if (results.part2 !== undefined) {
            html += `<h4>Часть 2: Оценка посттравматического стресса</h4>`;
            const score = results.part2;
            
            html += `
                <div class="scale-result ptsd">
                    <strong>Результат:</strong> ${score} баллов
                    <p>${results.part2_interpretation || ''}</p>
                </div>
            `;
        }
        
        html += `</div>`;
        return html;
    }

    formatModule3Results(results) {
        let html = `<div class="results-section">`;
        html += `<h4>Профессиональные профили и адаптивность</h4>`;
        
        // ИСПРАВЛЕНО: Обновленный список профилей в соответствии с process_results.php
        const profiles = [
            { key: 'organizational', name: 'Организационный профиль' },
            { key: 'analytical', name: 'Аналитический профиль' },
            { key: 'creative', name: 'Творческий профиль' },
            { key: 'communicative', name: 'Коммуникативный профиль' },
            { key: 'scientific', name: 'Научный профиль' },
            { key: 'technical', name: 'Практико-технический профиль' },
            { key: 'self_development', name: 'Саморазвитие и обучение' }
        ];
        
        profiles.forEach(profile => {
            if (results[profile.key] !== undefined) {
                const score = results[profile.key];
                // Определяем уровень выраженности
                let level = '';
                let color = '';
                
                if (score <= 8) {
                    level = 'Низкая выраженность';
                    color = '#e74c3c';
                } else if (score <= 15) {
                    level = 'Средняя выраженность';
                    color = '#f39c12';
                } else {
                    level = 'Высокая выраженность';
                    color = '#27ae60';
                }
                
                html += `
                    <div class="scale-result">
                        <strong>${profile.name}:</strong> 
                        <span style="color: ${color}">${score} баллов - ${level}</span>
                    </div>
                `;
            }
        });
        
        // Рекомендации
        if (results.recommendations && results.recommendations.length > 0) {
            html += `<h4>Рекомендации</h4>`;
            results.recommendations.forEach(rec => {
                html += `<p>• ${rec}</p>`;
            });
        }
        
        html += `</div>`;
        return html;
    }
    
    formatModule4Results(results) {
        let html = `<div class="results-section">`;
        
        const score = results.total_score || 0;
        
        html += `<h4>Результаты оценки склонности к употреблению ПАВ</h4>`;
        html += `<div class="scale-result" style="font-size: 18px; text-align: center; margin-bottom: 20px;">
                    <strong>Общий балл: ${score} из 30</strong>
                </div>`;
        
        if (score <= 13) {
            html += `
                <div class="scale-result" style="border-left-color: #27ae60; background: #d4edda;">
                    <h4 style="color: #155724; margin-bottom: 10px;">✅ Низкий风险 (${score} баллов)</h4>
                    <p>Ваш психологический профиль свидетельствует о гармоничном состоянии. Риск формирования зависимого поведения минимален, что говорит о развитых навыках самоконтроля и осознанности. Вы не испытываете навязчивого влечения к каким-либо веществам или активностям, а ваши решения носят взвешенный характер.</p>
                    <p>Вы демонстрируете высокий уровень удовлетворенности жизнью и успешную социальную адаптацию. Эта устойчивость служит надежным внутренним ресурсом, который защищает вас от потенциальных рисков.</p>
                    <p><strong>Рекомендация:</strong> Продолжайте поддерживать этот баланс через практики саморефлексии, здоровый образ жизни и развитие имеющихся сильных сторон личности. В этом вам может помочь опытный психолог или психотерапевт.</p>
                </div>
            `;
        } else if (score <= 16) {
            html += `
                <div class="scale-result" style="border-left-color: #f39c12; background: #fff3cd;">
                    <h4 style="color: #856404; margin-bottom: 10px;">⚠️ Умеренный риск (${score} баллов)</h4>
                    <p>У вас умеренный риск зависимого поведения, который означает наличие предрасположенности, когда привычные модели пока не вызывают серьёзных нарушений, но несут потенциальную угрозу. Ключевая задача — профилактика, чтобы ситуация не прогрессировала.</p>
                    <p><strong>Рекомендации:</strong></p>
                    <ul style="margin-left: 20px;">
                        <li>Осознанное отношение к последствиям выбора</li>
                        <li>Развитие навыков управления стрессом без внешних стимуляторов</li>
                        <li>Регулярная физическая активность для естественного повышения уровня эндорфинов</li>
                        <li>Поддержка близкого круга общения</li>
                    </ul>
                    <p>При сохранении или прогрессировании состояния рекомендуется консультация психолога.</p>
                </div>
            `;
        } else {
            html += `
                <div class="scale-result" style="border-left-color: #e74c3c; background: #fee;">
                    <h4 style="color: #721c24; margin-bottom: 10px;">🚨 Выраженный риск (${score} баллов)</h4>
                    <p>Набранные баллы указывают на выраженную стадию развития аддикции. Выявленные индикаторы, такие как навязчивые мысли, рост толерантности и пренебрежение обязанностями, указывают на активное формирование дезадаптивной схемы. На этом этапе критически важно провести комплексную диагностику для определения глубины вовлеченности и типа зависимости.</p>
                    <p><strong>Важно понимать:</strong> Зависимое поведение часто маскируется под привычку или способ справиться со стрессом. Однако его отличительная черта — прогрессирующая центрация жизни вокруг объекта зависимости. На физиологическом уровне происходит перестройка системы вознаграждения мозга.</p>
                    <p style="font-weight: bold; color: #c0392b;">Необходимо:</p>
                    <ul style="margin-left: 20px;">
                        <li>Комплексная диагностика для определения глубины вовлеченности и типа зависимости</li>
                        <li>Консультация психиатра-нарколога</li>
                        <li>Работа с клиническим психологом или психотерапевтом</li>
                        <li>Социальная реабилитация и поддержка близких</li>
                    </ul>
                    <p><strong>Раннее обращение к специалистам критически важно!</strong> Признание проблемы — это первый и самый значимый шаг к возвращению контроля над собственной жизнью.</p>
                </div>
            `;
        }
        
        html += `<p  class="progress-info"  style="margin-top: 20px; font-style: italic;">После прохождения психологического тестирования рекомендуется обратиться к психологу-консультанту для обсуждения результатов и получения рекомендаций. Если результаты теста оказались противоречивыми или вызвали сомнения в достоверности интерпретаций, когда результаты не совпадают с собственными ощущениями или представлениями о себе, специалист поможет Вам выявить индивидуальные особенности, уточнить проблему и предложить меры по её решению. Совместно с психологом-консультантом Вы обсудите практические рекомендации по решению проблемы. Уточните, проясните, конкретизируете во всех существенных деталях результаты тестирования. Координаты ВИТА-КОУЧИНГ</p>`;
        
        html += `</div>`;
        return html;
    }
}

// Глобальные функции для модального окна
function closeModal() {
    document.getElementById('resultsModal').style.display = 'none';
}

function continueToNextModule() {
    const currentModule = window.questionnaire.currentModule;
    if (currentModule < 4) {
        window.questionnaire.currentModule = currentModule + 1;
        window.questionnaire.currentPage = 1;
        window.questionnaire.loadModule(window.questionnaire.currentModule, 1);
        closeModal();
    } else {
        window.location.href = 'view_results.php';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.questionnaire = new Questionnaire();
    
    // Закрытие модального окна
    const closeBtn = document.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('resultsModal');
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Горячие клавиши
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            const prevBtn = document.querySelector('.pagination-item.prev');
            if (prevBtn && !prevBtn.classList.contains('disabled')) {
                window.questionnaire.navigatePage(-1);
            }
        } else if (e.key === 'ArrowRight') {
            const nextBtn = document.querySelector('.pagination-item.next');
            if (nextBtn && !nextBtn.classList.contains('disabled')) {
                window.questionnaire.navigatePage(1);
            }
        } else if (e.key === 'Escape') {
            closeModal();
        }
    });
});