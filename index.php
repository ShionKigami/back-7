<?php
header('Content-Type: text/html; charset=UTF-8');

require_once 'config.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    session_start();
    
    $messages = [];
    $errors = [];
    $values = [];

    if (!empty($_COOKIE['save'])) {
        $messages[] = 'Результаты сохранены.';
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                'Можно <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong>',
                htmlspecialchars($_COOKIE['login'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($_COOKIE['pass'], ENT_QUOTES, 'UTF-8')
            );
        }
    }
        
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);
    }

    $error_fields = ['name', 'phone', 'email', 'birthdate', 'sex', 'languages', 'contract'];
    foreach ($error_fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        if ($errors[$field]) {
            setcookie($field . '_error', '', 100000);
            $messages[] = '<div class="error">' . getErrorMessage($field) . '</div>';
        }
        
    }

    $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
    $values['phone'] = empty($_COOKIE['phone_value']) ? '' : strip_tags($_COOKIE['phone_value']);
    $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
    $values['birthdate'] = empty($_COOKIE['birthdate_value']) ? '' : strip_tags($_COOKIE['birthdate_value']);
    $values['sex'] = empty($_COOKIE['sex_value']) ? '' : strip_tags($_COOKIE['sex_value']);
    $values['languages'] = empty($_COOKIE['languages_value']) ? [] : explode('|', strip_tags($_COOKIE['languages_value']));
    $values['contract'] = empty($_COOKIE['contract_value']) ? '' : strip_tags($_COOKIE['contract_value']);

    if (empty($errors) && !empty($_SESSION['login'])) {
        try {
            $stmt = $db->prepare("SELECT id, name, phone, email, birthdate, sex, biography FROM users WHERE login = ?");
            $stmt->execute([$_SESSION['login']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                $_SESSION['uid'] = $userData['id'];
                $values['name'] = strip_tags($userData['name']);
                $values['phone'] = strip_tags($userData['phone']);
                $values['email'] = strip_tags($userData['email']);
                $values['birthdate'] = strip_tags($userData['birthdate']);
                $values['sex'] = strip_tags($userData['sex']);
                $values['contract'] = '1'; 

                $lang_stmt = $db->prepare("SELECT language FROM user_languages WHERE user_id = ?");
                $lang_stmt->execute([$userData['id']]);
                $values['languages'] = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (PDOException $e) {
            $messages[] = '<div class="error">Ошибка загрузки данных: ' . $e->getMessage() . '</div>';
        }
    }

    setcookie('name_value', '', 100000);
    setcookie('phone_value', '', 100000);
    setcookie('email_value', '', 100000);
    setcookie('birthdate_value', '', 100000);
    setcookie('sex_value', '', 100000);
    setcookie('languages_value', '', 100000);
    setcookie('contract_value', '', 100000);

    include('form.php');
    exit();
}
else {
    $errors = FALSE;

    if (empty($_POST['name'])) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif (strlen($_POST['name']) > 150) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]+$/u', $_POST['name'])) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('name_value', $_POST['name'], time() + 30 * 24 * 60 * 60);
    }

    if (!empty($_POST['phone']) && !preg_match('/^[\+0-9\s\-\(\)]{10,20}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
    }

    if (!empty($_POST['birthdate'])) {
        $date = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
        if (!$date || $date->format('Y-m-d') !== $_POST['birthdate']) {
            setcookie('birthdate_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
        } else {
            setcookie('birthdate_value', $_POST['birthdate'], time() + 30 * 24 * 60 * 60);
        }
    }

    $allowed_genders = ['male', 'female'];
    if (empty($_POST['sex'])) {
        setcookie('sex_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif (!in_array($_POST['sex'], $allowed_genders)) {
        setcookie('sex_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);
    }

    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        $lang_valid = TRUE;
        foreach ($_POST['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $lang_valid = FALSE;
                break;
            }
        }
        if (!$lang_valid) {
            setcookie('languages_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
        } else {
            setcookie('languages_value', implode('|', $_POST['languages']), time() + 30 * 24 * 60 * 60);
        }
    }

    if (empty($_POST['contract']) || $_POST['contract'] != '1') {
        setcookie('contract_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('contract_value', '1', time() + 30 * 24 * 60 * 60);
    }

    if ($errors) {
        header('Location: index.php');
        exit();
    }

    session_start();

    $error_cookies = ['name', 'phone', 'email', 'birthdate', 'sex', 'languages', 'contract'];
    foreach ($error_cookies as $field) {
        setcookie($field . '_error', '', 100000);
    }

    $is_update = false;
    if (!empty($_SESSION['login'])) {
        $is_update = true;
    }

    try {
        $db->beginTransaction();

        if ($is_update) {
            $check_stmt = $db->prepare("SELECT id FROM users WHERE login = ?");
            $check_stmt->execute([$_SESSION['login']]);
            $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_user) {
                throw new Exception("Пользователь не найден");
            }
            
            $user_id = $existing_user['id'];
            
            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, birthdate = ?, sex = ?, biography = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['phone'] ?? null,
                $_POST['email'] ?? null,
                $_POST['birthdate'] ?? null,
                $_POST['sex'],
                $_POST['biography'] ?? null,
                $user_id
            ]);

            $del_stmt = $db->prepare("DELETE FROM user_languages WHERE user_id = ?");
            $del_stmt->execute([$user_id]);
            
            $db->commit();
            
            setcookie('save', '1', time() + 30 * 24 * 60 * 60);
            
            header('Location: index.php');
            exit();

        } else {
            $login = 'user_' . bin2hex(random_bytes(8));
            
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
            $pass = '';
            for ($i = 0; $i < 12; $i++) {
                $pass .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (name, phone, email, birthdate, sex, biography, login, pass_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['phone'] ?? null,
                $_POST['email'] ?? null,
                $_POST['birthdate'] ?? null,
                $_POST['sex'],
                $_POST['biography'] ?? null,
                $login,
                $pass_hash
            ]);

            $user_id = $db->lastInsertId();

            $lang_stmt = $db->prepare("INSERT INTO user_languages (user_id, language) VALUES (?, ?)");
            foreach ($_POST['languages'] as $lang) {
                $lang_stmt->execute([$user_id, $lang]);
            }

            $db->commit();
            
            setcookie('save', '1', time() + 30 * 24 * 60 * 60);
            setcookie('login', $login, time() + 30 * 24 * 60 * 60);
            setcookie('pass', $pass, time() + 30 * 24 * 60 * 60);
            
            header('Location: index.php');
            exit();
        }

   } catch (PDOException $e) {
    $db->rollBack();
    error_log('Database error in form processing: ' . $e->getMessage());
    die('Произошла ошибка при сохранении данных. Пожалуйста, попробуйте позже.');
} catch (Exception $e) {
    $db->rollBack();
    error_log('General error in form processing: ' . $e->getMessage());
    die('Произошла непредвиденная ошибка. Администратор уже уведомлён.');
}
}

function getErrorMessage($field) {
    $messages = [
        'name' => 'Введите корректное имя.',
        'phone' => 'Введите корректный номер телефона.',
        'email' => 'Введите корректный email.',
        'birthdate' => 'Введите корректную дату рождения.',
        'sex' => 'Выберите корректный пол.',
        'languages' => 'Выберите хотя бы один язык программирования.',
        'contract' => 'Ознакомьтесь с условиями.'
    ];
    return $messages[$field] ?? 'Неизвестная ошибка.';
}
?>
