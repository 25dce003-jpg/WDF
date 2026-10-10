
<?php
require_once "session_check.php";

if ($_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}
?><a href="logout.php">Logout</a>