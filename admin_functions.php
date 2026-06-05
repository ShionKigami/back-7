<?php
// admin_functions.php - Функции админ-панели
require_once 'config.php';

// Получить статистику по языкам
function getLanguageStats() {
    $db = getDB();
    $stmt = $db->query("
        SELECT language, COUNT(*) as count 
        FROM user_languages 
        GROUP BY language 
        ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получить всех пользователей
function getAllUsers() {
    $db = getDB();
    return $db->query("SELECT id, name, phone, email, birthdate, sex, biography, login, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}

// Получить пользователя по ID
function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $stmt = $db->prepare("SELECT language FROM user_languages WHERE user_id = ?");
        $stmt->execute([$id]);
        $user['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    return $user;
}

// Удалить пользователя
function deleteUser($id) {
    $db = getDB();
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("DELETE FROM user_languages WHERE user_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        $db->commit();
        return ['success' => true, 'message' => 'Пользователь успешно удален!'];
    } catch (PDOException $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Ошибка удаления: ' . $e->getMessage()];
    }
}

// Обновить пользователя
function updateUser($id, $data) {
    $db = getDB();
    try {
        $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, birthdate = ?, sex = ?, biography = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['birthdate'] ?? null,
            $data['sex'],
            $data['biography'] ?? null,
            $id
        ]);
        
        $stmt = $db->prepare("DELETE FROM user_languages WHERE user_id = ?");
        $stmt->execute([$id]);
        
        if (!empty($data['languages'])) {
            $lang_stmt = $db->prepare("INSERT INTO user_languages (user_id, language) VALUES (?, ?)");
            foreach ($data['languages'] as $lang) {
                $lang_stmt->execute([$id, $lang]);
            }
        }
        
        return ['success' => true, 'message' => 'Данные пользователя успешно обновлены!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Ошибка обновления: ' . $e->getMessage()];
    }
}
?>
