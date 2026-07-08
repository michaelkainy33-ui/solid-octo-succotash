<?php
require __DIR__ . '/bd.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = :q");
    $stmt->execute(['q' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ПРОВЕРКА: найден ли пользователь
    if ($user !== false && password_verify($password, $user['password'])) {
        session_regenerate_id(true); 
        // Сохраняем в сессии ВСЕ данные пользователя
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $username;
        $_SESSION['user_role'] = $user['role']; // ← РОЛЬ!


        
        // Проверяем роль и перенаправляем
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: index.php");  // Админ → админ-панель
        } elseif ($_SESSION['user_role'] === 'user') {
            header("Location: user.php");   // Пользователь → обычная панель
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="Log.css">
    <style>
        .error-message {
            color: red;
            background-color: #ffe6e6;
            border: 1px solid red;
            padding: 10px;
            margin: 10px auto;
            border-radius: 5px;
            text-align: center;
            max-width: 300px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="separation">
            <div class="gap_space">
                <div class="sign-up_block">
                    <div class="sing-up">Вход</div>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="justify_input">
                    <div class="input_block">
                        <form class="input_block" method="post">
                            <input class="input_field" type="email" name="email" placeholder="Email" required>
                            <input class="input_field" type="password" name="password" placeholder="Пароль" required>
                            <div class="btn_sing">
                                <button class="btn-signup" type="submit">Войти</button>
                            </div>
                            <div class="line"></div>
                        </form>
                        <div class="googl_sing">
                            <a class="googl_link" href="#">
                                <img src="sing-img/Google.svg" alt="">Вход через Google
                            </a>
                        </div>
                        <div class="googl_sing">
                            <a class="googl_link" href="#">
                                <img src="sing-img/Facebook.svg" alt="">Вход через Facebook
                            </a>
                        </div>
                        <div class="googl_sing">
                            <a class="googl_link" href="#">
                                <img src="sing-img/Apple.svg" alt="">Вход через Apple
                            </a>
                        </div>
                        <div class="end_block">
                            Нет аккаунта?<a class="log_in" href="registration.php">Зарегистрироваться</a>
                        </div>
                    </div>
                </div>
                <div class="footer">© 2026 Relume</div>
            </div>
        </div>
    </div>
</body>

</html>
