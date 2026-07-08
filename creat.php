<?php

declare(strict_types=1); // Строгая типезация

require __DIR__ . '/bd.php';
require __DIR__ . '/functions.php';

$errors = [];
$data = ['full_name' => '', 'email' => '', 'group_name' => '', 'course' => '',];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация данных формы
    [$errors, $data] = validate_student($_POST);

    if (!$errors) {
        try {
                
            // подготовка запроса для вставки в таблицу student
            $stmt = $pdo->prepare("
                    INSERT INTO student(full_name, email, group_name, course)
                    VALUES (:full_name, :email, :group_name, :course)
            ");
            // выполнение запроса и передача параметров
            $stmt->execute($data);

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
    <title>Добавить студента</title>
    <link rel="stylesheet" href="create.css">
</head>

<body>
    <div class="container">
        <h1>Добавить студента</h1>
    <div class="block_btn">
         <p><a class="back_btn" href="index.php">< Назад</a></p>
    </div>

    <?php if (!empty($errors['common'])): ?>
        <div style="color: red;"><?= h($errors['common']) ?></div>
    <?php endif; ?>

    <form action="" method="post" class="form-create">
        <div>
            <label for="">ФИО</label><br>
            <input type="text" name="full_name" required value="<?= h($data['full_name']) ?>" />
            <?php if (!empty($errors['full_name'])): ?>
                <div style="color: red;"><?= h($errors['full_name']) ?></div>
            <?php endif; ?>
        </div><br>

        <div>
            <label for="">Email</label><br>
            <input type="text" name="email" required value="<?= h($data['email']) ?>" />
            <?php if (!empty($errors['email'])): ?>
                <div style="color: red;"><?= h($errors['email']) ?></div>
            <?php endif; ?>
        </div><br>

        <div>
            <label for="">Группа</label><br>
            <input type="text" name="group_name" required value="<?= h($data['group_name']) ?>" />
            <?php if (!empty($errors['group_name'])): ?>
                <div style="color: red;"><?= h($errors['group_name']) ?></div>
            <?php endif; ?>
        </div><br>

         <div>
            <label for="">Курс</label><br>
            <input type="text" name="course" required value="<?= h($data['course']) ?>" />
            <?php if (!empty($errors['course'])): ?>
                <div style="color: red;"><?= h($errors['course']) ?></div>
            <?php endif; ?>
        </div><br>

        <button class="retention" type="submit">Сохранить</button>
    </form>
    </div>
    
</body>

</html>
