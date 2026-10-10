
<?php
require_once __DIR__ . "/session_check.php";

// Only admins can access this dashboard.
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

// Database connection: practical11/db.php
require_once __DIR__ . "/../db.php";

$adminName = $_SESSION["full_name"] ?? "Administrator";
$totalStudents = 0;
$totalAdmins = 0;
$dbMessage = "";

try {
    $stmt = $conn->query(
        "SELECT
            COUNT(*) AS total_students,
            COALESCE(
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END),
                0
            ) AS total_admins
         FROM students"
    );

    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalStudents = (int) ($stats["total_students"] ?? 0);
    $totalAdmins = (int) ($stats["total_admins"] ?? 0);

} catch (Throwable $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $dbMessage = "Unable to load statistics. Please check the database connection.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Student HUB</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        .admin-dashboard {
            max-width: 1150px;
            margin: 30px auto;
            padding: 20px;
        }

        .admin-welcome {
            margin-bottom: 25px;
        }

        .admin-welcome h1 {
            margin-bottom: 8px;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .admin-card {
            padding: 24px;
            border-radius: 12px;
            background: var(--card-bg, #fff);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
        }

        .admin-card h2 {
            font-size: 18px;
            margin-top: 0;
        }

        .admin-number {
            font-size: 32px;
            font-weight: bold;
            margin: 12px 0;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .admin-action {
            display: block;
            padding: 24px;
            border-radius: 12px;
            background: var(--card-bg, #fff);
            color: inherit;
            text-decoration: none;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
            transition: transform 0.2s ease;
        }

        .admin-action:hover {
            transform: translateY(-4px);
        }

        .admin-action h2 {
            margin-top: 0;
        }

        .admin-action:focus-visible {
            outline: 3px solid #2563eb;
            outline-offset: 3px;
        }

        .admin-message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 8px;
            background: #fff3cd;
            color: #664d03;
        }

        .admin-navigation {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 18px;
        }

        .admin-navigation a {
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .admin-dashboard {
                padding: 12px;
            }

            .admin-card,
            .admin-action {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header class="header">

        <div class="logo">
            <img
                src="../images/charusat.png"
                alt="CHARUSAT Logo">
        </div>

        <button
            id="menuBtn"
            class="menu-btn"
            type="button"
            aria-label="Open Menu"
            aria-expanded="false">
            ☰
        </button>

        <nav id="navbar" class="admin-navigation">
            <a href="Admin.php" aria-current="page">Dashboard</a>

            <a href="adminpages/manage_students.php">
                Manage Students
            </a>

            <a href="adminpages/events.php">
                Manage Events
            </a>

            <a href="logout.php">Logout</a>
        </nav>

    </header>

    <!-- MAIN DASHBOARD -->
    <main class="admin-dashboard">

        <section class="admin-welcome">
            <h1>
                Welcome,
                <?= htmlspecialchars($adminName, ENT_QUOTES, "UTF-8") ?>
                👋
            </h1>

            <p>
                Welcome to the Student HUB Administration Dashboard.
                Manage student records and access administrative tools.
            </p>
        </section>

        <?php if ($dbMessage !== ""): ?>
            <p class="admin-message" role="status">
                <?= htmlspecialchars($dbMessage, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <!-- DASHBOARD STATISTICS -->
        <section aria-labelledby="overviewTitle">
            <h2 id="overviewTitle">Dashboard Overview</h2>

            <div class="admin-stats">

                <article class="admin-card">
                    <h2>Total Student Records</h2>

                    <p class="admin-number">
                        <?= number_format($totalStudents) ?>
                    </p>

                    <p>Number of records in the students table.</p>
                </article>

                <article class="admin-card">
                    <h2>Administrator Accounts</h2>

                    <p class="admin-number">
                        <?= number_format($totalAdmins) ?>
                    </p>

                    <p>Accounts with the admin role.</p>
                </article>

            </div>
        </section>

        <!-- ADMIN ACTIONS -->
        <section aria-labelledby="actionsTitle">
            <h2 id="actionsTitle">Administration Tools</h2>

            <div class="admin-actions">

                <a
                    class="admin-action"
                    href="adminpages/manage_students.php">

                    <h2>👨‍🎓 Manage Students</h2>

                    <p>
                        Add, view, edit, delete, search, and filter
                        student records.
                    </p>

                    <strong>Open Student Management →</strong>
                </a>

                <a
                    class="admin-action"
                    href="adminpages/events.php">

                    <h2>📅 Manage Events</h2>

                    <p>
                        Open the event management section.
                    </p>

                    <strong>Open Events →</strong>
                </a>

                <a
                    class="admin-action"
                    href="logout.php">

                    <h2>🔒 Secure Logout</h2>

                    <p>
                        End your current administrator session.
                    </p>

                    <strong>Logout →</strong>
                </a>

            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer>
        <p style="text-align: center;">
            &copy; 2026 Student HUB Portal
        </p>
    </footer>

    <script src="../js/index.js"></script>

</body>
</html>
