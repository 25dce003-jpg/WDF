
<?php
session_start();
require_once "../db.php";

$isHttps = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
$timeout = 15 * 60;
$cookieName = "studenthub_remember";
$message = "";

// Redirect according to role
function redirectByRole($role)
{
    header("Location: " . ($role === "admin" ? "admin.php" : "index.php"));
    exit;
}

// Clear remember-me cookie
function clearRememberCookie($name, $isHttps)
{
    setcookie($name, "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

// Session timeout
if (isset($_SESSION["student_id"])) {
    if (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] >= $timeout
    ) {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(session_name(), "", [
                "expires" => time() - 3600,
                "path" => $params["path"],
                "secure" => $params["secure"],
                "httponly" => true,
                "samesite" => "Lax"
            ]);
        }

        session_destroy();
        clearRememberCookie($cookieName, $isHttps);

        header("Location: login.php?timeout=1");
        exit;
    }

    $_SESSION["last_activity"] = time();
}

// Timeout message
if (isset($_GET["timeout"])) {
    $message = "Session expired. Please log in again.";
}

// Already logged in
if (isset($_SESSION["student_id"])) {
    redirectByRole($_SESSION["role"] ?? "student");
}

// Remember Me
if (
    $_SERVER["REQUEST_METHOD"] !== "POST" &&
    !isset($_GET["timeout"]) &&
    !empty($_COOKIE[$cookieName])
) {
    $token = $_COOKIE[$cookieName];

    if (preg_match('/^[a-f0-9]{64}$/', $token)) {
        try {
            $stmt = $conn->prepare(
                "SELECT student_id, full_name, email, role
                 FROM students
                 WHERE remember_token_hash = :token"
            );

            $stmt->execute([
                ":token" => hash("sha256", $token)
            ]);

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["full_name"] = $student["full_name"];
                $_SESSION["email"] = $student["email"];
                $_SESSION["role"] = $student["role"];
                $_SESSION["last_activity"] = time();

                redirectByRole($student["role"]);
            }
        } catch (PDOException $e) {
            $message = "Database error. Please try again.";
        }
    }

    clearRememberCookie($cookieName, $isHttps);
}

// Login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentID = trim($_POST["studentID"] ?? "");
    $studentpass = $_POST["studentpass"] ?? "";
    $remember = isset($_POST["remember"]);

    if ($studentID === "") {
        $message = "Please enter your Student ID.";
    } elseif ($studentpass === "") {
        $message = "Please enter your password.";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT student_id, full_name, email, password, role
                 FROM students
                 WHERE student_id = :id"
            );

            $stmt->execute([
                ":id" => $studentID
            ]);

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                !$student ||
                !password_verify($studentpass, $student["password"])
            ) {
                $message = "Invalid Student ID or password.";
            } else {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["full_name"] = $student["full_name"];
                $_SESSION["email"] = $student["email"];
                $_SESSION["role"] = $student["role"];
                $_SESSION["last_activity"] = time();

                // Update last login and clear old remember token
                $stmt = $conn->prepare(
                    "UPDATE students
                     SET last_login = NOW(),
                         remember_token_hash = NULL
                     WHERE student_id = :id"
                );

                $stmt->execute([
                    ":id" => $student["student_id"]
                ]);

                // Remember Me for 30 days
                if ($remember) {
                    $token = bin2hex(random_bytes(32));

                    $stmt = $conn->prepare(
                        "UPDATE students
                         SET remember_token_hash = :token
                         WHERE student_id = :id"
                    );

                    $stmt->execute([
                        ":token" => hash("sha256", $token),
                        ":id" => $student["student_id"]
                    ]);

                    setcookie($cookieName, $token, [
                        "expires" => time() + (30 * 24 * 60 * 60),
                        "path" => "/",
                        "secure" => $isHttps,
                        "httponly" => true,
                        "samesite" => "Lax"
                    ]);
                } else {
                    clearRememberCookie($cookieName, $isHttps);
                }

                redirectByRole($student["role"]);
            }
        } catch (PDOException $e) {
            $message = "Database error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CHARUSAT | Student Login</title>

    <link rel="stylesheet" href="../css/login.css">
</head>

<body>

<header class="header">

    <div class="logo">
        <img src="../images/charusat.png" alt="CHARUSAT Logo">
    </div>

    <div class="page-title">
        <h2>STUDENT LOGIN</h2>
    </div>

    <div class="logout-text">
        <a href="login.php">Login</a>
    </div>

</header>

<main>

    <div class="login-container">

        <div class="login-heading">
            <h1>Welcome Back</h1>
            <p>Login to access your student portal</p>
        </div>

        <?php if ($message !== ""): ?>
            <p class="login-message">
                <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php">

            <div class="input-group">
                <label for="studentID">
                    Student ID <span>*</span>
                </label>

                <input
                    type="text"
                    id="studentID"
                    name="studentID"
                    placeholder="Enter your student ID"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="input-group">
                <label for="studentpass">
                    Password <span>*</span>
                </label>

                <input
                    type="password"
                    id="studentpass"
                    name="studentpass"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="login-options">

                <label class="remember">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>

                <a href="#">Forgot Password?</a>

            </div>

            <div class="submit-section">

                <button type="submit" class="login-btn">
                    Login
                </button>

                <button
                    type="button"
                    class="clear-btn"
                    onclick="window.location.href='role.php'"
                >
                    Sign up
                </button>

            </div>

        </form>

    </div>

</main>

<footer>
    <p>© 2026 CHARUSAT University | Student Portal</p>
</footer>

<script src="../js/login.js"></script>

</body>
</html>