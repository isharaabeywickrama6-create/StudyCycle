<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

$adminName = $_SESSION["name"] ?? "Administrator";

function getCount($conn, $table, $condition = "") {
    $sql = "SELECT COUNT(*) AS total FROM " . $table;

    if ($condition !== "") {
        $sql .= " WHERE " . $condition;
    }

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return $row["total"];
}

$totalUsers = getCount($conn, "users");
$totalMaterials = getCount($conn, "materials");
$pendingMaterials = getCount(
    $conn,
    "materials",
    "status = 'pending'"
);
$totalDiscussions = getCount($conn, "discussions");
$totalGroups = getCount($conn, "study_groups");

$sql = "SELECT
            m.material_id,
            m.title,
            m.created_at,
            u.name AS student_name,
            s.subject_name
        FROM materials m
        LEFT JOIN users u
            ON m.user_id = u.user_id
        LEFT JOIN subjects s
            ON m.subject_id = s.subject_id
        WHERE m.status = 'pending'
        ORDER BY m.created_at DESC
        LIMIT 10";

$pendingResult = $conn->query($sql);
?>

<html>
<head>
    <title>StudyCircle Admin Dashboard</title>
    <style>
        :root {
            --nsbm-green: #16834A;
            --nsbm-green-dark: #106638;
            --nsbm-green-light: #E8F5EC;
            --nsbm-blue: #174A8B;
            --nsbm-blue-dark: #123665;
            --nsbm-white: #FFFFFF;
            --nsbm-grey: #E9EDF0;
            --nsbm-text: #263445;
            --nsbm-muted: #657386;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #F3F6F5;
            color: var(--nsbm-text);
        }

        header {
            background: linear-gradient(
                120deg,
                var(--nsbm-green-dark),
                var(--nsbm-green),
                var(--nsbm-green-dark)
            );
            color: var(--nsbm-white);
            padding: 28px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 4px 14px #174A8B20;
        }

        header h1 {
            margin: 0 0 7px;
            font-size: 30px;
        }

        header p {
            margin: 0;
            color: #E2F1E7;
        }

        main {
            max-width: 1200px;
            margin: auto;
            padding: 25px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--nsbm-white);
            padding: 23px;
            border-radius: 14px;
            border-left: 5px solid var(--nsbm-green);
            box-shadow: 0 4px 14px #2634450D;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 9px 22px #16834A20;
        }

        .card p {
            margin: 0 0 12px;
            color: var(--nsbm-muted);
            font-weight: 600;
        }

        .card h2 {
            margin: 0;
            font-size: 32px;
            color: var(--nsbm-blue);
        }

        .section {
            background: var(--nsbm-white);
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 4px 14px #2634450D;
            overflow-x: auto;
        }

        .section h2, .welcome h2 {
            color: var(--nsbm-blue-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid var(--nsbm-grey);
        }

        th {
            background: var(--nsbm-green-light);
            color: var(--nsbm-blue-dark);
        }

        tbody tr:hover {
            background: #F4FAF6;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
            margin-top: 25px;
        }

        .admin-card {
            display: block;
            background: var(--nsbm-white);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--nsbm-grey);
            border-top: 4px solid var(--nsbm-green);
            text-decoration: none;
            color: var(--nsbm-text);
            box-shadow: 0 5px 18px #2634450D;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 24px #16834A20;
            border-top-color: var(--nsbm-blue);
        }

        .admin-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--nsbm-green-light);
            border-radius: 13px;
            font-size: 27px;
            margin-bottom: 18px;
        }

        .admin-card h3 {
            margin: 0 0 10px;
            color: var(--nsbm-blue-dark);
            font-size: 19px;
        }

        .admin-card p {
            color: var(--nsbm-muted);
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 18px;
        }

        .admin-open {
            color: var(--nsbm-green-dark);
            font-size: 14px;
            font-weight: bold;
        }

        .admin-open:hover {
            color: var(--nsbm-blue);
        }

        .logout-button {
            display: inline-block;
            padding: 11px 18px;
            background: var(--nsbm-white);
            color: var(--nsbm-green-dark);
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-button:hover {
            background: var(--nsbm-green-light);
        }

        .empty {
            padding: 18px 0;
            color: var(--nsbm-muted);
        }

        @media (max-width: 600px) {
            main {
                padding: 15px;
            }

            header {
                padding: 20px 15px;
            }
        }

    </style>
</head>

<body>


<header>
    <div>
        <h1>StudyCircle</h1>
        <p>Administrator Dashboard</p>
    </div>

    <div class="header-right">
        <span class="admin-name">
            Welcome, <?= htmlspecialchars($adminName, ENT_QUOTES, "UTF-8") ?>
        </span>

        <a href="logout.php" class="logout-button">
            Logout ↗
        </a>
    </div>
</header>


<main>
    
<style>
    .admin-menu {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 22px;
        margin-top: 25px;
    }

    .admin-card {
        display: block;
        background: #ffffff;
        padding: 25px;
        border-radius: 16px;
        border: 1px solid #e8e8f2;
        border-top: 4px solid #16834A;
        text-decoration: none;
        color: #0c4d2b;
        box-shadow: 0 5px 18px rgba(32, 35, 75, 0.06);
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .admin-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 25px #035028;
        border-top-color: #0c4d2b;
    }

    .admin-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0edff;
        border-radius: 13px;
        font-size: 27px;
        margin-bottom: 18px;
    }

    .admin-card h3 {
        margin: 0 0 10px;
        color: #0c4d2b;
        font-size: 19px;
    }

    .admin-card p {
        color: gray;
        font-size: 14px;
        line-height: 1.6;
        margin: 0 0 18px;
    }

    .admin-open {
        color: #16834A;
        font-size: 14px;
        font-weight: bold;
    }
