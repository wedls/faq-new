    </div> <!-- Закрываем .container -->
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Личностный опросник "РЕСУРС". Все права защищены.</p>
        <p>Опросник предназначен для профессионального использования психологами.</p>
        <p>Версия 1.0 | Сессия: <?php echo substr($_SESSION['survey_id'], 0, 10); ?>...</p>
    </footer>
    
    <script src="script.js"></script>
    
    <script>
    // Дополнительные функции
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            document.getElementById('prevBtn').click();
        } else if (e.key === 'ArrowRight') {
            document.getElementById('nextBtn').click();
        }
    });
    </script>
</body>
</html>