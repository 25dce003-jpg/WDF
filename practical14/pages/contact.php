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

    <title>Contact Us</title>

    <link
        rel="stylesheet"
        href="../css/contact.css">

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



    <!-- Contact Section -->

    <section class="contact">

        <div class="contact-container">


            <h1>
                Contact Us
            </h1>



            <form>


                <div class="input-group">

                    <input
                        type="text"
                        placeholder="Your Name"
                        required>

                </div>



                <div class="input-group">

                    <input
                        type="email"
                        placeholder="Your Email"
                        required>

                </div>



                <div class="input-group">

                    <input
                        type="text"
                        placeholder="Subject"
                        required>

                </div>



                <div class="input-group">

                    <textarea
                        rows="6"
                        placeholder="Your Message"
                        required></textarea>

                </div>



                <button type="submit">
                    Send Message
                </button>


            </form>


        </div>

    </section>


</body>

</html>