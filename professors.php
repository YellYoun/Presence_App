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
    $professor_name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $professor_email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $professor_id = isset($_POST['id']) ? (int) $_POST['id'] : null;

    if ($action === 'add' && $professor_name && $professor_email) {
        $sql = "INSERT INTO professors (name, email) VALUES ('$professor_name', '$professor_email')";
    } elseif ($action === 'edit' && $professor_id && $professor_name && $professor_email) {
        $sql = "UPDATE professors SET name='$professor_name', email='$professor_email' WHERE id=$professor_id";
    } elseif ($action === 'delete' && $professor_id) {
        $sql = "DELETE FROM professors WHERE id=$professor_id";
    }

    if (isset($sql) && mysqli_query($conn, $sql)) {
        $message = "Action completed successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Fetch all professors
$result = mysqli_query($conn, "SELECT * FROM professors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Professors</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Manage Professors</h1>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Form to Add Professor -->
        <section>
            <h2>Add New Professor</h2>
            <form action="professors.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Professor Name" required>
                <input type="email" name="email" placeholder="Professor Email" required>
                <button type="submit">Add Professor</button>
            </form>
        </section>

        <!-- Display Existing Professors -->
        <section>
            <h2>Existing Professors</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($professor = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $professor['id'] ?></td>
                            <td><?= htmlspecialchars($professor['name']) ?></td>
                            <td><?= htmlspecialchars($professor['email']) ?></td>
                            <td>
                                <!-- Edit Form -->
                                <form action="professors.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= $professor['id'] ?>">
                                    <input type="text" name="name" value="<?= htmlspecialchars($professor['name']) ?>" required>
                                    <input type="email" name="email" value="<?= htmlspecialchars($professor['email']) ?>" required>
                                    <button type="submit">Edit</button>
                                </form>

                                <!-- Delete Form -->
                                <form action="professors.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $professor['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this professor?')">Delete</button>
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
