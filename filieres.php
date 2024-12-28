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
    $filiere_name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $filiere_id = isset($_POST['id']) ? (int) $_POST['id'] : null;

    if ($action === 'add' && $filiere_name) {
        $sql = "INSERT INTO filieres (name) VALUES ('$filiere_name')";
    } elseif ($action === 'edit' && $filiere_id && $filiere_name) {
        $sql = "UPDATE filieres SET name='$filiere_name' WHERE id=$filiere_id";
    } elseif ($action === 'delete' && $filiere_id) {
        $sql = "DELETE FROM filieres WHERE id=$filiere_id";
    }

    if (isset($sql) && mysqli_query($conn, $sql)) {
        $message = "Action completed successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Fetch all filières
$result = mysqli_query($conn, "SELECT * FROM filieres");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Filières</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Manage Filières</h1>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Form to Add Filière -->
        <section>
            <h2>Add New Filière</h2>
            <form action="filieres.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Filière Name" required>
                <button type="submit">Add Filière</button>
            </form>
        </section>

        <!-- Display Existing Filières -->
        <section>
            <h2>Existing Filières</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($filiere = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $filiere['id'] ?></td>
                            <td><?= htmlspecialchars($filiere['name']) ?></td>
                            <td>
                                <!-- Edit Form -->
                                <form action="filieres.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= $filiere['id'] ?>">
                                    <input type="text" name="name" value="<?= htmlspecialchars($filiere['name']) ?>" required>
                                    <button type="submit">Edit</button>
                                </form>
                                
                                <!-- Delete Form -->
                                <form action="filieres.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $filiere['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this filière?')">Delete</button>
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
