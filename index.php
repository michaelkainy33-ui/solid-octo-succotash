<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: user.php');
    exit;
}

require __DIR__ . '/bd.php';
require __DIR__ . '/functions.php';


// ОБРАБОТКА AJAX ЗАПРОСОВ ДЛЯ РЕДАКТИРОВАНИЯ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_student') {
        // Получить данные студента для редактирования
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Неверный ID']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM student WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        
        if (!$student) {
            echo json_encode(['success' => false, 'error' => 'Студент не найден']);
        } else {
            echo json_encode(['success' => true, 'student' => $student]);
        }
        exit;
        
    } elseif ($_POST['action'] === 'update_student') {
        // Обновить данные студента
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Неверный ID']);
            exit;
        }
        
        // Валидация
        $errors = [];
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'group_name' => trim($_POST['group_name'] ?? ''),
            'course' => (int) ($_POST['course'] ?? 1),
            'id' => $id
        ];
        
        // Простая валидация
        if (empty($data['full_name'])) $errors['full_name'] = 'Введите ФИО';
        if (empty($data['email'])) $errors['email'] = 'Введите email';
        if (empty($data['group_name'])) $errors['group_name'] = 'Введите группу';
        if ($data['course'] < 1 || $data['course'] > 6) $errors['course'] = 'Курс должен быть от 1 до 6';
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("
                UPDATE student 
                SET full_name = :full_name, email = :email, 
                    group_name = :group_name, course = :course 
                WHERE id = :id
            ");
            $stmt->execute($data);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                echo json_encode(['success' => false, 'errors' => ['email' => 'Этот email уже используется']]);
            } else {
                echo json_encode(['success' => false, 'errors' => ['common' => 'Ошибка базы данных']]);
            }
        }
        exit;
    }
}

$q = trim((string) ($_GET['q'] ?? ''));

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM student
        WHERE full_name LIKE :q OR email LIKE :q OR group_name LIKE :q
        ORDER BY id DESC
    ");
    $stmt->execute(['q' => "%{$q}%"]);
} else {
    $stmt = $pdo->query('SELECT * FROM student ORDER BY id DESC');
}

$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="modal.css">
</head>
<body>
    <div class="container">
        <div class="top">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Управление студентами</h1> 
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="color: #555;">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Администратор') ?> (Администратор)
                    </span>
                    <a href="logout.php" class="logout-btn">Выход</a>
                </div>
            </div>
            <div class="top_buttons">
                <a class="btn-primary" href="creat.php"> + Добавить</a>
                <a class="btn-primary-im" href="import.php"> Импорт CSV</a>
                <a class="btn-primary-ex" href="export.php"> Экспорт CSV</a>
                <a class="btn-primary-dt" href="debt.php"> Новый долг</a>
            </div>
        </div>
        <div class="box">
            <form action="" method='get' class="search-form">
                <input type="text" name='q' value='<?= h($q) ?>' placeholder="Поиск (ФИО/Группа)" />
                <button class="search-btn" type="submit">Найти</button>
                <a class="reset-btn" href="index.php">Сброс</a>
            </form>
        </div>

        <div class="cards">
            <?php foreach ($students as $s): ?>
                <div class="card_block" data-id="<?= h((string) $s['id']) ?>">
                    <div class="card__element"><img class="avatar__img" src="admin-img/avatar.png" alt=""></div>
                    <div class="card__element">id <?= h((string) $s['id']) ?></div>
                    <div class="card__element">ФИО <?= h($s['full_name']) ?></div>
                    <div class="card__element">Email <?= h($s['email']) ?></div>
                    <div class="card__element">Группа <?= h($s['group_name']) ?></div>
                    <div class="card__element">Курс <?= h((string) $s['course']) ?></div>
                    <div class="card__element">Создан <?= h($s['created_at']) ?></div>
                    <div class="actions">
                        
                        <button class="edit-btn" onclick="openEditModal(<?= h((string) $s['id']) ?>)">
                            Редактировать
                        </button>
                        <a class="delete-btn" href="delete.php?id=<?= h((string) $s['id']) ?>"
                           onclick="return confirm('Удалить запись?')">Удалить
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!$students): ?>
            <div class="no-data">Нет данных</div>
        <?php endif; ?>
    </div>
            <h2>Таблица задач</h2>
            <iframe src="https://docs.google.com/spreadsheets/d/e/2PACX-1vTU5Wkft-5ude0fro6hXKOOfPtbn0r4DaCz56VfYETHtF8in8pUhm819HdQ8aVcv8P9CbdEjdANPciD/pubhtml?widget=true&amp;headers=false" 
                class="frame" 
                style="width: 1800px; height: 630px;"></iframe>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2>Редактирование студента</h2>
            
            <form id="editForm" class="edit-form">
                <input type="hidden" id="editStudentId" name="id">
                
                <div class="form-group">
                    <label for="editFullName">ФИО *</label>
                    <input type="text" id="editFullName" name="full_name" required>
                    <div class="error-message" id="errorFullName"></div>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email *</label>
                    <input type="email" id="editEmail" name="email" required>
                    <div class="error-message" id="errorEmail"></div>
                </div>
                
                <div class="form-group">
                    <label for="editGroup">Группа *</label>
                    <input type="text" id="editGroup" name="group_name" required>
                    <div class="error-message" id="errorGroup"></div>
                </div>
                
                <div class="form-group">
                    <label for="editCourse">Курс *</label>
                    <input type="number" id="editCourse" name="course" min="1" max="6" required>
                    <div class="error-message" id="errorCourse"></div>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Отмена</button>
                    <button type="submit" class="save-btn">Сохранить</button>
                </div>
                
                <div class="error-message" id="errorCommon"></div>
            </form>
        </div>
    </div>

    <script src="modal.js"></script>
</body>
</html>
