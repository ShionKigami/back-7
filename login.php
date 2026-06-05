<?php
header('Content-Type: text/html; charset=UTF-8');

require_once 'config.php';
$db = getDB();

$session_started = false;
if (!empty($_COOKIE[session_name()]) && session_start()) {
    $session_started = true;
    if (!empty($_SESSION['login'])) {
        header('Location: ./');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Вход в систему</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <div class="container">
        <div class="header">
            <h1>🔐 Вход в личный кабинет</h1>
            <p>Введите ваш логин и пароль для входа</p>
        </div>
        
        <div class="form-content">
            <?php
            if (!empty($_COOKIE['login_error'])) {
                echo '<div class="error-message">❌ Неверный логин или пароль.</div>';
                setcookie('login_error', '', 100000); 
            }
            ?>
            
            <form action="" method="post">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input name="login" id="login" type="text" required />
                </div>
                
                <div class="form-group">
                    <label for="pass">Пароль</label>
                    <input name="pass" id="pass" type="password" required />
                </div>
                
                <button type="submit" class="login-btn">Войти</button>
            </form>
            
            <a href="index.php" class="back-link">← На главную</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}
else {
    $login_input = $_POST['login'] ?? '';
    $pass_input = $_POST['pass'] ?? '';

    if (empty($login_input) || empty($pass_input)) {
        setcookie('login_error', '1', time() + 24 * 60 * 60);
        header('Location: login.php');
        exit();
    }

    try {
        $stmt = $db->prepare("SELECT id, login, pass_hash FROM users WHERE login = ?");
        $stmt->execute([$login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($pass_input) === $user['pass_hash']) {
            if (!$session_started) {
                session_start();
            }
            $_SESSION['login'] = $user['login'];
            $_SESSION['uid'] = $user['id'];

            header('Location: index.php');
            exit();
        } else {
            setcookie('login_error', '1', time() + 24 * 60 * 60);
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        die('Ошибка базы данных: ' . $e->getMessage());
    }
}
?>
