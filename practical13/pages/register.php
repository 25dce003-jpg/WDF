<?php

session_start();

if (!isset($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

}

require_once "../db.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check CSRF token

    if (
        !isset($_POST["csrf_token"]) ||
        $_POST["csrf_token"] != $_SESSION["csrf_token"]
    ) {

        $message = "Invalid form submission.";

    }
    else {

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
        elseif (
            !preg_match(
                "/^[0-9]{2}[A-Z]{3}[0-9]{3}$/",
                $studentid
            )
        ) {

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

            try {

                // Check student in database

                $check = $conn->prepare(
                    "SELECT student_id, email
                     FROM students
                     WHERE student_id = :student_id
                     OR email = :email"
                );

                $check->execute([
                    ":student_id" => $studentid,
                    ":email" => $email
                ]);


                if ($check->fetch()) {

                    $message = "Student ID or Email already exists";

                }
                else {

                    // Hash password

                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    // Insert data into MySQL

                    $sql = "INSERT INTO students
                            (
                                student_id,
                                full_name,
                                email,
                                mobile,
                                course,
                                password
                            )
                            VALUES
                            (
                                :student_id,
                                :full_name,
                                :email,
                                :mobile,
                                :course,
                                :password
                            )";

                    $stmt = $conn->prepare($sql);

                    $stmt->execute([
                        ":student_id" => $studentid,
                        ":full_name" => $fullname,
                        ":email" => $email,
                        ":mobile" => $mobile,
                        ":course" => $course,
                        ":password" => $hashedPassword
                    ]);


                    // Save data into CSV

                    $file = "../data/students.csv";

                    $fp = fopen($file, "a");


                    if ($fp === false) {

                        $message =
                            "Saved in database but CSV file could not be opened.";

                    }
                    else {

                        // Add header if CSV is empty

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


                        // Add student data

                        fputcsv($fp, [
                            $studentid,
                            $fullname,
                            $email,
                            $mobile,
                            $course,
                            $hashedPassword
                        ]);

                        fclose($fp);


                        $message =
                            "Registration Successful! Data saved in CSV and Database.";

                    }

                }

            }
            catch (PDOException $e) {

                $message =
                    "Database error. Student was not registered.";

            }

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

    <title>StudentHub - Register</title>


    <link
        rel="stylesheet"
        href="../css/register.css">

</head>


<body>


    <!-- Header -->

    <header class="header">


        <div class="logo">

            <img
                src="../images/charusat.png"
                alt="CHARUSAT Logo">

        </div>


        <!-- Navigation -->

        <nav>

            <ul>



                <li>

                    <a href="register.php">
                        Register
                    </a>

                </li>


                 


            </ul>

        </nav>


        <!-- Login -->

        <div class="logout-text">

            Sign up

        </div>


    </header>



    <!-- Registration Section -->

    <main>

        <section>


            <h2>
                Student Registration
            </h2>


            <?php

            if ($message != "") {

                echo "<p>";

                echo htmlspecialchars($message);

                echo "</p>";

            }

            ?>


            <form
                method="POST"
                action="register.php">


                <!-- CSRF Token -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php
                        echo htmlspecialchars(
                            $_SESSION["csrf_token"]
                        );
                    ?>">



                <!-- Student ID -->

                <p>

                    <label for="studentid">
                        Student ID:
                    </label>

                    <br>

                    <input
                        type="text"
                        id="studentid"
                        name="studentid"
                        placeholder="Example: 25DCE001"
                        maxlength="8"
                        required>

                </p>



                <!-- Full Name -->

                <p>

                    <label for="fullname">
                        Full Name:
                    </label>

                    <br>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        placeholder="Enter your full name"
                        required>

                </p>



                <!-- Email -->

                <p>

                    <label for="email">
                        Email:
                    </label>

                    <br>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                </p>



                <!-- Mobile -->

                <p>

                    <label for="mobile">
                        Mobile Number:
                    </label>

                    <br>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        placeholder="Enter 10-digit mobile number"
                        maxlength="10"
                        required>

                </p>



                <!-- Course -->

                <p>

                    <label for="course">
                        Course:
                    </label>

                    <br>

                    <select
                        id="course"
                        name="course"
                        required>

                        <option value="">
                            Select Course
                        </option>

                        <option value="B.tech">
                            B.tech
                        </option>

                        <option value="BSc IT">
                            BSc IT
                        </option>

                        <option value="BCom">
                            BCom
                        </option>

                        <option value="BBA">
                            BBA
                        </option>

                    </select>

                </p>



                <!-- Password -->

                <p>

                    <label for="password">
                        Password:
                    </label>

                    <br>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required>

                </p>



                <!-- Confirm Password -->

                <p>

                    <label for="confirm">
                        Confirm Password:
                    </label>

                    <br>

                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="Confirm password"
                        required>

                </p>



                <!-- Buttons -->

                <button
                    type="submit">

                    Register

                </button>


                <button
                    type="reset">

                    Reset

                </button>


            </form>


        </section>

    </main>



    <!-- Footer -->

    <footer>

        <p>
            @2026 Student HUB Portal
        </p>

    </footer>


</body>

</html>