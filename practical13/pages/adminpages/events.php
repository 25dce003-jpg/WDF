
<?php
require_once "../session_check.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

require_once "../../db.php";

if (empty($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

$uploadDir = dirname(__DIR__, 2) . "/uploads/event_posters/";
$uploadUrl = "../../uploads/event_posters/";
$maxSize = 2 * 1024 * 1024;

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$message = "";
$messageType = "success";
$editEvent = null;

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function savePoster(array $file, string $dir, int $maxSize): ?string
{
    $error = $file["error"] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Poster upload failed.");
    }

    if (
        empty($file["tmp_name"]) ||
        !is_uploaded_file($file["tmp_name"]) ||
        empty($file["size"]) ||
        $file["size"] > $maxSize
    ) {
        throw new RuntimeException("Poster must be smaller than 2 MB.");
    }

    if (!is_dir($dir) || !is_writable($dir)) {
        throw new RuntimeException("Poster upload folder is not writable.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Only JPG, PNG and WebP images are allowed.");
    }

    $imageInfo = @getimagesize($file["tmp_name"]);

    if (!$imageInfo || ($imageInfo["mime"] ?? "") !== $mime) {
        throw new RuntimeException("Invalid image file.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

    if (!move_uploaded_file($file["tmp_name"], $dir . $filename)) {
        throw new RuntimeException("Could not save the poster.");
    }

    return $filename;
}

function removePoster(?string $filename, string $dir): void
{
    if (!$filename || basename($filename) !== $filename) {
        return;
    }

    $path = $dir . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}

/* Process CRUD requests */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $token = $_POST["csrf_token"] ?? "";

        if (
            !is_string($token) ||
            !hash_equals($_SESSION["event_csrf"], $token)
        ) {
            throw new RuntimeException("Invalid request. Please refresh the page.");
        }

        $action = $_POST["action"] ?? "";

        if ($action === "delete") {
            $id = filter_var($_POST["event_id"] ?? null, FILTER_VALIDATE_INT);

            if (!$id || $id < 1) {
                throw new RuntimeException("Invalid event ID.");
            }

            $stmt = $conn->prepare(
                "SELECT poster_path FROM events WHERE event_id = :id"
            );
            $stmt->execute([":id" => $id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                throw new RuntimeException("Event not found.");
            }

            $stmt = $conn->prepare(
                "DELETE FROM events WHERE event_id = :id"
            );
            $stmt->execute([":id" => $id]);

            removePoster($event["poster_path"] ?? null, $uploadDir);

            $message = "Event deleted successfully.";
            $messageType = "success";

        } elseif ($action === "save") {
            $rawId = $_POST["event_id"] ?? "";
            $isEdit = $rawId !== "";
            $id = null;

            if ($isEdit) {
                $id = filter_var($rawId, FILTER_VALIDATE_INT);

                if (!$id || $id < 1) {
                    throw new RuntimeException("Invalid event ID.");
                }
            }

            $name = trim($_POST["event_name"] ?? "");
            $date = trim($_POST["event_date"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $category = trim($_POST["category"] ?? "");
            $status = $_POST["status"] ?? "Open";

            if ($name === "" || strlen($name) > 150) {
                throw new RuntimeException(
                    "Enter an event name up to 150 characters."
                );
            }

            $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

            if (!$parsedDate || $parsedDate->format("Y-m-d") !== $date) {
                throw new RuntimeException("Please select a valid event date.");
            }

            if (strlen($description) > 5000) {
                throw new RuntimeException("Description is too long.");
            }

            if (strlen($category) > 100) {
                throw new RuntimeException("Category must be under 100 characters.");
            }

            if (!in_array($status, ["Open", "Closed"], true)) {
                throw new RuntimeException("Invalid event status.");
            }

            $oldPoster = null;

            if ($isEdit) {
                $stmt = $conn->prepare(
                    "SELECT poster_path FROM events WHERE event_id = :id"
                );
                $stmt->execute([":id" => $id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing) {
                    throw new RuntimeException("Event not found.");
                }

                $oldPoster = $existing["poster_path"] ?? null;
            }

            $newPoster = savePoster(
                $_FILES["poster"] ?? ["error" => UPLOAD_ERR_NO_FILE],
                $uploadDir,
                $maxSize
            );

            $poster = $newPoster ?? $oldPoster;

            if ($isEdit) {
                $stmt = $conn->prepare(
                    "UPDATE events
                     SET event_name = :name,
                         event_date = :date,
                         description = :description,
                         category = :category,
                         status = :status,
                         poster_path = :poster
                     WHERE event_id = :id"
                );

                $stmt->execute([
                    ":name" => $name,
                    ":date" => $date,
                    ":description" => $description,
                    ":category" => $category,
                    ":status" => $status,
                    ":poster" => $poster,
                    ":id" => $id
                ]);

                if ($newPoster !== null) {
                    removePoster($oldPoster, $uploadDir);
                }

                $message = "Event updated successfully.";

            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO events
                    (event_name, event_date, description,
                     category, status, poster_path)
                    VALUES
                    (:name, :date, :description,
                     :category, :status, :poster)"
                );

                $stmt->execute([
                    ":name" => $name,
                    ":date" => $date,
                    ":description" => $description,
                    ":category" => $category,
                    ":status" => $status,
                    ":poster" => $newPoster
                ]);

                $message = "Event added successfully.";
            }

            $messageType = "success";

        } else {
            throw new RuntimeException("Invalid action.");
        }

    } catch (RuntimeException $ex) {
        $message = $ex->getMessage();
        $messageType = "error";

    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $message = "Database operation failed. Check your events table columns.";
        $messageType = "error";
    }

    $_SESSION["event_flash"] = [
        "message" => $message,
        "type" => $messageType
    ];

    header("Location: events.php");
    exit;
}

/* Flash message */
if (isset($_SESSION["event_flash"])) {
    $message = $_SESSION["event_flash"]["message"];
    $messageType = $_SESSION["event_flash"]["type"];
    unset($_SESSION["event_flash"]);
}

/* Edit mode */
$editId = filter_input(INPUT_GET, "edit", FILTER_VALIDATE_INT);

if ($editId) {
    $stmt = $conn->prepare(
        "SELECT * FROM events WHERE event_id = :id"
    );
    $stmt->execute([":id" => $editId]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (!$editEvent) {
        $message = "Event not found.";
        $messageType = "error";
    }
}

/* Search and status filter */
$search = trim($_GET["search"] ?? "");
$statusFilter = $_GET["status"] ?? "";

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($search !== "") {
    $sql .= " AND (event_name LIKE :search OR category LIKE :search)";
    $params[":search"] = "%" . $search . "%";
}

if (in_array($statusFilter, ["Open", "Closed"], true)) {
    $sql .= " AND status = :status";
    $params[":status"] = $statusFilter;
}

$sql .= " ORDER BY event_date DESC, event_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Statistics */
$stmt = $conn->query("SELECT COUNT(*) FROM events");
$totalAll = (int)$stmt->fetchColumn();

$stmt = $conn->query(
    "SELECT COUNT(*) FROM events WHERE status = 'Open'"
);
$totalOpen = (int)$stmt->fetchColumn();

$stmt = $conn->query(
    "SELECT COUNT(*) FROM events WHERE status = 'Closed'"
);
$totalClosed = (int)$stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Events | Student HUB</title>

<!-- All styling is inside this file. No register.css link. -->

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    min-height: 100vh;
    background: linear-gradient(135deg, #87ceeb 0%, #dff6ff 100%);
    font-family: "Segoe UI", Arial, sans-serif;
    color: #202124;
    display: flex;
    flex-direction: column;
}

/* Header and original CHARUSAT styling */
.header {
    background: rgba(255,255,255,.97);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 5%;
    gap: 25px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
}

.logo {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.logo img {
    width: 100px;
    height: auto;
    display: block;
    transition: transform .3s ease;
}

.logo img:hover {
    transform: scale(1.05);
}

nav {
    flex: 1;
    background: #fffde7;
    border-radius: 40px;
    padding: 10px 18px;
    margin: 0 10px;
    border: 1px solid rgba(10,29,143,.08);
    box-shadow: 0 3px 10px rgba(0,0,0,.06);
}

nav ul {
    display: flex;
    justify-content: space-evenly;
    align-items: center;
    list-style: none;
    gap: 10px;
}

nav a {
    display: block;
    text-decoration: none;
    color: #0a1d8f;
    font-size: 16px;
    font-weight: 700;
    padding: 10px 12px;
    border-radius: 20px;
    transition: background .3s ease, color .3s ease, transform .3s ease;
}

nav a:hover,
nav a:focus {
    color: white;
    background: #0a1d8f;
    transform: translateY(-2px);
}

/* Main layout */
main {
    flex: 1;
    width: min(1450px, 96%);
    margin: 0 auto;
    padding: 35px 20px 45px;
}

.page-heading {
    margin-bottom: 24px;
}

.eyebrow {
    color: #3158d8;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.page-heading h1 {
    color: #172d4d;
    font-size: clamp(28px, 3vw, 38px);
    line-height: 1.2;
    margin-bottom: 10px;
}

.page-heading p {
    color: #667b99;
    line-height: 1.6;
}

/* Notices */
.notice {
    margin-bottom: 22px;
    padding: 14px 18px;
    border-radius: 10px;
    font-weight: 600;
}

.notice.success {
    color: #17643a;
    background: #e9f8ef;
    border: 1px solid #a7e4bb;
}

.notice.error {
    color: #a32637;
    background: #fff0f1;
    border: 1px solid #f2c2c8;
}

/* Statistics */
.stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 26px;
}

.stat-card {
    background: rgba(255,255,255,.97);
    padding: 22px;
    border-radius: 16px;
    border: 1px solid #e0e8f3;
    box-shadow: 0 8px 25px rgba(23,45,77,.08);
}

.stat-card span {
    display: block;
    color: #667b99;
    font-size: 14px;
    margin-bottom: 9px;
}

.stat-card strong {
    color: #172d4d;
    font-size: 32px;
}

/* Main content panels */
.panel {
    background: rgba(255,255,255,.98);
    border: 1px solid #e0e8f3;
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 26px;
    box-shadow: 0 10px 30px rgba(23,45,77,.09);
    min-width: 0;
}

.panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 23px;
}

.panel-heading h2 {
    color: #0a1d8f;
    font-size: 23px;
    margin-bottom: 8px;
}

.panel-heading p {
    color: #71829b;
    line-height: 1.5;
    font-size: 14px;
}

.mode-badge {
    flex-shrink: 0;
    background: #eaf0ff;
    color: #3158d8;
    border-radius: 30px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 800;
}

/* Form */
.event-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px 24px;
}

.form-group {
    min-width: 0;
}

.form-group.full-width,
.form-actions {
    grid-column: 1 / -1;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #253b58;
    font-size: 14px;
    font-weight: 700;
}

input[type="text"],
input[type="search"],
input[type="date"],
input[type="file"],
select,
textarea {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    padding: 12px 14px;
    border: 1px solid #d6e0ef;
    border-radius: 10px;
    background: white;
    color: #263750;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}

input[type="text"],
input[type="search"],
input[type="date"],
select {
    min-height: 46px;
}

input[type="file"] {
    padding: 10px;
    background: #fbfdff;
}

textarea {
    min-height: 115px;
    resize: vertical;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #3158d8;
    box-shadow: 0 0 0 3px rgba(49,88,216,.12);
}

input::placeholder,
textarea::placeholder {
    color: #91a0b5;
}

.form-group small {
    display: block;
    color: #7c8ba1;
    font-size: 12px;
    margin-top: 7px;
}

.current-poster {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 12px;
    color: #667b99;
    font-size: 13px;
}

.current-poster img {
    width: 100px;
    height: 75px;
    object-fit: cover;
    border: 1px solid #e0e8f3;
    border-radius: 8px;
}

/* Buttons */
button,
.button-link {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border: 0;
    border-radius: 9px;
    padding: 12px 19px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: background .2s, transform .2s, box-shadow .2s;
}

button[type="submit"] {
    background: #0a1d8f;
    color: white;
}

button[type="submit"]:hover {
    background: #071568;
    transform: translateY(-1px);
    box-shadow: 0 5px 12px rgba(10,29,143,.18);
}

button[type="reset"] {
    background: #e9eef3;
    color: #263750;
}

button[type="reset"]:hover {
    background: #d7e0e9;
}

.button-link {
    background: #edf2fa;
    color: #233b60;
}

.button-link:hover {
    background: #dce7f7;
}

.form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 2px;
}

button:focus-visible,
a:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible {
    outline: 3px solid #ff9800;
    outline-offset: 2px;
}

/* Search controls */
.filters {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) minmax(140px, 190px) auto auto;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.filters button,
.filters .button-link {
    min-height: 46px;
    white-space: nowrap;
}

.results-label {
    color: #71829b;
    font-size: 13px;
    margin-bottom: 14px;
}

/* Table */
.table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e0e8f3;
    border-radius: 12px;
}

table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    background: white;
}

thead {
    background: #f0f4fc;
}

th {
    color: #425675;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .4px;
}

th,
td {
    padding: 16px 14px;
    border-bottom: 1px solid #e8edf5;
    text-align: left;
    vertical-align: middle;
}

td {
    color: #526681;
    font-size: 13px;
    line-height: 1.5;
}

tbody tr:last-child td {
    border-bottom: 0;
}

tbody tr:hover {
    background: #fbfdff;
}

.event-name {
    color: #203b60;
    font-size: 14px;
    font-weight: 800;
}

.poster {
    display: block;
    width: 76px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e0e8f3;
}

.no-poster {
    display: inline-block;
    padding: 12px 9px;
    border: 1px dashed #ccd5e4;
    border-radius: 8px;
    color: #8591a5;
    background: #f8faff;
    font-size: 11px;
    white-space: nowrap;
}

.status-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
}

