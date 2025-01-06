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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container my-4">
            <h1>Manage Professors</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </header>

    <main class="container">
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form to Add Professor -->
        <section>
            <h2>Add New Professor</h2>
            <form action="professors.php" method="POST" class="mb-4">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Professor Name" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Professor Email" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Professor</button>
            </form>
        </section>

        <!-- Display Existing Professors -->
        <section>
            <h2>Existing Professors</h2>
            <table class="table table-striped">
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
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        data-id="<?= $professor['id'] ?>"
                                        data-name="<?= htmlspecialchars($professor['name']) ?>"
                                        data-email="<?= htmlspecialchars($professor['email']) ?>">
                                    Edit
                                </button>
                                <!-- Delete Form -->
                                <form action="professors.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $professor['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this professor?')">Delete</button>
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
            <form action="professors.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Professor</h5>
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
        });
    </script>
</body>
</html>
