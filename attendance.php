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

// Fetch Filières for the dropdown
$filieres_result = mysqli_query($conn, "SELECT * FROM filieres");

// Handle AJAX requests
if (isset($_GET['filiere_id']) && !empty($_GET['filiere_id'])) {
    $filiere_id = (int) $_GET['filiere_id'];
    $modules_result = mysqli_query($conn, "SELECT * FROM modules WHERE filiere_id = $filiere_id");

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
    exit;
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
        function fetchModules(filiereId) {
            const moduleSelect = document.getElementById('module_id');
            const studentsContainer = document.getElementById('students-container');

            moduleSelect.innerHTML = '<option value="">Loading...</option>';
            studentsContainer.innerHTML = '<p>Please select a module to view students.</p>';

            fetch(`attendance.php?filiere_id=${filiereId}`)
                .then(response => response.text())
                .then(data => {
                    moduleSelect.innerHTML = data;
                })
                .catch(err => {
                    console.error('Error fetching modules:', err);
                    moduleSelect.innerHTML = '<option value="">Error loading modules</option>';
                });
        }

        function fetchStudents(moduleId) {
            const studentsContainer = document.getElementById('students-container');
            studentsContainer.innerHTML = '<p>Loading students...</p>';

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
                    studentsContainer.innerHTML = '<p>Error loading students. Please try again later.</p>';
                });
        }
    </script>
</head>
<body>
    
    <style>

        header {
            width: 100%;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
        }
        header nav {
            margin: 0;
        }
        header nav a {
            margin: 0;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #000;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
        }
        .container.my-4 {
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
        }

        main {
            width: 100%;
            min-height: calc(100vh - 120px);

            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            width: 100%;
            max-width: 500px;
        }
        section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        section header {
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .attendance-form button {
            margin-top: 10px;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
    </style>
    
    <header>
        <nav>
            <!-- <a href="dashboard.php">Back to Dashboard</a> -->
            <a href="dashboard.php" class="btn btn-secondary">←</a>
        </nav>
        <h1>Mark Attendance</h1>
    </header>

    <main>
        <!-- Display messages -->
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Attendance Form -->
        <form action="attendance.php" method="POST" class="attendance-form">
            <label for="filiere_id">Select Filière:</label>
            <select name="filiere_id" id="filiere_id" onchange="fetchModules(this.value)" required>
                <option value="">Select Filière</option>
                <?php while ($filiere = mysqli_fetch_assoc($filieres_result)): ?>
                    <option value="<?= $filiere['id'] ?>"><?= htmlspecialchars($filiere['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="module_id">Select Module:</label>
            <select name="module_id" id="module_id" onchange="fetchStudents(this.value)" required>
                <option value="">Select a Filière first</option>
            </select>

            <div id="students-container">
                <p>Please select a module to view students.</p>
            </div>

            <button type="submit">Submit Attendance</button>
        </form>
    </main>
</body>
</html>
