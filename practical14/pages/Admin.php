
<?php
require_once __DIR__ . "/session_check.php";

// ==========================================
// 1. ADMIN-ONLY ACCESS CONTROL
// ==========================================
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access. Admins only.");
}

// ==========================================
// 2. DATABASE CONNECTION
// ==========================================
require_once __DIR__ . "/../db.php";

$adminName = $_SESSION["full_name"] ?? "Administrator";

$totalStudents = 0;
$totalAdmins = 0;
$totalEvents = 0;
$totalRegistrations = 0;
$latestRegistrations = [];
$dbMessage = "";

// ==========================================
// 3. DASHBOARD STATISTICS
// ==========================================
try {
    // Total student records and admin accounts
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

    // Total events
    $totalEvents = (int) $conn
        ->query("SELECT COUNT(*) FROM events")
        ->fetchColumn();

    // Total registrations
    $totalRegistrations = (int) $conn
        ->query("SELECT COUNT(*) FROM registrations")
        ->fetchColumn();

    // Latest five registrations
    $stmt = $conn->query(
        "SELECT
            r.registration_id,
            r.student_id,
            r.event_id,
            r.registered_at,
            s.full_name,
            e.event_name
         FROM registrations r
         LEFT JOIN students s
            ON s.student_id = r.student_id
         LEFT JOIN events e
            ON e.event_id = r.event_id
         ORDER BY r.registered_at DESC
         LIMIT 5"
    );

    $latestRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log("Admin dashboard error: " . $e->getMessage());

    $dbMessage =
        "Unable to load dashboard data. Please check the database.";
}

// ==========================================
// 4. AUDIT LOG: DASHBOARD ACCESS
// ==========================================
try {
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
            (student_id, action, details, ip_address)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $_SESSION["student_id"] ?? null,
        "VIEW_DASHBOARD",
        "Admin opened the dashboard",
        $_SERVER["REMOTE_ADDR"] ?? null
    ]);

} catch (Throwable $e) {
    // Dashboard should continue working if audit logging fails.
    error_log("Audit log error: " . $e->getMessage());
}

// ==========================================
// 5. ROLE-BASED NAVIGATION
// ==========================================
$role = $_SESSION["role"] ?? "";

$menuItems = [];

if ($role === "admin") {
    $menuItems = [
        ["label" => "Dashboard", "url" => "Admin.php"],
        [
            "label" => "Manage Students",
            "url" => "adminpages/manage_students.php"
        ],
        [
            "label" => "Manage Events",
            "url" => "adminpages/events.php"
        ],
        [
            "label" => "Logout",
            "url" => "logout.php"
        ]
    ];
} elseif ($role === "student") {
    $menuItems = [
        ["label" => "Home", "url" => "student.php"],
        ["label" => "Logout", "url" => "logout.php"]
    ];
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

        .admin-table-wrapper {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .admin-table th {
            background: #f3f4f6;
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
            <?php foreach ($menuItems as $item): ?>
                <a
                    href="<?= htmlspecialchars(
                        $item["url"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    <?= $item["label"] === "Dashboard"
                        ? 'aria-current="page"'
                        : "" ?>>
                    <?= htmlspecialchars(
                        $item["label"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </a>
            <?php endforeach; ?>
        </nav>

    </header>

    <!-- MAIN DASHBOARD -->
    <main class="admin-dashboard">

        <section class="admin-welcome">
            <h1>
                Welcome,
                <?= htmlspecialchars(
                    $adminName,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
                👋
            </h1>

            <p>
                Welcome to the Student HUB Administration Dashboard.
                Manage student records, events, and registrations.
            </p>
        </section>

        <?php if ($dbMessage !== ""): ?>
            <p class="admin-message" role="status">
                <?= htmlspecialchars(
                    $dbMessage,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
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
                    <p>Records in the students table.</p>
                </article>

                <article class="admin-card">
                    <h2>Administrator Accounts</h2>
                    <p class="admin-number">
                        <?= number_format($totalAdmins) ?>
                    </p>
                    <p>Accounts with the admin role.</p>
                </article>

                <article class="admin-card">
                    <h2>Total Events</h2>
                    <p class="admin-number">
                        <?= number_format($totalEvents) ?>
                    </p>
                    <p>Events in the database.</p>
                </article>

                <article class="admin-card">
                    <h2>Total Registrations</h2>
                    <p class="admin-number">
                        <?= number_format($totalRegistrations) ?>
                    </p>
                    <p>Student event registrations.</p>
                </article>

            </div>
        </section>

        <!-- LATEST REGISTRATIONS -->
        <section aria-labelledby="latestRegistrationsTitle">
            <h2 id="latestRegistrationsTitle">Latest Registrations</h2>

            <div class="admin-card admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Event</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($latestRegistrations)): ?>
                            <tr>
                                <td colspan="4">
                                    No registrations found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (
                                $latestRegistrations as $registration
                            ): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $registration["student_id"] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $registration["full_name"]
                                                ?? "Unknown",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $registration["event_name"]
                                                ?? "Event unavailable",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $registration["registered_at"]
                                                ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                    <p>Open the event management section.</p>
                    <strong>Open Events →</strong>
                </a>

                <a
                    class="admin-action"
                    href="logout.php">
                    <h2>🔒 Secure Logout</h2>
                    <p>End your current administrator session.</p>
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
