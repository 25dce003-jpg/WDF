
<?php
session_start();
require_once "../db.php";

$isHttps = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

// Remove the saved Remember me token
if (!empty($_SESSION["student_id"])) {
    $stmt = $conn->prepare(
        "UPDATE students
         SET remember_token_hash = NULL
         WHERE student_id = :id"
    );

    $stmt->execute([
        ":id" => $_SESSION["student_id"]
    ]);
}

// Clear Remember me cookie
setcookie("studenthub_remember", "", [
    "expires" => time() - 3600,
    "path" => "/",
    "secure" => $isHttps,
    "httponly" => true,
    "samesite" => "Lax"
]);

// Clear session data
$_SESSION = [];

// Remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 42000,
        "path" => $params["path"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"] ?? "Lax"
    ]);
}

session_destroy();

header("Location: login.php");
exit;
?>