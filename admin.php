<?php
// admin.php - Главная страница админ-панели
require_once 'auth.php';
require_once 'admin_functions.php';

// Проверяем авторизацию
$admin_username = authenticateAdmin();

// Обрабатываем действия
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;
$message = null;
$error = null;

if ($action === 'delete' && $user_id) {
    $result = deleteUser($user_id);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateUser($user_id, $_POST);
    if ($result['success']) {
        $message = $result['message'];
        $action = '';
    } else {
        $error = $result['message'];
    }
}

// Получаем данные
$lang_stats = getLanguageStats();
$users = getAllUsers();
$edit_user = ($action === 'edit' && $user_id && $_SERVER['REQUEST_METHOD'] === 'GET') ? getUserById($user_id) : null;
$all_languages = getAllLanguages();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 15px; }
        .stat-card { background: white; padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card .count { font-size: 28px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #667eea; color: white; }
        tr:hover { background: #f8f9fa; }
        .action-buttons { display: flex; gap: 10px; }
        .edit-btn, .delete-btn { padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; display: inline-block; }
        .edit-btn { background: #28a745; color: white; }
        .delete-btn { background: #dc3545; color: white; }
        .back-btn { display: inline-block; margin-top: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; }
        .edit-form { background: #f8f9fa; padding: 20px; border-radius: 15px; margin-bottom: 30px; }
        .admin-info { background: #e9ecef; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .total-users { font-size: 14px; color: #666; margin-top: 10px; text-align: center; }
        @media (max-width: 768px) { th, td { padding: 8px; font-size: 12px; } .stats-container { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); } }
    </style>
</head>
<body>
<div class="container">
    <div class="header admin-header">
        <h1>👑 Панель администратора</h1>
        <p>Управление пользователями и просмотр статистики</p>
    </div>
    
    <div class="form-content">
        <div class="admin-info">
            <div>👋 Здравствуйте, <strong><?php echo htmlspecialchars($admin_username); ?></strong>!</div>
            <a href="index.php" style="color: #dc3545; text-decoration: none; font-weight: 600;">← Вернуться на главную</a>
        </div>
        
        <?php if ($message): ?>
            <div class="success-message">✅ <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <h2>📊 Статистика по языкам программирования</h2>
        <div class="stats-container">
            <?php 
            $lang_counts = [];
            foreach ($lang_stats as $stat) {
                $lang_counts[$stat['language']] = $stat['count'];
            }
            foreach ($all_languages as $lang):
                $count = $lang_counts[$lang] ?? 0;
            ?>
                <div class="stat-card">
                    <h4><?php echo htmlspecialchars($lang); ?></h4>
                    <div class="count"><?php echo $count; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($edit_user): ?>
            <div class="edit-form">
                <h3>✏️ Редактирование пользователя</h3>
                <form action="?action=edit&id=<?php echo $user_id; ?>" method="POST">
                    <div class="form-group">
                        <label>ФИО *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($edit_user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Дата рождения</label>
                        <input type="date" name="birthdate" value="<?php echo htmlspecialchars($edit_user['birthdate']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Пол *</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="sex" value="male" <?php echo ($edit_user['sex'] == 'male') ? 'checked' : ''; ?>> Мужской</label>
                            <label class="radio-label"><input type="radio" name="sex" value="female" <?php echo ($edit_user['sex'] == 'female') ? 'checked' : ''; ?>> Женский</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Языки программирования</label>
                        <div class="checkbox-group">
                            <?php foreach ($all_languages as $lang): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="languages[]" value="<?php echo htmlspecialchars($lang); ?>"
                                        <?php echo (in_array($lang, $edit_user['languages'] ?? [])) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($lang); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Биография</label>
                        <textarea name="biography" rows="4"><?php echo htmlspecialchars($edit_user['biography']); ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">💾 Сохранить изменения</button>
                    <a href="admin.php" class="back-btn">Отмена</a>
                </form>
            </div>
        <?php endif; ?>
        
        <h2>👥 Все пользователи</h2>
        <div class="total-users">Всего пользователей: <strong><?php echo count($users); ?></strong></div>
        
        <div class="users-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th><th>Дата рождения</th><th>Пол</th><th>Логин</th><th>Действия</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['birthdate']); ?></td>
                            <td><?php echo ($user['sex'] == 'male') ? 'Мужской' : 'Женский'; ?></td>
                            <td><?php echo htmlspecialchars($user['login']); ?></td>
                            <td class="action-buttons">
                                <a href="?action=edit&id=<?php echo $user['id']; ?>" class="edit-btn">✏️ Ред.</a>
                                <a href="?action=delete&id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Удалить пользователя?')">🗑️ Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
