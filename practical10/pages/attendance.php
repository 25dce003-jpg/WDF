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

    <title>Attendance</title>

    <link
        rel="stylesheet"
        href="../css/attendance.css">

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
                Logout
            </a>

        </div>


    </header>



    <!-- Attendance -->

    <main>

        <div class="box">


            <table>

                <tr>

                    <th>
                        Subject
                    </th>

                    <th>
                        Attendance
                    </th>

                </tr>


                <tr>

                    <td>
                        Network
                    </td>

                    <td>
                        85%
                    </td>

                </tr>


                <tr>

                    <td>
                        Java
                    </td>

                    <td>
                        90%
                    </td>

                </tr>


                <tr>

                    <td>
                        WDF
                    </td>

                    <td>
                        80%
                    </td>

                </tr>

            </table>


        </div>

    </main>


</body>

</html>