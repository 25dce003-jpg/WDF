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

    <link
        rel="stylesheet"
        href="../css/assignment.css">

    <title>Assignments</title>

</head>


<body>


    <!-- Header -->

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
                Login
            </a>

        </div>


    </header>



    <!-- Assignment Section -->

    <div class="assignment-card">

        <h2>
            Assignments
        </h2>


        <table>

            <tr>

                <th>
                    Subject
                </th>

                <th>
                    Assignment
                </th>

                <th>
                    Due Date
                </th>

                <th>
                    Status
                </th>

            </tr>


            <tr>

                <td>
                    Mathematics
                </td>

                <td>
                    Assignment 1
                </td>

                <td>
                    20-Aug-2026
                </td>

                <td>
                    Pending
                </td>

            </tr>


            <tr>

                <td>
                    Computer Engineering
                </td>

                <td>
                    Assignment 2
                </td>

                <td>
                    22-Aug-2026
                </td>

                <td>
                    Submitted
                </td>

            </tr>


            <tr>

                <td>
                    Physics
                </td>

                <td>
                    Assignment 3
                </td>

                <td>
                    25-Aug-2026
                </td>

                <td>
                    Pending
                </td>

            </tr>


            <tr>

                <td>
                    English
                </td>

                <td>
                    Assignment 1
                </td>

                <td>
                    28-Aug-2026
                </td>

                <td>
                    Submitted
                </td>

            </tr>

        </table>

    </div>


</body>

</html>