<?php

session_start();

require_once "../db.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $studentID = trim($_POST["studentID"] ?? "");
    $studentpass = $_POST["studentpass"] ?? "";


    if ($studentID == "") {

        $message = "Please enter your Student ID.";

    }
    elseif ($studentpass == "") {

        $message = "Please enter your password.";

    }
    else {

        try {

            // Find student in database

            $sql = "SELECT student_id, full_name, email, password
                    FROM students
                    WHERE student_id = :student_id";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ":student_id" => $studentID
            ]);

            $student = $stmt->fetch(PDO::FETCH_ASSOC);


            // Check Student ID and password

            if ($student === false) {

                $message = "Invalid Student ID or password.";

            }
            elseif (!password_verify(
                $studentpass,
                $student["password"]
            )) {

                $message = "Invalid Student ID or password.";

            }
            else {

                // Login successful

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["full_name"] = $student["full_name"];
                $_SESSION["email"] = $student["email"];

                header("Location: index.html");
                exit;

            }

        }
        catch (PDOException $e) {

            $message = "Database error.";

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

    <title>CHARUSAT | Student Login</title>

    <link
        rel="stylesheet"
        href="../css/login.css">

</head>


<body>


    <!-- Header -->

    <header class="header">

        <div class="logo">

            <img
                src="../images/charusat.png"
                alt="CHARUSAT Logo">

        </div>


        <div class="page-title">

            <h2>
                STUDENT LOGIN
            </h2>

        </div>


        <div class="logout-text">

            <a href="login.php">
                Login
            </a>

        </div>

    </header>



    <!-- Login Section -->

    <main>

        <div class="login-container">


            <div class="login-heading">

                <h1>
                    Welcome Back
                </h1>

                <p>
                    Login to access your student portal
                </p>

            </div>


            <?php

            if ($message != "") {

                echo "<p class='login-message'>";
                echo htmlspecialchars($message);
                echo "</p>";

            }

            ?>


            <form
                id="loginForm"
                method="POST"
                action="login.php">


                <!-- Student ID -->

                <div class="input-group">

                    <label for="studentID">

                        Student ID

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        id="studentID"
                        name="studentID"
                        placeholder="Enter your student ID"
                        autocomplete="username"
                        required>

                </div>



                <!-- Password -->

                <div class="input-group">

                    <label for="studentpass">

                        Password

                        <span>*</span>

                    </label>


                    <input
                        type="password"
                        id="studentpass"
                        name="studentpass"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>

                </div>



                <!-- Remember / Forgot -->

                <div class="login-options">

                    <label class="remember">

                        <input
                            type="checkbox"
                            name="remember">

                        <span>
                            Remember me
                        </span>

                    </label>


                    <a href="#">
                        Forgot Password?
                    </a>

                </div>



                <!-- Buttons -->

                <div class="submit-section">


                    <button
                        type="submit"
                        class="login-btn">

                        Login

                    </button>


                    <button
                        type="button"
                        class="clear-btn"
                        onclick="window.location.href='register.php'">

                        Sign up

                    </button>


                </div>


            </form>

        </div>

    </main>



    <!-- Footer -->

    <footer>

        <p>
            © 2026 CHARUSAT University | Student Portal
        </p>

    </footer>



    <!-- JavaScript -->

    <script src="../js/login.js"></script>


</body>

</html>