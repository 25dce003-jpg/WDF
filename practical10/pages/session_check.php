
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Prevent browser caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$timeoutSeconds = 15 * 60;

// Check whether the user is logged in
if (
    empty($_SESSION["student_id"]) ||
    empty($_SESSION["role"])
) {
    header("Location: login.php");
    exit;
}

// Check 15-minute inactivity timeout
if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] >= $timeoutSeconds
) {
    $_SESSION = [];

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

    $isHttps = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
    setcookie("studenthub_remember", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

// Refresh activity time
$_SESSION["last_activity"] = time();

?>