.status-open {
    background: #e5f7ec;
    color: #177245;
}

.status-closed {
    background: #fff2dc;
    color: #99620c;
}

.actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 7px;
}

.actions form {
    margin: 0;
}

.actions .button-link,
.actions button {
    padding: 9px 12px;
    font-size: 12px;
}

.actions button[type="submit"] {
    background: #fff0f1;
    color: #b42332;
}

.actions button[type="submit"]:hover {
    background: #fbdadd;
}

.empty-state {
    padding: 36px 20px;
    color: #71829b;
    text-align: center;
}

/* Footer */
footer {
    background: white;
    color: #555;
    text-align: center;
    padding: 18px 10px;
    margin-top: auto;
    border-top: 1px solid #e7edf5;
}

footer p {
    font-size: 14px;
}

/* Responsive layout */
@media (max-width: 1100px) {
    .header {
        flex-wrap: wrap;
        justify-content: center;
    }

    nav {
        flex-basis: 100%;
        margin: 5px 0;
    }

    nav ul {
        flex-wrap: wrap;
    }

    .filters {
        grid-template-columns: minmax(0, 1fr) minmax(140px, 190px);
    }

    .filters button,
    .filters .button-link {
        width: 100%;
    }
}

@media (max-width: 700px) {
    .header {
        padding: 12px 15px;
        gap: 12px;
    }

    .logo img {
        width: 85px;
    }

    nav {
        border-radius: 18px;
        padding: 8px;
    }

    nav ul {
        gap: 5px;
    }

    nav a {
        font-size: 13px;
        padding: 9px 10px;
    }

    main {
        width: 100%;
        padding: 24px 14px 32px;
    }

    .page-heading h1 {
        font-size: 29px;
    }

    .stats {
        gap: 9px;
    }

    .stat-card {
        padding: 14px 10px;
    }

    .stat-card span {
        font-size: 11px;
    }

    .stat-card strong {
        font-size: 25px;
    }

    .panel {
        padding: 19px 15px;
        border-radius: 14px;
    }

    .panel-heading h2 {
        font-size: 21px;
    }

    .event-form {
        grid-template-columns: minmax(0, 1fr);
        gap: 17px;
    }

    .form-group,
    .form-group.full-width,
    .form-actions {
        grid-column: 1;
    }

    .filters {
        grid-template-columns: minmax(0, 1fr);
    }

    .filters button,
    .filters .button-link {
        width: 100%;
    }
}

