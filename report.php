<?php
include('includes/db.php');
session_start();

// Check if the user is logged in and is an admin or professor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'professor'])) {
    header('Location: index.html');
    exit;
}

// Handle AJAX request for fetching modules based on Filière
if (isset($_GET['filiere_id']) && !empty($_GET['filiere_id'])) {
    $filiere_id = (int)$_GET['filiere_id'];

    $modules_query = "SELECT id, name FROM modules WHERE filiere_id = $filiere_id";
    $modules_result = mysqli_query($conn, $modules_query);

    if (mysqli_num_rows($modules_result) > 0) {
        echo '<option value="">Select Module</option>';
        while ($module = mysqli_fetch_assoc($modules_result)) {
            echo '<option value="' . $module['id'] . '">' . htmlspecialchars($module['name']) . '</option>';
        }
    } else {
        echo '<option value="">No modules found for this Filière</option>';
    }
    exit;
}

// Fetch Filières for the dropdown
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : null;
    $module_id = isset($_POST['module_id']) ? (int)$_POST['module_id'] : null;

    // Validate inputs
    if ($start_date && $end_date && $module_id) {
        $filename = "attendance_report_" . date('Ymd') . ".csv";

        // Set headers to download file
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        // Open output stream for writing CSV data
        $output = fopen('php://output', 'w');

        // Write column headers
        fputcsv($output, ['Student Name', 'Module Name', 'Date', 'Status']);

        // Query data for the given date range and Module
        $query = "
            SELECT 
                students.name AS student, 
                modules.name AS module, 
                attendance.date, 
                attendance.status 
            FROM attendance 
            JOIN students ON attendance.student_id = students.id 
            JOIN modules ON attendance.module_id = modules.id 
            WHERE attendance.date BETWEEN '$start_date' AND '$end_date'
            AND attendance.module_id = $module_id
        ";

        $result = mysqli_query($conn, $query);

        // Write data rows
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } else {
        $message = "Please provide both start and end dates and select a Module.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Fetch modules dynamically based on selected Filière
        function fetchModules(filiereId) {
            const moduleSelect = document.getElementById('module_id');
            moduleSelect.innerHTML = '<option value="">Loading...</option>';

            fetch(`report.php?filiere_id=${filiereId}`)
                .then(response => response.text())
                .then(data => {
                    moduleSelect.innerHTML = data;
                })
                .catch(err => {
                    console.error('Error fetching modules:', err);
                    moduleSelect.innerHTML = '<option value="">Error loading modules</option>';
                });
        }
    </script>
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
    <header class="container my-4">
        <a href="dashboard.php" class="btn btn-secondary">←</a>
        <h1>Download Attendance Report</h1>
    </header>

    <main class="container">
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form to Filter and Generate Report -->
        <form action="report.php" method="POST" class="mb-4">
            <div class="mb-3">
                <label for="filiere_id" class="form-label">Select Filière:</label>
                <select id="filiere_id" name="filiere_id" class="form-select" onchange="fetchModules(this.value)" required>
                    <option value="">Select Filière</option>
                    <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                        <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="module_id" class="form-label">Select Module:</label>
                <select id="module_id" name="module_id" class="form-select" required>
                    <option value="">Select Filière first</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Download Report</button>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