</style>

<h2>Administration</h2>
<p>Manage and monitor your StudyCircle platform.</p>

<div class="admin-menu">

    <a href="users.php" class="admin-card">
        <div class="admin-icon">👥</div>
        <h3>Manage Users</h3>
        <p>View registered students and manage user information.</p>
        <span class="admin-open">Open Management →</span>
    </a>

    <a href="materials.php" class="admin-card">
        <div class="admin-icon">📚</div>
        <h3>Study Materials</h3>
        <p>Review submitted resources and approve or reject materials.</p>
        <span class="admin-open">Review Materials →</span>
    </a>

    <a href="subjects.php" class="admin-card">
        <div class="admin-icon">📖</div>
        <h3>Manage Subjects</h3>
        <p>Add, update, and organize academic subjects.</p>
        <span class="admin-open">Manage Subjects →</span>
    </a>

    <a href="discussions.php" class="admin-card">
        <div class="admin-icon">💬</div>
        <h3>Forum Moderation</h3>
        <p>Review student discussions and moderate inappropriate posts.</p>
        <span class="admin-open">View Discussions →</span>
    </a>

    <a href="groups.php" class="admin-card">
        <div class="admin-icon">🎓</div>
        <h3>Study Groups</h3>
        <p>View study groups, creators, subjects, and member counts.</p>
        <span class="admin-open">Manage Groups →</span>
    </a>

    <a href="reports.php" class="admin-card">
        <div class="admin-icon">📊</div>
        <h3>Admin Reports</h3>
        <p>Monitor platform statistics, registrations, and downloads.</p>
        <span class="admin-open">View Reports →</span>
    </a>

</div>
    
    <div class="welcome">
        <h2>Dashboard Overview</h2>
        <p>Monitor students and academic activities.</p>
    </div>

    
<div class="cards">
    <div class="card">
        <p>👥 Total Users</p>
        <h2><?= (int) $totalUsers ?></h2>
    </div>

    <div class="card">
        <p>📚 Total Materials</p>
        <h2><?= (int) $totalMaterials ?></h2>
    </div>

    <div class="card">
        <p>⏳ Pending Approvals</p>
        <h2><?= (int) $pendingMaterials ?></h2>
    </div>

    <div class="card">
        <p>💬 Discussions</p>
        <h2><?= (int) $totalDiscussions ?></h2>
    </div>

    <div class="card">
        <p>🎓 Study Groups</p>
        <h2><?= (int) $totalGroups ?></h2>
    </div>
</div>


    <div class="section">
        <h2>Materials Waiting for Approval</h2>

        <?php if ($pendingResult->num_rows > 0) { ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Material Title</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Date Uploaded</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($material =
                        $pendingResult->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <?php echo (int) $material["material_id"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $material["title"] ?? ""
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $material["student_name"] ?? "Unknown"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $material["subject_name"] ?? "Unassigned"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $material["created_at"] ?? ""
                                ); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>

            <p class="empty">
                There are no materials waiting for approval.
            </p>

        <?php } ?>
    </div>
</main>

</body>
</html>

<?php
$conn->close();
?>