@media (max-width: 400px) {
    .stats {
        grid-template-columns: 1fr;
    }

    .stat-card {
        padding: 15px;
    }

    .panel-heading {
        flex-direction: column;
    }

    .form-actions button,
    .form-actions .button-link {
        width: 100%;
    }
}
</style>
</head>

<body>

<header class="header">
    <div class="logo">
        <img src="../../images/charusat.png" alt="CHARUSAT Logo">
    </div>

    <nav>
        <ul>
            <li><a href="../Admin.php">Admin Dashboard</a></li>
            <li><a href="events.php">Manage Events</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>

    <section class="page-heading">
        <div class="eyebrow">Admin Panel</div>
        <h1>Event Management</h1>
        <p>Create, update and manage your campus events.</p>
    </section>

    <?php if ($message !== ""): ?>
        <div class="notice <?= e($messageType) ?>" role="status">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="stats">
        <article class="stat-card">
            <span>Total Events</span>
            <strong><?= $totalAll ?></strong>
        </article>

        <article class="stat-card">
            <span>Open Events</span>
            <strong><?= $totalOpen ?></strong>
        </article>

        <article class="stat-card">
            <span>Closed Events</span>
            <strong><?= $totalClosed ?></strong>
        </article>
    </section>

    <section class="panel" id="event-form">
        <div class="panel-heading">
            <div>
                <h2><?= $editEvent ? "Update Event" : "Add Event" ?></h2>
                <p>
                    <?= $editEvent
                        ? "Update the event information below."
                        : "Fill in the details to create a campus event." ?>
                </p>
            </div>

            <?php if ($editEvent): ?>
                <span class="mode-badge">EDIT MODE</span>
            <?php endif; ?>
        </div>

        <form class="event-form"
              method="POST"
              action="events.php"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION["event_csrf"]) ?>">
            <input type="hidden" name="action" value="save">

            <?php if ($editEvent): ?>
                <input type="hidden" name="event_id"
                       value="<?= (int)$editEvent["event_id"] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="event_name">Event Name *</label>
                <input type="text" id="event_name" name="event_name"
                       maxlength="150" required
                       placeholder="Enter event name"
                       value="<?= e($editEvent["event_name"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" id="event_date" name="event_date"
                       required
                       value="<?= e($editEvent["event_date"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="category">Event Category</label>
                <input type="text" id="category" name="category"
                       maxlength="100"
                       placeholder="Technical, Cultural, Sports..."
                       value="<?= e($editEvent["category"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="status">Event Status</label>
                <select id="status" name="status">
                    <option value="Open"
                        <?= ($editEvent["status"] ?? "Open") === "Open"
                            ? "selected" : "" ?>>Open</option>
                    <option value="Closed"
                        <?= ($editEvent["status"] ?? "") === "Closed"
                            ? "selected" : "" ?>>Closed</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="description">Event Description</label>
                <textarea id="description" name="description"
                          maxlength="5000" rows="4"
                          placeholder="Describe the event, venue and activities..."><?= e($editEvent["description"] ?? "") ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="poster">Event Poster</label>
                <input type="file" id="poster" name="poster"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <small>Allowed formats: JPG, PNG and WebP. Maximum size: 2 MB.</small>

                <?php if (!empty($editEvent["poster_path"])): ?>
                    <div class="current-poster">
                        <img
                            src="<?= e($uploadUrl . rawurlencode(basename($editEvent["poster_path"]))) ?>"
                            alt="Current poster">
                        <span>Current poster. Upload a new image to replace it.</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit">
                    <?= $editEvent ? "Update Event" : "Add Event" ?>
                </button>

                <?php if ($editEvent): ?>
                    <a class="button-link" href="events.php">Cancel</a>
                <?php else: ?>
                    <button type="reset">Reset</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel" id="event-list">
        <div class="panel-heading">
            <div>
                <h2>All Events</h2>
                <p>View, search, edit and delete existing events.</p>
            </div>

            <span class="mode-badge"><?= count($events) ?> RECORDS</span>
        </div>

        <form class="filters" method="GET" action="events.php">
            <input type="search" name="search"
                   placeholder="Search event or category..."
                   aria-label="Search event or category"
                   value="<?= e($search) ?>">

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="Open"
                    <?= $statusFilter === "Open" ? "selected" : "" ?>>
                    Open events
                </option>
                <option value="Closed"
                    <?= $statusFilter === "Closed" ? "selected" : "" ?>>
                    Closed events
                </option>
            </select>

            <button type="submit">Search / Filter</button>
            <a class="button-link" href="events.php#event-list">Clear</a>
        </form>

        <p class="results-label">
            Showing <?= count($events) ?> matching events
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Event Details</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                No events found. Try another search or add an event.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= (int)$event["event_id"] ?></td>

                            <td>
                                <?php if (!empty($event["poster_path"])): ?>
                                    <img class="poster"
                                         src="<?= e($uploadUrl . rawurlencode(basename($event["poster_path"]))) ?>"
                                         alt="Event poster">
                                <?php else: ?>
                                    <span class="no-poster">No poster</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="event-name">
                                    <?= e($event["event_name"]) ?>
                                </span>
                            </td>

                            <td><?= e($event["event_date"]) ?></td>

                            <td>
                                <?= e($event["category"] ?? "") !== ""
                                    ? e($event["category"])
                                    : "—" ?>
                            </td>

                            <td>
                                <?php $closed = ($event["status"] ?? "Open") === "Closed"; ?>
                                <span class="status-badge <?= $closed ? "status-closed" : "status-open" ?>">
                                    <?= e($event["status"] ?? "Open") ?>
                                </span>
                            </td>

                            <td>
                                <?= e($event["description"] ?? "") !== ""
                                    ? e($event["description"])
                                    : "—" ?>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button-link"
                                       href="events.php?edit=<?= (int)$event["event_id"] ?>#event-form">
                                        Edit
                                    </a>

                                    <form method="POST" action="events.php"
                                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e($_SESSION["event_csrf"]) ?>">
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="event_id"
                                               value="<?= (int)$event["event_id"] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer>
    <p>&copy; <?= date("Y") ?> Student HUB Portal</p>
</footer>

</body>
</html>
