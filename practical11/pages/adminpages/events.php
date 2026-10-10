
<?php
require_once "../session_check.php";

// Only admin can manage events
if ($_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

require_once "../../db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_name = trim($_POST["event_name"] ?? "");
    $event_date = trim($_POST["event_date"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($event_name === "") {
        $message = "Please enter event name.";
        $messageType = "error";
    } elseif ($event_date === "") {
        $message = "Please select event date.";
        $messageType = "error";
    } else {
        // Validate the submitted date
        $date = DateTime::createFromFormat("Y-m-d", $event_date);

        if (!$date || $date->format("Y-m-d") !== $event_date) {
            $message = "Please enter a valid event date.";
            $messageType = "error";
        } else {
            try {
                $sql = "INSERT INTO events
                        (event_name, event_date, description)
                        VALUES
                        (:event_name, :event_date, :description)";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":event_name" => $event_name,
                    ":event_date" => $event_date,
                    ":description" => $description
                ]);

                $message = "Event added successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $message = "Event could not be added. Please try again.";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Event</title>
    <link rel="stylesheet" href="../../css/register.css">
</head>

<body>
<header class="header">
    <div class="logo">
        <img src="../../images/charusat.png" alt="CHARUSAT Logo">
    </div>

    <nav>
        <ul>
            <li><a href="../Admin.php">Admin Dashboard</a></li>
            <li><a href="events.php">Add Event</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <section>
        <h2>Add Event</h2>

        <?php if ($message !== ""): ?>
            <p role="status"
               style="color: <?= $messageType === 'success' ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="events.php">
            <p>
                <label for="event_name">Event Name</label><br>
                <input
                    type="text"
                    id="event_name"
                    name="event_name"
                    maxlength="150"
                    placeholder="Enter event name"
                    required>
            </p>

            <p>
                <label for="event_date">Event Date</label><br>
                <input
                    type="date"
                    id="event_date"
                    name="event_date"
                    required>
            </p>

            <p>
                <label for="description">Description</label><br>
                <textarea
                    id="description"
                    name="description"
                    maxlength="5000"
                    placeholder="Enter event description"
                    rows="4"></textarea>
            </p>

            <button type="submit">Add Event</button>
            <button type="reset">Reset</button>
        </form>
    </section>
</main>

<footer>
    <p>&copy; 2026 Student HUB Portal</p>
</footer>
</body>
</html>
