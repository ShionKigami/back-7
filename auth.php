<?php
// auth.php - Функции для авторизации
require_once 'config.php';

// Проверка HTTP Basic Auth для админа
function authenticateAdmin() {
    $auth_realm = "Admin Panel";
    
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('HTTP/1.0 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="' . $auth_realm . '"');
        echo '<h1>Требуется авторизация</h1>';
        echo '<p>Для доступа к панели администратора введите логин и пароль.</p>';
        exit;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT password_hash FROM admins WHERE username = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || md5($_SERVER['PHP_AUTH_PW']) !== $admin['password_hash']) {
        header('HTTP/1.0 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="' . $auth_realm . '"');
        echo '<h1>Неверный логин или пароль!</h1>';
        exit;
    }
    
    return $_SERVER['PHP_AUTH_USER'];
}
?>
