<?php

declare(strict_types=1); // Строгая типезация

require __DIR__ . '/bd.php';

$errors = [];
$success = '';

// Значения по умолчанию
$student_id = '';
$subject_name = '';
$debt_type = 'экзамен';
$semester = '';
$status = 'не сдан';

// Получаем список студентов для выпадающего списка
$student = $pdo->query('SELECT id, full_name FROM student ORDER BY full_name')->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Получаем данные из формы
    $student_id = $_POST['student_id'] ?? '';
    $subject_name = trim($_POST['subject_name'] ?? '');
    $debt_type = $_POST['debt_type'] ?? 'экзамен';
    $semester = $_POST['semester'] ?? '';
    $status = $_POST['status'] ?? 'не сдана';

    // Простая проверка
    if (empty($student_id)) {
        $errors[] = 'Выберите студента';
    }
    if (empty($subject_name)) {
        $errors[] = 'Введите название предмета';
    }

    // Если ошибок нет - добавляем в базу
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
        INSERT INTO debt (student_id, subject_name, debt_type, semester, status)
        VALUES (?, ?, ?, ?, ?)
        ");

            $stmt->execute([$student_id, $subject_name, $debt_type, $semester, $status]);

            $success = 'Долг успешно добавлен!';

            // Очищаем поля после успешного добавления
            $subject_name = '';
            $semester = '';

        } catch (Exception $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Долги</title>
    <link rel="stylesheet" href="debt-st.css">
</head>
<body>
    <h1 class="sing-up">Добавить долг студенту</h1>
    
    <!-- Показываем ошибки -->
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Показываем успех -->
    <?php if ($success): ?>
        <div style="color: green;">
            <p><?= $success ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Форма -->
<div class="container">
     <form class="form_debt" method="POST">
        <p>
            <label>Студент:</label><br>
            <select  class="input_field" name="student_id" required>
                <option value="">Выберите студента</option>
            <?php
                foreach ($student as $student_one): ?>
                    <option value="<?= $student_one['id'] ?>">
                        <?= htmlspecialchars($student_one['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <label for="test">Предмет:</label><br>
            <input id="test" class="input_field" type="text" name="subject_name" value="<?= htmlspecialchars($subject_name) ?>" required>
        </p>
        
        <p>
            <label>Тип долга:</label>
            <select class="input_field" name="debt_type">
                <option value="экзамен" <?= ($debt_type == 'экзамен') ? 'selected' : '' ?>>Экзамен</option>
                <option value="зачет" <?= ($debt_type == 'зачет') ? 'selected' : '' ?>>Зачет</option>
                <option value="курсовая" <?= ($debt_type == 'курсовая') ? 'selected' : '' ?>>Курсовая</option>
                <option value="практика" <?= ($debt_type == 'практика') ? 'selected' : '' ?>>Практика</option>
                <option value="другое" <?= ($debt_type == 'другое') ? 'selected' : '' ?>>Другое</option>
            </select>
        </p>
        
        <p>
            <label>Семестр (не обязательно):</label><br>
            <input class="input_field" type="number" name="semester" value="<?= htmlspecialchars($semester) ?>" min="1" max="12">
        </p>
        
        <p>
            <label>Статус:</label><br>
            <select class="input_field" name="status">
                <option value="не сдана" <?= ($status == 'не сдана') ? 'selected' : '' ?>>Не сдана</option>
                <option value="в процессе" <?= ($status == 'в процессе') ? 'selected' : '' ?>>В процессе</option>
                <option value="исправлена" <?= ($status == 'исправлена') ? 'selected' : '' ?>>Исправлена</option>
            </select>
        </p>
        
        <button class="btn-signup" type="submit">Добавить долг</button>
    </form>
    
    <p class="back__p"> <a class="back__btn" href="index.php"> <- Назад к списку</a></p>
</div>
   
</body>
</html>
