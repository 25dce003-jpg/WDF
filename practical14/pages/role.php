
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Choose Role</title>

    <link
        rel="stylesheet"
        href="../css/login.css">

    <style>
        .role-container {
            width: 90%;
            max-width: 500px;
            margin: 60px auto;
            padding: 35px;
            background: white;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .role-container h1 {
            color: #1428a0;
            margin-bottom: 10px;
        }

        .role-container p {
            color: #666;
            margin-bottom: 25px;
        }

        .role-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: none;
            border-radius: 8px;
            background: #1428a0;
            color: white;
            font-size: 17px;
            font-weight: bold;
            text-decoration: none;
            box-sizing: border-box;
        }

        .role-btn:hover {
            background: #0b176d;
        }

        .admin-btn {
            background: #555;
        }

        .admin-btn:hover {
            background: #333;
        }
    </style>

</head>

<body>

    <header class="header">

        <div class="logo">
            <img
                src="../images/charusat.png"
                alt="CHARUSAT Logo">
        </div>

        <div class="page-title">
            <h2>CHOOSE YOUR ROLE</h2>
        </div>

        <div class="logout-text">
            <a href="login.php">Login</a>
        </div>

    </header>


    <main>

        <div class="role-container">

            <h1>Join StudentHub</h1>

            <p>
                Choose how you want to access the portal.
            </p>

            <a
                href="register.php"
                class="role-btn">

                Student

            </a>

            <a
                href="admin_register.php"
                class="role-btn admin-btn">

                Admin

            </a>

            <p>
                Admin accounts must be created or approved
                by the administrator.
            </p>

        </div>

    </main>


    <footer>
        <p>© 2026 CHARUSAT University | Student Portal</p>
    </footer>

</body>

</html>