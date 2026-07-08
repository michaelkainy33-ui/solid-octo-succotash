<?php
declare(strict_types=1); // Строгая типезация

//Подключение config
$config = require __DIR__.'/config.php';
$db = $config['db'];

// формируем строку подключения
$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";


$options = [
    // ошибки будут выбрасыватся как исключение
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // Fetch() будет возвращать ассоц массив ключ - значение
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// подкключение базы

try {
    // соединение бд
    $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
} catch (PDOException $e) {
    error_log('Ошибка БД: ' . $e->getMessage() . ' в ' . $e->getFile() . ' на строке ' . $e->getLine());
    http_response_code(500);
    exit("Извините, произошла техническая ошибка. Мы уже работаем над её устранением.");
}

?>
