<?php
include('includes/db.php');
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Handle form submissions for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $module_name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $professor_id = isset($_POST['professor_id']) ? (int) $_POST['professor_id'] : null;
    $filiere_id = isset($_POST['filiere_id']) ? (int) $_POST['filiere_id'] : null;
    $module_id = isset($_POST['id']) ? (int) $_POST['id'] : null;

    if ($action === 'add' && $module_name && $professor_id && $filiere_id) {
        $sql = "INSERT INTO modules (name, professor_id, filiere_id) VALUES ('$module_name', $professor_id, $filiere_id)";
    } elseif ($action === 'edit' && $module_id && $module_name && $professor_id && $filiere_id) {
        $sql = "UPDATE modules SET name='$module_name', professor_id=$professor_id, filiere_id=$filiere_id WHERE id=$module_id";
    } elseif ($action === 'delete' && $module_id) {
        $sql = "DELETE FROM modules WHERE id=$module_id";
    }

    if (isset($sql) && mysqli_query($conn, $sql)) {
        $message = "Action completed successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Fetch all modules
$modules_query = "
    SELECT 
        modules.id, 
        modules.name AS module_name, 
        professors.name AS professor_name, 
        filieres.name AS filiere_name 
    FROM modules
    JOIN professors ON modules.professor_id = professors.id
    JOIN filieres ON modules.filiere_id = filieres.id
";
$modules_result = mysqli_query($conn, $modules_query);

// Fetch all professors
$professors_result = mysqli_query($conn, "SELECT * FROM professors");

// Fetch all filieres
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Manage Modules</h1>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Form to Add Module -->
        <section>
            <h2>Add New Module</h2>
            <form action="modules.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Module Name" required>
                <select name="professor_id" required>
                    <option value="">Select Professor</option>
                    <?php while ($professor = mysqli_fetch_assoc($professors_result)): ?>
                        <option value="<?= $professor['id'] ?>"><?= htmlspecialchars($professor['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="filiere_id" required>
                    <option value="">Select Filière</option>
                    <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                        <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Add Module</button>
            </form>
        </section>

        <!-- Display Existing Modules -->
        <section>
            <h2>Existing Modules</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Module Name</th>
                        <th>Professor</th>
                        <th>Filière</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($module = mysqli_fetch_assoc($modules_result)): ?>
                        <tr>
                            <td><?= $module['id'] ?></td>
                            <td><?= htmlspecialchars($module['module_name']) ?></td>
                            <td><?= htmlspecialchars($module['professor_name']) ?></td>
                            <td><?= htmlspecialchars($module['filiere_name']) ?></td>
                            <td>
                                <!-- Edit Form -->
                                <form action="modules.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= $module['id'] ?>">
                                    <input type="text" name="name" value="<?= htmlspecialchars($module['module_name']) ?>" required>
                                    <select name="professor_id" required>
                                        <?php
                                        $professors_result = mysqli_query($conn, "SELECT * FROM professors");
                                        while ($professor = mysqli_fetch_assoc($professors_result)):
                                            $selected = $professor['id'] == $module['professor_name'] ? 'selected' : '';
                                        ?>
                                            <option value="<?= $professor['id'] ?>" <?= $selected ?>><?= htmlspecialchars($professor['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <select name="filiere_id" required>
                                        <?php
                                        $filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
                                        while ($filiere = mysqli_fetch_assoc($filieres_result)):
                                            $selected = $filiere['id'] == $module['filiere_name'] ? 'selected' : '';
                                        ?>
                                            <option value="<?= $filiere['id'] ?>" <?= $selected ?>><?= htmlspecialchars($filiere['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit">Edit</button>
                                </form>

                                <!-- Delete Form -->
                                <form action="modules.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $module['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this module?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
