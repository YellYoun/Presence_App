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

// Fetch data for tables
$professors_result = mysqli_query($conn, "SELECT * FROM professors");
$modules_result = mysqli_query($conn, "
    SELECT modules.id, modules.name AS module_name, professors.name AS professor_name, filieres.name AS filiere_name 
    FROM modules
    JOIN professors ON modules.professor_id = professors.id
    JOIN filieres ON modules.filiere_id = filieres.id
");
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <div class="container my-4">
            <h1>Welcome, <?= htmlspecialchars($user_name) ?>!</h1>
            <nav>
                <ul class="nav">
                    <?php foreach ($navigation as $label => $link): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $link ?>"><?= htmlspecialchars($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <form action="logout.php" method="POST">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </header>

    <main class="container">
        <h2>Dashboard</h2>
        <p>Use the navigation above to manage the system.</p>

        <?php if ($user_role === 'admin'): ?>
            <!-- Quick Stats -->
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

            <!-- Professors Table -->
            <section class="my-4">
                <h3>Professors</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($professor = mysqli_fetch_assoc($professors_result)): ?>
                            <tr>
                                <td><?= $professor['id'] ?></td>
                                <td><?= htmlspecialchars($professor['name']) ?></td>
                                <td><?= htmlspecialchars($professor['email']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Modules Table -->
            <section class="my-4">
                <h3>Modules</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Module Name</th>
                            <th>Professor</th>
                            <th>Filière</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($module = mysqli_fetch_assoc($modules_result)): ?>
                            <tr>
                                <td><?= $module['id'] ?></td>
                                <td><?= htmlspecialchars($module['module_name']) ?></td>
                                <td><?= htmlspecialchars($module['professor_name']) ?></td>
                                <td><?= htmlspecialchars($module['filiere_name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Filières Table -->
            <section class="my-4">
                <h3>Filières</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                            <tr>
                                <td><?= $filiere['id'] ?></td>
                                <td><?= htmlspecialchars($filiere['name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
