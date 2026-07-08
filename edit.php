<?php

declare(strict_types=1); // Строгая типезация

// Эти строки подключают файлы с настройками базы данных
require __DIR__ . '/bd.php';
require __DIR__ . '/functions.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Неверный ID');
}


$stmt = $pdo->prepare("SELECT * from student WHERE id=:id");
$stmt->execute(["id" => $id]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    exit('Запись не найдена');
}

$errors = [];

$data = [
    'full_name' => $student['full_name'],
    'email' => $student['email'],
    'group_name' => $student['group_name'],
    'course' => (string) $student['course'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация данных формы
    [$errors, $data] = validate_student($_POST);

    if (!$errors) {
        try {

            // подготовка запроса для вставки в таблицу student
            $stmt = $pdo->prepare("
                UPDATE student
                SET full_name=:full_name, email=:email, group_name=:group_name, course=:course
                WHERE id = :id
            ");

            // выполнение запроса и передача параметров
            $stmt->execute($data + ["id" => $id]);

            //Редирект обратно в список студента
            header('Location: index.php');

            exit;
        } catch (PDOException $e) {
    error_log('Ошибка при сохранении студента: ' . $e->getMessage());
    if ((int) $e->errorInfo[1] === 1062) {
        $errors['email'] = 'Эта почта уже есть!';
    } else {
        $errors['common'] = 'Ошибка при сохранении данных. Попробуйте позже.';
    }
}
    } else {

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирвание </title>
    <link rel="stylesheet" href="edit.css">

</head>

<body>
    <h1>Редактирвание Ученика</h1>
    <div class="block_btn">
        <p><a class="back_btn" href="index.php">
                < Назад</a>
        </p>
    </div>


    <?php if (!empty($errors['common'])): ?>
        <div style="color: red;"><?= h($errors['common']) ?></div>
    <?php endif; ?>
    <div class="container">
        <form action="" method="post" class="form-create">
            <div>
                <label for="">ФИО</label><br>
                <input type="text" name="full_name" required value="<?= h($data['full_name']) ?>" />

                <div style="color: red;"><?= h($errors['full_name'] ?? '') ?></div>
            </div><br>

            <div>
                <label for="">Email</label><br>
                <input type="text" name="email" required value="<?= h($data['email']) ?>" />

                <div style="color: red;"><?= h($errors['email'] ?? '') ?></div>
            </div><br>

            <div>
                <label for="">Группа</label><br>
                <input type="text" name="group_name" required value="<?= h($data['group_name']) ?>" />

                <div style="color: red;"><?= h($errors['group_name'] ?? '') ?></div>
            </div><br>

            <div>
                <label for="">Курс</label><br>
                <input type="text" name="course" required value="<?= h($data['course']) ?>" />

                <div style="color: red;"><?= h($errors['course'] ?? '') ?></div>
            </div><br>

            <button class="retention" type="submit">Сохранить</button>


        </form>
    </div>

</body>

</html>
