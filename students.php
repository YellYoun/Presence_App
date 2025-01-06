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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container my-4">
            <h1>Manage Students</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </header>

    <main class="container">
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form to Add Student -->
        <section>
            <h2>Add New Student</h2>
            <form action="students.php" method="POST" class="mb-4">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Student Name" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Student Email" required>
                </div>
                <div class="mb-3">
                    <select name="filiere_id" class="form-select" required>
                        <option value="">Select Filière</option>
                        <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                            <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </form>
        </section>

        <!-- Display Existing Students -->
        <section>
            <h2>Existing Students</h2>
            <table class="table table-striped">
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
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        data-id="<?= $student['id'] ?>"
                                        data-name="<?= htmlspecialchars($student['name']) ?>"
                                        data-email="<?= htmlspecialchars($student['email']) ?>"
                                        data-filiere="<?= $student['filiere'] ?>">
                                    Edit
                                </button>
                                <!-- Delete Form -->
                                <form action="students.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="students.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email</label>
                        <input type="email" name="email" id="edit-email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-filiere" class="form-label">Filière</label>
                        <select name="filiere_id" id="edit-filiere" class="form-select" required>
                            <?php
                            $filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
                            while ($filiere = mysqli_fetch_assoc($filieres_result)):
                            ?>
                                <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-name').value = button.getAttribute('data-name');
            document.getElementById('edit-email').value = button.getAttribute('data-email');
            document.getElementById('edit-filiere').value = button.getAttribute('data-filiere');
        });
    </script>
</body>
</html>
