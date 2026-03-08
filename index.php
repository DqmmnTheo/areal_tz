<?php
require_once 'db.php';

$errors = [];
$departments = ['IT', 'Бухгалтерия', 'Отдел продаж'];

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

        if ($full_name === '' || $birth_date === '' || $passport === '' || $phone === '' ||
            $address === '' || $department === '' || $position === '' || $salary === '' || $hire_date === '') {
            $errors[] = 'Все поля обязательны для заполнения.';
        } else {
            if (!is_numeric($salary)) {
                $errors[] = 'Зарплата должна быть числом.';
            }
            if (!preg_match('/^\d{10}$/', $passport)) {
                $errors[] = 'Паспорт должен содержать 10 цифр (4 серии и 6 номера).';
            }
            if (!preg_match('/^[78]\d{10}$/', $phone)) {
                $errors[] = 'Телефон должен быть в формате 8XXXXXXXXXX.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare(
                'INSERT INTO employees (full_name, birth_date, passport, phone, address, department, position, salary, hire_date)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            $stmt->bind_param('sssssssss', $full_name, $birth_date, $passport, $phone, $address, $department, $position, $salary, $hire_date);
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit;
        }
    } elseif ($action === 'fire') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $today = date('Y-m-d');
            $stmt = $conn->prepare('UPDATE employees SET is_fired = 1, fired_at = ? WHERE id = ?');
            $stmt->bind_param('si', $today, $id);
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit;
        }
    }
}

$filter_department = trim($_GET['filter_department'] ?? '');
$filter_position   = trim($_GET['filter_position'] ?? '');
$search_name       = trim($_GET['search_name'] ?? '');
$filter_status = trim($_GET['filter_status'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($filter_department !== '') {
    $where[]  = 'department = ?';
    $params[] = $filter_department;
    $types   .= 's';
}
if ($filter_position !== '') {
    $where[]  = 'position = ?';
    $params[] = $filter_position;
    $types   .= 's';
}
if ($search_name !== '') {
    $where[]  = 'full_name LIKE ?';
    $params[] = '%' . $search_name . '%';
    $types   .= 's';
}
if ($filter_status !== '') {
    if ($filter_status === 'working') {
        $where[] = 'is_fired = ?';
        $params[] = 0;
        $types .= 'i';
    } elseif ($filter_status === 'fired') {
        $where[] = 'is_fired = ?';
        $params[] = 1;
        $types .= 'i';
    }
}

$sql = 'SELECT * FROM employees';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY full_name';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$employees = $stmt->get_result();

$is_post_save = $_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'save');
$form_data = [
    'full_name' => '', 'birth_date' => '', 'passport' => '', 'phone' => '', 'address' => '',
    'department' => '', 'position' => '', 'salary' => '', 'hire_date' => '',
];
if ($is_post_save && !empty($errors)) {
    $form_data['full_name']  = trim($_POST['full_name'] ?? '');
    $form_data['birth_date'] = $_POST['birth_date'] ?? '';
    $form_data['passport']   = trim($_POST['passport'] ?? '');
    $form_data['phone']      = trim($_POST['phone'] ?? '');
    $form_data['address']    = trim($_POST['address'] ?? '');
    $form_data['department'] = trim($_POST['department'] ?? '');
    $form_data['position']   = trim($_POST['position'] ?? '');
    $form_data['salary']     = trim($_POST['salary'] ?? '');
    $form_data['hire_date']  = $_POST['hire_date'] ?? '';
}
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
        <details class="errors" open>
            <summary>Ошибки заполнения формы (<?php echo count($errors); ?>)</summary>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </details>
    <?php endif; ?>

    <div class="filters">
        <form method="get">
            <div>
                <label>Отдел:</label>
                <select name="filter_department">
                    <option value="">Все</option>
                    <?php foreach ($departments as $dep): ?>
                        <option value="<?php echo htmlspecialchars($dep); ?>" <?php echo $filter_department === $dep ? 'selected' : ''; ?>><?php echo htmlspecialchars($dep); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Должность:</label>
                <input type="text" name="filter_position" value="<?php echo htmlspecialchars($filter_position); ?>">
            </div>
            <div>
                <label>Статус:</label>
                <select name="filter_status">
                    <option value="" <?php echo $filter_status === '' ? 'selected' : ''; ?>>Все</option>
                    <option value="working" <?php echo $filter_status === 'working' ? 'selected' : ''; ?>>Работает</option>
                    <option value="fired" <?php echo $filter_status === 'fired' ? 'selected' : ''; ?>>Уволен</option>
                </select>
            </div>
            <div>
                <label>Поиск по ФИО:</label>
                <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>">
            </div>
            <button type="submit">Фильтровать</button>
            <a href="index.php" class="reset-link">Сбросить</a>
        </form>
    </div>

    <div class="form-block">
        <h2>Новый сотрудник</h2>
        <form method="post">
            <input type="hidden" name="action" value="save">
            <div class="row">
                <label>ФИО:</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($form_data['full_name']); ?>">
            </div>
            <div class="row">
                <label>Дата рождения:</label>
                <input type="date" name="birth_date" required value="<?php echo htmlspecialchars($form_data['birth_date']); ?>">
            </div>
            <div class="row">
                <label>Паспорт (серия/номер):</label>
                <input type="text" name="passport" id="passport" required value="<?php echo htmlspecialchars($form_data['passport']); ?>">
            </div>
            <div class="row">
                <label>Телефон:</label>
                <input type="tel" name="phone" id="phone" required placeholder="8XXXXXXXXXX" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
            </div>
            <div class="row">
                <label>Адрес:</label>
                <input type="text" name="address" required value="<?php echo htmlspecialchars($form_data['address']); ?>">
            </div>
            <div class="row">
                <label>Отдел:</label>
                <select name="department" required>
                    <option value="">Выберите отдел</option>
                    <?php foreach ($departments as $dep): ?>
                        <option value="<?php echo htmlspecialchars($dep); ?>" <?php echo $form_data['department'] === $dep ? 'selected' : ''; ?>><?php echo htmlspecialchars($dep); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <label>Должность:</label>
                <input type="text" name="position" required value="<?php echo htmlspecialchars($form_data['position']); ?>">
            </div>
            <div class="row">
                <label>Зарплата:</label>
                <input type="text" name="salary" required value="<?php echo htmlspecialchars($form_data['salary']); ?>">
            </div>
            <div class="row">
                <label>Дата принятия:</label>
                <input type="date" name="hire_date" required value="<?php echo htmlspecialchars($form_data['hire_date']); ?>">
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
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $employees->fetch_assoc()): ?>
            <tr class="<?php echo (int)$row['is_fired'] === 1 ? 'fired' : ''; ?>">
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['birth_date']); ?></td>
                <td><?php echo htmlspecialchars($row['passport']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td><?php echo htmlspecialchars($row['position']); ?></td>
                <td><?php echo htmlspecialchars($row['salary']); ?></td>
                <td><?php echo htmlspecialchars($row['hire_date']); ?></td>
                <td><?php echo (int)$row['is_fired'] === 1 ? 'Уволен' : 'Работает'; ?></td>
                <td>
                    <?php if ((int)$row['is_fired'] === 0): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="fire">
                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                            <button type="submit">Уволить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script>
var phoneInput = document.getElementById('phone');
if (phoneInput) phoneInput.addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 11); });
var passportInput = document.getElementById('passport');
if (passportInput) passportInput.addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 10); });
</script>
</body>
</html>
