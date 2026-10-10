<?php

// Start session securely
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");
    session_start();
}

// Prevent browser caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Project configuration
$loginPage = "/practical11/pages/login.php";
$timeoutSeconds = 15 * 60;

// Check login session
if (
    empty($_SESSION["student_id"]) ||
    empty($_SESSION["role"])
) {
    header("Location: " . $loginPage);
    exit;
}

// Validate allowed roles
$allowedRoles = ["student", "admin"];

if (!in_array($_SESSION["role"], $allowedRoles, true)) {
    $_SESSION = [];
    session_destroy();

    header("Location: " . $loginPage);
    exit;
}

// Check inactivity timeout
if (
    !isset($_SESSION["last_activity"]) ||
    !is_numeric($_SESSION["last_activity"]) ||
    (time() - (int) $_SESSION["last_activity"]) >= $timeoutSeconds
) {
    // Save user ID before clearing the session
    $studentId = $_SESSION["student_id"];

    // Invalidate the Remember-Me token in the database
    // if the database connection is available.
    try {
        require_once __DIR__ . "/../db.php";

        $stmt = $conn->prepare(
            "UPDATE students
             SET remember_token_hash = NULL
             WHERE student_id = :id"
        );

        $stmt->execute([":id" => $studentId]);

    } catch (Throwable $e) {
        error_log("Remember-me invalidation failed: " . $e->getMessage());
    }

    // Clear all session data
    $_SESSION = [];

    // Remove PHP session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(session_name(), "", [
            "expires" => time() - 3600,
            "path" => $params["path"] ?: "/",
            "secure" => $params["secure"],
            "httponly" => true,
            "samesite" => $params["samesite"] ?? "Lax"
        ]);
    }

    // Remove Remember-Me cookie
    $isHttps = (
        !empty($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    setcookie("studenthub_remember", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    // Destroy server-side session
    session_destroy();

    // Redirect to login with timeout message
    header("Location: " . $loginPage . "?timeout=1");
    exit;
}

// Refresh last activity timestamp
$_SESSION["last_activity"] = time();

?>