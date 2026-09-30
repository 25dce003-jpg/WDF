<?php

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $studentid = trim($_POST["studentid"]);
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $course = trim($_POST["course"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];


    // Validation

    if ($studentid == "") {
        $message = "Enter Student ID";
    }
    elseif (!preg_match("/^[0-9]{2}[A-Z]{3}[0-9]{3}$/", $studentid)) {
        $message = "Student ID must be like 25DCE003";
    }
    elseif ($fullname == "") {
        $message = "Enter your name";
    }
    elseif (!preg_match("/^[A-Za-z ]{3,}$/", $fullname)) {
        $message = "Enter a valid name";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email";
    }
    elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $message = "Mobile number must be 10 digits";
    }
    elseif ($course == "") {
        $message = "Select a course";
    }
    elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters";
    }
    elseif ($password != $confirm) {
        $message = "Passwords do not match";
    }
    else {

        $studentid = htmlspecialchars($studentid);
        $fullname = htmlspecialchars($fullname);
        $email = htmlspecialchars($email);
        $mobile = htmlspecialchars($mobile);
        $course = htmlspecialchars($course);

        $file = "students.csv";

        $exists = false;

        

        if (file_exists($file)) {

            $fp = fopen($file, "r");

            while (($row = fgetcsv($fp)) !== false) {

                if (isset($row[0]) && $row[0] == $studentid) {
                    $exists = true;
                    break;
                }

                if (isset($row[2]) && $row[2] == $email) {
                    $exists = true;
                    break;
                }
            }

            fclose($fp);
        }


        if ($exists) {

            $message = "Student ID or Email already exists";

        }
        else {

            $fp = fopen($file, "a");

            if (filesize($file) == 0) {

                fputcsv($fp, [
                    "Student ID",
                    "Full Name",
                    "Email",
                    "Mobile",
                    "Course",
                    "Password"
                ]);
            }

            fputcsv($fp, [
                $studentid,
                $fullname,
                $email,
                $mobile,
                $course,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            fclose($fp);

            $message = "Registration Successful!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Register</title>

    <link rel="stylesheet" href="../css/register.css">

</head>

<body>

    <header class="header">

        <div class="logo">
            <img src="../images/charusat.png" alt="CHARUSAT Logo">
        </div>

        <nav>

            <ul>

                <li><a href="profile.html">Profile</a></li>

                <li><a href="attendance.html">Attendance</a></li>

                <li><a href="assignment.html">Assignment</a></li>

                <li><a href="index.html">Dashboard</a></li>

                <li><a href="register.php">Register</a></li>

                <li><a href="result.html">Result</a></li>

                <li><a href="courses.html">Courses</a></li>

                <li><a href="contact.html">Contact</a></li>

            </ul>

        </nav>

        <div class="logout-text">
            <a href="login.html">Logout</a>
        </div>

    </header>


    <main>

        <section>

            <h2>Student Registration</h2>

            <?php

            if ($message != "") {
                echo "<p>$message</p>";
            }

            ?>

            <form method="POST" action="register.php">

                <p>

                    <label for="studentid">Student ID:</label><br>

                    <input
                        type="text"
                        id="studentid"
                        name="studentid"
                        placeholder="Example: 25DCE001"
                        maxlength="8"
                        required>

                </p>


                <p>

                    <label for="fullname">Full Name:</label><br>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        placeholder="Enter your full name"
                        required>

                </p>


                <p>

                    <label for="email">Email:</label><br>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                </p>


                <p>

                    <label for="mobile">Mobile Number:</label><br>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        placeholder="Enter 10-digit mobile number"
                        maxlength="10"
                        required>

                </p>


                <p>

                    <label for="course">Course:</label><br>

                    <select id="course" name="course" required>

                        <option value="">Select Course</option>

                        <option value="B.tech">B.tech</option>

                        <option value="BSc IT">BSc IT</option>

                        <option value="BCom">BCom</option>

                        <option value="BBA">BBA</option>

                    </select>

                </p>


                <p>

                    <label for="password">Password:</label><br>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required>

                </p>


                <p>

                    <label for="confirm">Confirm Password:</label><br>

                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="Confirm password"
                        required>

                </p>


                <button type="submit">Register</button>

                <button type="reset">Reset</button>

            </form>

        </section>

    </main>


    <footer>

        <p>@2026 Student HUB Portal</p>

    </footer>

</body>

</html>