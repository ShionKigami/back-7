<?php
// config.php - Конфигурация и подключение к БД
header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения к БД
define('DB_USER', 'u82624');
define('DB_PASS', '8440989');
define('DB_NAME', 'u82624');
define('DB_HOST', 'localhost');

// Функция подключения к БД
function getDB() {
    static $db = null;
    if ($db === null) {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ALL);
        
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/logs/php_errors.log');
        
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755);
        }
        
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('Технические работы. Пожалуйста, попробуйте позже.');
        }
    }
    return $db;
}

// Все языки программирования
function getAllLanguages() {
    return ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
}
?>
