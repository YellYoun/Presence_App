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
    $student_name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $student_email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $filiere_id = isset($_POST['filiere_id']) ? (int) $_POST['filiere_id'] : null;
    $student_id = isset($_POST['id']) ? (int) $_POST['id'] : null;


    if ($action === 'add' && $student_name && $student_email && $filiere_id) {
        $sql = "INSERT INTO students (name, email, filiere_id) VALUES ('$student_name', '$student_email', $filiere_id)";
    } elseif ($action === 'edit' && $student_id && $student_name && $student_email && $filiere_id) {
        $sql = "UPDATE students SET name='$student_name', email='$student_email', filiere_id=$filiere_id WHERE id=$student_id";
    } elseif ($action === 'delete' && $student_id) {
        $sql = "DELETE FROM students WHERE id=$student_id";
    }

    if (isset($sql) && mysqli_query($conn, $sql)) {
        $message = "Action completed successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Fetch all students and filières
$students_result = mysqli_query($conn, "SELECT students.id, students.name, students.email, filieres.name AS filiere 
                                         FROM students 
                                         JOIN filieres ON students.filiere_id = filieres.id");
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Manage Students</h1>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Form to Add Student -->
        <section>
            <h2>Add New Student</h2>
            <form action="students.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Student Name" required>
                <input type="email" name="email" placeholder="Student Email" required>
                <select name="filiere_id" required>
                    <option value="">Select Filière</option>
                    <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                        <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Add Student</button>
            </form>
        </section>

        <!-- Display Existing Students -->
        <section>
            <h2>Existing Students</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Filière</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                        <tr>
                            <td><?= $student['id'] ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['filiere']) ?></td>
                            <td>
                                <!-- Edit Form -->
                                <form action="students.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                    <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                                    <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                                    <select name="filiere_id" required>
                                        <?php
                                        $filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
                                        while ($filiere = mysqli_fetch_assoc($filieres_result)):
                                            $selected = $filiere['name'] === $student['filiere'] ? 'selected' : '';
                                        ?>
                                            <option value="<?= $filiere['id'] ?>" <?= $selected ?>><?= htmlspecialchars($filiere['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit">Edit</button>
                                </form>

                                <!-- Delete Form -->
                                <form action="students.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
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
