<?php
include('includes/db.php');
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle form submissions for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $module_name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $professor_id = isset($_POST['professor_id']) ? (int)$_POST['professor_id'] : null;
    $filiere_id = isset($_POST['filiere_id']) ? (int)$_POST['filiere_id'] : null;
    $module_id = isset($_POST['id']) ? (int)$_POST['id'] : null;

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

// Fetch modules with pagination
$modules_query = "
    SELECT 
        modules.id, 
        modules.name AS module_name, 
        professors.name AS professor_name, 
        filieres.name AS filiere_name 
    FROM modules
    JOIN professors ON modules.professor_id = professors.id
    JOIN filieres ON modules.filiere_id = filieres.id
    LIMIT $limit OFFSET $offset
";
$modules_result = mysqli_query($conn, $modules_query);

// Count total records for pagination
$total_query = "SELECT COUNT(*) AS total FROM modules";
$total_result = mysqli_fetch_assoc(mysqli_query($conn, $total_query));
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $limit);

// Cache professors and filières for dropdowns
$professors = [];
$professors_result = mysqli_query($conn, "SELECT * FROM professors");
while ($professor = mysqli_fetch_assoc($professors_result)) {
    $professors[] = $professor;
}

$filieres = [];
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");
while ($filiere = mysqli_fetch_assoc($filieres_result)) {
    $filieres[] = $filiere;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container my-4">
            <h1>Manage Modules</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </header>

    <main class="container">
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form to Add Module -->
        <section>
            <h2>Add New Module</h2>
            <form action="modules.php" method="POST" class="mb-4">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Module Name" required>
                </div>
                <div class="mb-3">
                    <select name="professor_id" class="form-select" required>
                        <option value="">Select Professor</option>
                        <?php foreach ($professors as $professor): ?>
                            <option value="<?= $professor['id'] ?>"><?= htmlspecialchars($professor['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <select name="filiere_id" class="form-select" required>
                        <option value="">Select Filière</option>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Module</button>
            </form>
        </section>

        <!-- Display Existing Modules -->
        <section>
            <h2>Existing Modules</h2>
            <table class="table table-striped">
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
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        data-id="<?= $module['id'] ?>"
                                        data-name="<?= htmlspecialchars($module['module_name']) ?>"
                                        data-professor-id="<?= $module['professor_name'] ?>"
                                        data-filiere-id="<?= $module['filiere_name'] ?>">
                                    Edit
                                </button>
                                <!-- Delete Form -->
                                <form action="modules.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $module['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this module?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="modules.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </main>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="modules.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Module</h5>
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
                        <label for="edit-professor" class="form-label">Professor</label>
                        <select name="professor_id" id="edit-professor" class="form-select" required>
                            <?php foreach ($professors as $professor): ?>
                                <option value="<?= $professor['id'] ?>"><?= htmlspecialchars($professor['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-filiere" class="form-label">Filière</label>
                        <select name="filiere_id" id="edit-filiere" class="form-select" required>
                            <?php foreach ($filieres as $filiere): ?>
                                <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                            <?php endforeach; ?>
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
            document.getElementById('edit-professor').value = button.getAttribute('data-professor-id');
            document.getElementById('edit-filiere').value = button.getAttribute('data-filiere-id');
        });
    </script>
</body>
</html>
