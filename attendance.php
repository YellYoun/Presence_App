<?php
include('includes/db.php');
session_start();

// Check if the user is logged in and is an admin or professor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'professor'])) {
    header('Location: index.html');
    exit;
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_id = isset($_POST['module_id']) ? (int) $_POST['module_id'] : null;
    $attendance = isset($_POST['attendance']) ? $_POST['attendance'] : [];

    if ($module_id && !empty($attendance)) {
        foreach ($attendance as $student_id => $status) {
            $date = date('Y-m-d');
            $sql = "INSERT INTO attendance (student_id, module_id, date, status)
                    VALUES ($student_id, $module_id, '$date', '$status')
                    ON DUPLICATE KEY UPDATE status='$status'";
            mysqli_query($conn, $sql);
        }
        $message = "Attendance marked successfully!";
    } else {
        $message = "Please select a module and mark attendance.";
    }
}

// Fetch modules for the dropdown
$modules_result = mysqli_query($conn, "SELECT * FROM modules");

// Handle AJAX request to fetch students based on module
if (isset($_GET['module_id']) && !empty($_GET['module_id'])) {
    $module_id = (int) $_GET['module_id'];
    $students_result = mysqli_query($conn, "SELECT * FROM students WHERE filiere_id IN (SELECT filiere_id FROM modules WHERE id = $module_id)");

    if (mysqli_num_rows($students_result) > 0) {
        while ($student = mysqli_fetch_assoc($students_result)) {
            echo "<tr>
                    <td>" . htmlspecialchars($student['name']) . "</td>
                    <td>
                        <select name='attendance[" . $student['id'] . "]' required>
                            <option value='present'>Present</option>
                            <option value='absent'>Absent</option>
                        </select>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>No students found for this module.</td></tr>";
    }
    exit; // End script execution for AJAX requests
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script>
        // Fetch students dynamically when a module is selected
        function fetchStudents(moduleId) {
            const studentsContainer = document.getElementById('students-container');
            if (moduleId) {
                fetch(`attendance.php?module_id=${moduleId}`)
                    .then(response => response.text())
                    .then(data => {
                        studentsContainer.innerHTML = `
                            <h3>Students</h3>
                            <table border="1">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data}
                                </tbody>
                            </table>`;
                    })
                    .catch(err => {
                        console.error('Error fetching students:', err);
                        studentsContainer.innerHTML = `<p style="color: red;">Error fetching students. Please try again later.</p>`;
                    });
            } else {
                studentsContainer.innerHTML = `<p>Please select a module to view students.</p>`;
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Mark Attendance</h1>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Attendance Form -->
        <form action="attendance.php" method="POST">
            <label for="module_id">Select Module:</label>
            <select name="module_id" id="module_id" onchange="fetchStudents(this.value)" required>
                <option value="">Select Module</option>
                <?php while ($module = mysqli_fetch_assoc($modules_result)): ?>
                    <option value="<?= $module['id'] ?>"><?= htmlspecialchars($module['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <div id="students-container">
                <p>Please select a module to view students.</p>
            </div>

            <button type="submit">Submit Attendance</button>
        </form>
    </main>
</body>
</html>
