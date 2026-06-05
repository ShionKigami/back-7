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
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            die('Ошибка подключения к базе данных: ' . $e->getMessage());
        }
    }
    return $db;
}

// Все языки программирования
function getAllLanguages() {
    return ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
}
?>
