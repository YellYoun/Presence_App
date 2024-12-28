<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Attendance Management System</h1>
            <nav>
                <ul>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="students.php">Students</a></li>
                        <li><a href="professors.php">Professors</a></li>
                        <li><a href="filieres.php">Filières</a></li>
                        <li><a href="modules.php">Modules</a></li>
                        <li><a href="attendance.php">Attendance</a></li>
                        <li><a href="report.php">Reports</a></li>
                    <?php elseif ($_SESSION['role'] === 'professor'): ?>
                        <li><a href="attendance.php">Attendance</a></li>
                        <li><a href="report.php">Reports</a></li>
                    <?php endif; ?>
                    <li>
                        <form action="logout.php" method="POST" style="display:inline;">
                            <button type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
