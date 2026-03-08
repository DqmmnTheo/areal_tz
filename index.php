<?php
require_once 'db.php';

$sql = 'SELECT * FROM employees ORDER BY full_name';
$stmt = $conn->prepare($sql);
$stmt->execute();
$employees = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Учет сотрудников</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Учет сотрудников</h1>

    <h2>Список сотрудников</h2>
    <table>
        <thead>
        <tr>
            <th>ФИО</th>
            <th>Дата рождения</th>
            <th>Паспорт</th>
            <th>Телефон</th>
            <th>Адрес</th>
            <th>Отдел</th>
            <th>Должность</th>
            <th>Зарплата</th>
            <th>Дата принятия</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $employees->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['birth_date']); ?></td>
                <td><?php echo htmlspecialchars($row['passport']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td><?php echo htmlspecialchars($row['position']); ?></td>
                <td><?php echo htmlspecialchars($row['salary']); ?></td>
                <td><?php echo htmlspecialchars($row['hire_date']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>