
<?php

require_once "../session_check.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);

    exit;
}

require_once "../../db.php";
require_once __DIR__ . "/../audit_helper.php";


/* ---------------------------------
   CSRF TOKEN
--------------------------------- */

if (empty($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}


/* ---------------------------------
   UPLOAD SETTINGS
--------------------------------- */

$uploadDir = dirname(__DIR__, 2) . "/uploads/event_posters/";
$uploadUrl = "../../uploads/event_posters/";
$maxSize = 2 * 1024 * 1024;


/* ---------------------------------
   JSON RESPONSE
--------------------------------- */

function respond(int $status, array $body): void
{
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}


/* ---------------------------------
   READ REQUEST DATA
--------------------------------- */

function inputData(): array
{
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";

    if (stripos($contentType, "application/json") !== false) {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            respond(400, [
                "success" => false,
                "message" => "Invalid JSON request."
            ]);
        }

        return $data;
    }

    return $_POST;
}


/* ---------------------------------
   CSRF VALIDATION
--------------------------------- */

function checkCsrf(): void
{
    $token = $_SERVER["HTTP_X_CSRF_TOKEN"]
        ?? $_POST["csrf_token"]
        ?? "";

    if (
        !is_string($token) ||
        empty($_SESSION["event_csrf"]) ||
        !hash_equals($_SESSION["event_csrf"], $token)
    ) {
        respond(403, [
            "success" => false,
            "message" => "Invalid security token. Refresh the page."
        ]);
    }
}


/* ---------------------------------
   VALIDATE EVENT ID
--------------------------------- */

function getEventId(array $data): int
{
    $id = filter_var(
        $data["event_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($id === false || $id === null || $id < 1) {
        respond(422, [
            "success" => false,
            "message" => "Invalid event ID."
        ]);
    }

    return (int) $id;
}


/* ---------------------------------
   VALIDATE EVENT FIELDS
--------------------------------- */

function validateEvent(array $data): array
{
    $name = trim((string)($data["event_name"] ?? ""));
    $date = trim((string)($data["event_date"] ?? ""));
    $description = trim((string)($data["description"] ?? ""));
    $category = trim((string)($data["category"] ?? ""));
    $status = trim((string)($data["status"] ?? "Open"));

    if ($name === "" || strlen($name) > 150) {
        respond(422, [
            "success" => false,
            "message" => "Event name is required and must be under 150 characters."
        ]);
    }

    $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

    if (
        !$parsedDate ||
        $parsedDate->format("Y-m-d") !== $date
    ) {
        respond(422, [
            "success" => false,
            "message" => "Please select a valid event date."
        ]);
    }

    if (strlen($description) > 5000) {
        respond(422, [
            "success" => false,
            "message" => "Description must be under 5000 characters."
        ]);
    }

    if (strlen($category) > 100) {
        respond(422, [
            "success" => false,
            "message" => "Category must be under 100 characters."
        ]);
    }

    if (!in_array($status, ["Open", "Closed"], true)) {
        respond(422, [
            "success" => false,
            "message" => "Invalid event status."
        ]);
    }

    return [
        "event_name" => $name,
        "event_date" => $date,
        "description" => $description,
        "category" => $category,
        "status" => $status
    ];
}


/* ---------------------------------
   SAVE POSTER
--------------------------------- */

function savePoster(
    array $file,
    string $dir,
    int $maxSize
): ?string {
    $error = $file["error"] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        respond(400, [
            "success" => false,
            "message" => "Poster upload failed."
        ]);
    }

    if (
        empty($file["tmp_name"]) ||
        !is_uploaded_file($file["tmp_name"]) ||
        !isset($file["size"]) ||
        $file["size"] < 1 ||
        $file["size"] > $maxSize
    ) {
        respond(400, [
            "success" => false,
            "message" => "Poster must be smaller than 2 MB."
        ]);
    }

    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        respond(500, [
            "success" => false,
            "message" => "Cannot create poster folder."
        ]);
    }

    if (!is_writable($dir)) {
        respond(500, [
            "success" => false,
            "message" => "Poster folder is not writable."
        ]);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    $imageInfo = @getimagesize($file["tmp_name"]);

    if (
        !isset($allowed[$mime]) ||
        !$imageInfo ||
        ($imageInfo["mime"] ?? "") !== $mime
    ) {
        respond(400, [
            "success" => false,
            "message" => "Only valid JPG, PNG and WebP images are allowed."
        ]);
    }

    $filename = bin2hex(random_bytes(16))
        . "."
        . $allowed[$mime];

    if (!move_uploaded_file($file["tmp_name"], $dir . $filename)) {
        respond(500, [
            "success" => false,
            "message" => "Could not save the poster."
        ]);
    }

    return $filename;
}


