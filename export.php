<?php

declare(strict_types=1); // Строгая типезация

require __DIR__ . '/bd.php';

header('Content-Type: text/csv; charset=utf-8');
echo "\xEF\xBB\xBF";
header('Content-Disposition: attachment; filename="students.csv"');

$out = fopen('php://output', 'w');

fputcsv($out, ['full_name', 'email', 'group_name', 'course', 'created_at']);

$stmt = $pdo->query("SELECT full_name, email, group_name, course, created_at FROM student ORDER BY id DESC");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out,$row);
}

fclose($out);
?>
