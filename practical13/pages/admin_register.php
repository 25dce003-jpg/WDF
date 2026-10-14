<?php

require_once "../db.php";

$message = "";

// Process form

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $studentid = trim($_POST["studentid"] ?? "");
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm"] ?? "";

    // Validate inputs

    if (!preg_match("/^[0-9]{2}[A-Z]{3}[0-9]{3}$/", $studentid)) {

        $message = "Enter a valid ID, for example 25DCE003.";

    } elseif (!preg_match("/^[A-Za-z ]{3,}$/", $fullname)) {

        $message = "Enter a valid full name.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Enter a valid email address.";

    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Mobile number must contain 10 digits.";

    } elseif ($course === "") {

        $message = "Please select a department.";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";

    } elseif ($password !== $confirm) {

        $message = "Passwords do not match.";

    } else {

        try {

            // Check duplicate student ID or email

            $check = $conn->prepare(
                "SELECT student_id
                 FROM students
                 WHERE student_id = :student_id
                 OR email = :email"
            );

            $check->execute([
                ":student_id" => $studentid,
                ":email" => $email
            ]);

            if ($check->fetch()) {

                $message = "Student ID or email already exists.";

            } else {

                // Hash password before storing

                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Create administrator account

                $sql = "INSERT INTO students
                        (
                            student_id,
                            full_name,
                            email,
                            mobile,
                            course,
                            password,
                            role
                        )
                        VALUES
                        (
                            :student_id,
                            :full_name,
                            :email,
                            :mobile,
                            :course,
                            :password,
                            :role
                        )";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    ":student_id" => $studentid,
                    ":full_name" => $fullname,
                    ":email" => $email,
                    ":mobile" => $mobile,
                    ":course" => $course,
                    ":password" => $hashedPassword,
                    ":role" => "admin"
                ]);

                $message = "Admin account created successfully!";

            }

        } catch (PDOException $e) {

            $message = "Database error. Admin account was not created.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Admin Registration</title>

    <link
        rel="stylesheet"
        href="../css/register.css">

    <style>

        .admin-heading {
            text-align: center;
            margin-bottom: 20px;
        }

        .admin-message {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #f0f5ff;
            border-radius: 5px;
        }

        .role-display {
            padding: 10px;
            background: #eeeeee;
            border-radius: 5px;
            display: inline-block;
        }

        .back-link {
            text-decoration: none;
            font-weight: bold;
        }

    </style>

</head>

<body>

    <header class="header">

        <div class="logo">
            <img
                src="../images/charusat.png"
                alt="CHARUSAT Logo">
        </div>

        <h2>ADMIN REGISTRATION</h2>

        <div class="logout-text">
            <a href="admin.php">Dashboard</a>
        </div>

    </header>

    <main>

        <section>

            <h2 class="admin-heading">
                Create Admin Account
            </h2>

            <?php if ($message !== ""): ?>

                <p class="admin-message">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </p>

            <?php endif; ?>

            <form
                method="POST"
                action="admin_register.php">

                <p>
                    <label for="studentid">Admin ID:</label>
                    <br>
                    <input
                        type="text"
                        id="studentid"
                        name="studentid"
                        maxlength="8"
                        placeholder="Example: 25DCE003"
                        pattern="[0-9]{2}[A-Z]{3}[0-9]{3}"
                        required>
                </p>

                <p>
                    <label for="fullname">Full Name:</label>
                    <br>
                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        minlength="3"
                        required>
                </p>

                <p>
                    <label for="email">Email:</label>
                    <br>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required>
                </p>

                <p>
                    <label for="mobile">Mobile Number:</label>
                    <br>
                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        required>
                </p>

                <p>
                    <label for="course">Department:</label>
                    <br>

                    <select id="course" name="course" required>
                        <option value="">Select Department</option>
                        <option value="Administration">Administration</option>
                        <option value="B.tech">B.tech</option>
                        <option value="BSc IT">BSc IT</option>
                        <option value="BCom">BCom</option>
                        <option value="BBA">BBA</option>
                    </select>
                </p>

                <p>
                    <label>Role:</label>
                    <br>
                    <span class="role-display">Administrator</span>
                </p>

                <p>
                    <label for="password">Password:</label>
                    <br>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required>
                </p>

                <p>
                    <label for="confirm">Confirm Password:</label>
                    <br>
                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        minlength="8"
                        autocomplete="new-password"
                        required>
                </p>

                <button type="submit">Create Admin</button>

                <button type="reset">Reset</button>

            </form>

            <p style="text-align:center; margin-top:20px;">
                <a class="back-link" href="admin.php">
                    Back to Admin Dashboard
                </a>
            </p>

        </section>

    </main>

    <footer>
        <p>© 2026 CHARUSAT University | StudentHub</p>
    </footer>

</body>

</html>