/* ---------------------------------
   REMOVE POSTER
--------------------------------- */

function removePoster(?string $filename, string $dir): void
{
    if (
        !$filename ||
        basename($filename) !== $filename
    ) {
        return;
    }

    $path = $dir . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}


/* ---------------------------------
   MAIN API
--------------------------------- */

try {
    $method = $_SERVER["REQUEST_METHOD"] ?? "GET";


    /* GET: LIST, SEARCH AND FILTER */

    if ($method === "GET") {
        $search = trim((string)($_GET["search"] ?? ""));
        $status = (string)($_GET["status"] ?? "");

        $sql = "
            SELECT
                event_id,
                event_name,
                event_date,
                description,
                poster_path,
                status,
                category
            FROM events
            WHERE 1 = 1
        ";

        $params = [];

        if ($search !== "") {
            $sql .= "
                AND (
                    event_name LIKE :search_name
                    OR category LIKE :search_category
                )
            ";

            $params[":search_name"] = "%" . $search . "%";
            $params[":search_category"] = "%" . $search . "%";
        }

        if (in_array($status, ["Open", "Closed"], true)) {
            $sql .= " AND status = :status";
            $params[":status"] = $status;
        }

        $sql .= " ORDER BY event_date DESC, event_id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($events as &$event) {
            $filename = $event["poster_path"] ?? "";

            $event["poster_url"] =
                $filename !== "" &&
                basename($filename) === $filename
                    ? $uploadUrl . rawurlencode($filename)
                    : null;
        }

        unset($event);


        /* STATISTICS */

        $total = (int)$conn
            ->query("SELECT COUNT(*) FROM events")
            ->fetchColumn();

        $open = (int)$conn
            ->query("SELECT COUNT(*) FROM events WHERE status = 'Open'")
            ->fetchColumn();

        $closed = (int)$conn
            ->query("SELECT COUNT(*) FROM events WHERE status = 'Closed'")
            ->fetchColumn();

        respond(200, [
            "success" => true,
            "message" => "Events loaded successfully.",
            "events" => $events,
            "stats" => [
                "total" => $total,
                "open" => $open,
                "closed" => $closed
            ]
        ]);
    }


    /* ALLOW ONLY SUPPORTED METHODS */

    if (!in_array(
        $method,
        ["POST", "PUT", "PATCH", "DELETE"],
        true
    )) {
        header("Allow: GET, POST, PUT, PATCH, DELETE");

        respond(405, [
            "success" => false,
            "message" => "HTTP method not allowed."
        ]);
    }


    $data = inputData();
    checkCsrf();

    $action = (string)($data["action"] ?? "");

    if ($method === "DELETE") {
        $action = "delete";
    }

    if ($method === "PUT" || $method === "PATCH") {
        $action = "update";
    }


    /* ---------------------------------
       DELETE EVENT + AUDIT LOG
    --------------------------------- */

    if ($action === "delete") {
        $id = getEventId($data);

        $stmt = $conn->prepare(
            "SELECT poster_path FROM events WHERE event_id = :id"
        );

        $stmt->execute([":id" => $id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            respond(404, [
                "success" => false,
                "message" => "Event not found."
            ]);
        }

        $stmt = $conn->prepare(
            "DELETE FROM events WHERE event_id = :id"
        );

        $stmt->execute([":id" => $id]);

        // Audit log after successful deletion.
        logAdminAction(
            $conn,
            "DELETE_EVENT",
            "Deleted event ID: " . $id
        );

        removePoster(
            $existing["poster_path"] ?? null,
            $uploadDir
        );

        respond(200, [
            "success" => true,
            "message" => "Event deleted successfully."
        ]);
    }


    /* VALIDATE ACTION */

    if (!in_array($action, ["add", "update"], true)) {
        respond(400, [
            "success" => false,
            "message" => "Action must be add, update or delete."
        ]);
    }


    $isUpdate = ($action === "update");

    $id = $isUpdate ? getEventId($data) : null;

    $event = validateEvent($data);

    $oldPoster = null;


    /* CHECK EXISTING EVENT */

    if ($isUpdate) {
        $stmt = $conn->prepare(
            "SELECT poster_path FROM events WHERE event_id = :id"
        );

        $stmt->execute([":id" => $id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            respond(404, [
                "success" => false,
                "message" => "Event not found."
            ]);
        }

        $oldPoster = $existing["poster_path"] ?? null;
    }


    /* POSTER UPLOAD */

    $newPoster = savePoster(
        $_FILES["poster"] ?? [
            "error" => UPLOAD_ERR_NO_FILE
        ],
        $uploadDir,
        $maxSize
    );

    $poster = $newPoster ?? $oldPoster;


    /* ---------------------------------
       UPDATE EVENT + AUDIT LOG
    --------------------------------- */

    if ($isUpdate) {
        $stmt = $conn->prepare("
            UPDATE events
            SET
                event_name = :name,
                event_date = :date,
                description = :description,
                category = :category,
                status = :status,
                poster_path = :poster
            WHERE event_id = :id
        ");

        $stmt->execute([
            ":name" => $event["event_name"],
            ":date" => $event["event_date"],
            ":description" => $event["description"],
            ":category" => $event["category"],
            ":status" => $event["status"],
            ":poster" => $poster,
            ":id" => $id
        ]);

        // Audit log after successful update.
        logAdminAction(
            $conn,
            "UPDATE_EVENT",
            "Updated event ID: " . $id .
            ", name: " . $event["event_name"]
        );

        if ($newPoster !== null) {
            removePoster($oldPoster, $uploadDir);
        }

        respond(200, [
            "success" => true,
            "message" => "Event updated successfully.",
            "event_id" => $id
        ]);
    }


    /* ---------------------------------
       ADD EVENT + AUDIT LOG
    --------------------------------- */

    $stmt = $conn->prepare("
        INSERT INTO events (
            event_name,
            event_date,
            description,
            poster_path,
            status,
            category
        )
        VALUES (
            :name,
            :date,
            :description,
            :poster,
            :status,
            :category
        )
    ");

    $stmt->execute([
        ":name" => $event["event_name"],
        ":date" => $event["event_date"],
        ":description" => $event["description"],
        ":poster" => $newPoster,
        ":status" => $event["status"],
        ":category" => $event["category"]
    ]);

    $newEventId = (int)$conn->lastInsertId();

    // Audit log after successful insertion.
    logAdminAction(
        $conn,
        "ADD_EVENT",
        "Added event ID: " . $newEventId .
        ", name: " . $event["event_name"]
    );

    respond(201, [
        "success" => true,
        "message" => "Event added successfully.",
        "event_id" => $newEventId
    ]);

} catch (PDOException $ex) {

    error_log(
        "Events API database error: " . $ex->getMessage()
    );

    respond(500, [
        "success" => false,
        "message" => "Database operation failed."
    ]);

} catch (Throwable $ex) {

    error_log(
        "Events API error: " . $ex->getMessage()
    );

    respond(500, [
        "success" => false,
        "message" => "An unexpected server error occurred."
    ]);
}
