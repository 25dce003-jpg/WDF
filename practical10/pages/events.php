<?php

require_once "session_check.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}

require_once "../db.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $event_name = trim($_POST["event_name"] ?? "");
    $event_date = trim($_POST["event_date"] ?? "");
    $description = trim($_POST["description"] ?? "");


    // Check fields

    if ($event_name == "") {

        $message = "Please enter event name.";

    }
    elseif ($event_date == "") {

        $message = "Please select event date.";

    }
    else {

        try {

            // Insert event into database

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

        }
        catch (PDOException $e) {

            $message = "Error: Event could not be added.";

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

    <title>Add Event</title>

    <link
        rel="stylesheet"
        href="../css/register.css">

</head>


<body>


<!-- =========================================
     HEADER
========================================= -->

<header class="header">


    <!-- Logo -->

    <div class="logo">

        <img
            src="../images/charusat.png"
            alt="CHARUSAT Logo">

    </div>



    <!-- Navigation -->

    <nav>

        <ul>


            <li>
                <a href="profile.php">
                    Profile
                </a>
            </li>


            <li>
                <a href="attendance.php">
                    Attendance
                </a>
            </li>


            <li>
                <a href="assignment.php">
                    Assignment
                </a>
            </li>


            <li>
                <a href="events.php">
                    Events
                </a>
            </li>


            <li>
                <a href="event_register.php">
                    Event Registration
                </a>
            </li>


            <li>
                <a href="index.php">
                    Dashboard
                </a>
            </li>


            <li>
                <a href="courses.php">
                    Courses
                </a>
            </li>


            <li>
                <a href="contact.php">
                    Contact
                </a>
            </li>


        </ul>

    </nav>



    <!-- Login -->

    <div class="logout-text">

        <a href="login.php">
            logout
        </a>

    </div>


</header>



<!-- =========================================
     MAIN CONTENT
========================================= -->

<main>

    <section>


        <h2>
            Add Event
        </h2>



        <?php

        if ($message != "") {

            echo "<p>" . htmlspecialchars($message) . "</p>";

        }

        ?>



        <form
            method="POST"
            action="events.php">


            <!-- Event Name -->

            <p>

                <label for="event_name">
                    Event Name
                </label>

                <br>

                <input
                    type="text"
                    id="event_name"
                    name="event_name"
                    placeholder="Enter event name"
                    required>

            </p>



            <!-- Event Date -->

            <p>

                <label for="event_date">
                    Event Date
                </label>

                <br>

                <input
                    type="date"
                    id="event_date"
                    name="event_date"
                    required>

            </p>



            <!-- Description -->

            <p>

                <label for="description">
                    Description
                </label>

                <br>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Enter event description"
                    rows="4"></textarea>

            </p>



            <!-- Buttons -->

            <button type="submit">
                Add Event
            </button>


            <button type="reset">
                Reset
            </button>


        </form>


    </section>

</main>



<!-- =========================================
     FOOTER
========================================= -->

<footer>

    <p>
        @2026 Student HUB Portal
    </p>

</footer>


</body>

</html>