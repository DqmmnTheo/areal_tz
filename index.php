<?php
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $full_name  = trim($_POST['full_name'] ?? '');
        $birth_date = $_POST['birth_date'] ?? '';
        $passport   = trim($_POST['passport'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $address    = trim($_POST['address'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $position   = trim($_POST['position'] ?? '');
        $salary     = trim($_POST['salary'] ?? '');
        $hire_date  = $_POST['hire_date'] ?? '';

        if (
            $full_name === '' || $birth_date === '' || $passport === '' || $phone === '' ||
            $address === '' || $department === '' || $position === '' || $salary === '' || $hire_date === ''
        ) {
            $errors[] = 'Все поля обязательны для заполнения.';
        } else {
            if (!is_numeric($salary)) {
                $errors[] = 'Зарплата должна быть числом.';
            }
            if (!preg_match('/^\d{10}$/', $passport)) {
                $errors[] = 'Паспорт должен содержать 10 цифр (4 серии и 6 номера).';
            }
            if (!preg_match('/^[78]\d{10}$/', $phone)) {
                $errors[] = 'Телефон должен быть в формате 8XXXXXXXXXX или 7XXXXXXXXXX.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare(
                'INSERT INTO employees (full_name, birth_date, passport, phone, address, department, position, salary, hire_date)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            $stmt->bind_param(
                'sssssssss',
                $full_name,
                $birth_date,
                $passport,
                $phone,
                $address,
                $department,
                $position,
                $salary,
                $hire_date
            );
            $stmt->execute();
            $stmt->close();

            header('Location: index.php');
            exit;
        }
    }
}

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

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $e): ?>
                <div><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-block">
        <h2>Новый сотрудник</h2>
        <form method="post">
            <input type="hidden" name="action" value="save">
            <div class="row">
                <label>ФИО:</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="row">
                <label>Дата рождения:</label>
                <input type="date" name="birth_date" required>
            </div>
            <div class="row">
                <label>Паспорт (серия/номер):</label>
                <input type="text" name="passport" required>
            </div>
            <div class="row">
                <label>Телефон:</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="row">
                <label>Адрес:</label>
                <input type="text" name="address" required>
            </div>
            <div class="row">
                <label>Отдел:</label>
                <select name="department" required>
                    <option value="">Выберите отдел</option>
                    <option value="IT">IT</option>
                    <option value="Бухгалтерия">Бухгалтерия</option>
                    <option value="Отдел продаж">Отдел продаж</option>
                </select>
            </div>
            <div class="row">
                <label>Должность:</label>
                <input type="text" name="position" required>
            </div>
            <div class="row">
                <label>Зарплата:</label>
                <input type="text" name="salary" required>
            </div>
            <div class="row">
                <label>Дата принятия:</label>
                <input type="date" name="hire_date" required>
            </div>
            <button type="submit">Сохранить</button>
        </form>
    </div>

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