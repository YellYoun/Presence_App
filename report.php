<?php
include('includes/db.php');
session_start();

// Check if the user is logged in and is an admin or professor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'professor'])) {
    header('Location: index.html');
    exit;
}

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : null;

    // Validate dates
    if ($start_date && $end_date) {
        $filename = "attendance_report_" . date('Ymd') . ".csv";

        // Set headers to download file
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        // Open output stream for writing CSV data
        $output = fopen('php://output', 'w');

        // Write column headers
        fputcsv($output, ['Student Name', 'Module Name', 'Date', 'Status']);

        // Query data for the given date range
        $query = "SELECT 
                    students.name AS student, 
                    modules.name AS module, 
                    attendance.date, 
                    attendance.status 
                  FROM attendance 
                  JOIN students ON attendance.student_id = students.id 
                  JOIN modules ON attendance.module_id = modules.id 
                  WHERE attendance.date BETWEEN '$start_date' AND '$end_date'";

        $result = mysqli_query($conn, $query);

        // Write data rows
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } else {
        $message = "Please provide both start and end dates.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Attendance Report</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Download Attendance Report</h1>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Form to Filter and Generate Report -->
        <form action="report.php" method="POST">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <button type="submit">Download Report</button>
        </form>
    </main>
</body>
</html>
