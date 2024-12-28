<?php
session_start();
include('includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch user role (admin or professor) from the session
$user_role = $_SESSION['role'];
$user_name = $_SESSION['username'];

// Navigation items based on user role
$nav_items = [
    'admin' => [
        'Manage Students' => 'students.php',
        'Manage Professors' => 'professors.php',
        'Manage Filières' => 'filieres.php',
        'Manage Modules' => 'modules.php',
        'Download Report' => 'report.php',
    ],
    'professor' => [
        'Mark Attendance' => 'attendance.php',
        'View Attendance Report' => 'report.php',
    ],
];

// Fetch the appropriate navigation for the user role
$navigation = $nav_items[$user_role];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Welcome, <?= htmlspecialchars($user_name) ?>!</h1>
        <nav>
            <ul>
                <?php foreach ($navigation as $label => $link): ?>
                    <li><a href="<?= $link ?>"><?= htmlspecialchars($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <form action="logout.php" method="POST">
            <button type="submit">Logout</button>
        </form>
    </header>
    <main>
        <h2>Dashboard</h2>
        <p>Use the navigation above to manage the system.</p>
        <?php if ($user_role === 'admin'): ?>
            <section>
                <h3>Quick Stats</h3>
                <ul>
                    <li>
                        <?php
                        $student_count = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS count FROM students'))['count'];
                        echo "Total Students: " . $student_count;
                        ?>
                    </li>
                    <li>
                        <?php
                        $professor_count = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS count FROM professors'))['count'];
                        echo "Total Professors: " . $professor_count;
                        ?>
                    </li>
                    <li>
                        <?php
                        $module_count = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS count FROM modules'))['count'];
                        echo "Total Modules: " . $module_count;
                        ?>
                    </li>
                </ul>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
