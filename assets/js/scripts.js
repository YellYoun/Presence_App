// Wait for the DOM to fully load
document.addEventListener("DOMContentLoaded", () => {
    console.log("JavaScript Loaded!");

    // Form Validation
    const forms = document.querySelectorAll("form");
    forms.forEach((form) => {
        form.addEventListener("submit", (e) => {
            let valid = true;
            const inputs = form.querySelectorAll("input[required], select[required]");
            inputs.forEach((input) => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add("error");
                } else {
                    input.classList.remove("error");
                }
            });

            if (!valid) {
                e.preventDefault();
                alert("Please fill in all required fields.");
            }
        });
    });

    // Confirmation for Delete Actions
    const deleteButtons = document.querySelectorAll("button[type='submit'][data-confirm]");
    deleteButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            const confirmMessage = button.getAttribute("data-confirm") || "Are you sure?";
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });

    // Dynamic Dropdown for Attendance Page
    const moduleSelect = document.getElementById("module_id");
    if (moduleSelect) {
        moduleSelect.addEventListener("change", () => {
            const moduleId = moduleSelect.value;
            const studentsContainer = document.getElementById("students-container");
            if (moduleId) {
                fetch(`attendance.php?module_id=${moduleId}`)
                    .then((response) => response.text())
                    .then((html) => {
                        studentsContainer.innerHTML = html;
                    })
                    .catch((error) => console.error("Error fetching students:", error));
            } else {
                studentsContainer.innerHTML = "<p>Please select a module to view students.</p>";
            }
        });
    }

    // Highlight Table Rows on Hover
    const tableRows = document.querySelectorAll("table tbody tr");
    tableRows.forEach((row) => {
        row.addEventListener("mouseover", () => {
            row.style.backgroundColor = "#f0f0f0";
        });
        row.addEventListener("mouseout", () => {
            row.style.backgroundColor = "";
        });
    });

    // Password Visibility Toggle (for Login Page)
    const passwordField = document.getElementById("password");
    const togglePasswordButton = document.createElement("button");
    if (passwordField) {
        togglePasswordButton.textContent = "Show";
        togglePasswordButton.type = "button";
        togglePasswordButton.style.marginLeft = "10px";
        passwordField.parentElement.appendChild(togglePasswordButton);

        togglePasswordButton.addEventListener("click", () => {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                togglePasswordButton.textContent = "Hide";
            } else {
                passwordField.type = "password";
                togglePasswordButton.textContent = "Show";
            }
        });
    }

    // Auto-Close Success Messages
    const messages = document.querySelectorAll("p");
    messages.forEach((message) => {
        if (message.classList.contains("success")) {
            setTimeout(() => {
                message.style.display = "none";
            }, 5000); // Auto-hide after 5 seconds
        }
    });
});
