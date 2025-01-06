<?php
include('includes/db.php');
session_start();

// Check if the user is logged in and is an admin or professor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'professor'])) {
    header('Location: index.html');
    exit;
}

// Fetch Filières for the dropdown
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : null;
    $filiere_id = isset($_POST['filiere_id']) ? (int) $_POST['filiere_id'] : null;

    // Validate inputs
    if ($start_date && $end_date && $filiere_id) {
        $filename = "attendance_report_" . date('Ymd') . ".csv";

        // Set headers to download file
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        // Open output stream for writing CSV data
        $output = fopen('php://output', 'w');

        // Write column headers
        fputcsv($output, ['Student Name', 'Module Name', 'Date', 'Status']);

        // Query data for the given date range and Filière
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
            AND students.filiere_id = $filiere_id
        ";

        $result = mysqli_query($conn, $query);

        // Write data rows
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } else {
        $message = "Please provide both start and end dates and select a Filière.";
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
</head>
<body>
    <header class="container my-4">
        <h1>Download Attendance Report</h1>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
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
                <select id="filiere_id" name="filiere_id" class="form-select" required>
                    <option value="">Select Filière</option>
                    <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                        <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                    <?php endwhile; ?>
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
