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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <style>
        .container.my-4 {
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
        }
    </style>
    <header>
        <div class="container my-4">
            <a href="dashboard.php" class="btn btn-secondary">←</a>
            <h1>Manage Filières</h1>
        </div>
    </header>

    <main class="container">
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form to Add Filière -->
        <section>
            <h2>Add New Filière</h2>
            <form action="filieres.php" method="POST" class="mb-4">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Filière Name" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Filière</button>
            </form>
        </section>

        <!-- Display Existing Filières -->
        <section>
            <h2>Existing Filières</h2>
            <table class="table table-striped">
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
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        data-id="<?= $filiere['id'] ?>"
                                        data-name="<?= htmlspecialchars($filiere['name']) ?>">
                                    Edit
                                </button>
                                <!-- Delete Form -->
                                <form action="filieres.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $filiere['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this filière?')">Delete</button>
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
            <form action="filieres.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Filière</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
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
        });
    </script>
</body>
</html>
