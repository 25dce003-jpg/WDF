<?php
require_once "session_check.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Profile</title>

    <link
        rel="stylesheet"
        href="../css/profile.css">

</head>


<body>


    <!-- Header -->

    <header class="header">


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
                Logout
            </a>

        </div>


    </header>



    <!-- Profile Section -->

    <div class="profile-card">


        <img
            src="profile.png"
            alt="Student Photo"
            class="profile-img">


        <div class="profile-info">


            <h2>
                Student Profile
            </h2>


            <p>
                <strong>Name:</strong>
                Aniket Amrutiya
            </p>


            <p>
                <strong>Roll No:</strong>
                25DCE003
            </p>


            <p>
                <strong>Department:</strong>
                computer engineering
            </p>


            <p>
                <strong>Year:</strong>
                II Year
            </p>


            <p>
                <strong>Email:</strong>
                amrutiyaaniket@gmail.com
            </p>


            <p>
                <strong>Phone:</strong>
                9876543210
            </p>


        </div>

    </div>


</body>

</html>