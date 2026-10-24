<?php
session_start();

require_once "config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "student"
) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$student_name = $_SESSION["name"] ?? "Student";

$material_counts = [
    "total" => 0,
    "pending" => 0,
    "approved" => 0,
    "rejected" => 0
];

$stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'pending'), 0) AS pending,
        COALESCE(SUM(status = 'approved'), 0) AS approved,
        COALESCE(SUM(status = 'rejected'), 0) AS rejected
     FROM materials
     WHERE user_id = ?"
);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $material_counts = $result->fetch_assoc();

    $stmt->close();
}

$total_downloads = 0;

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM resource_downloads
     WHERE user_id = ?"
);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $download_data = $result->fetch_assoc();

    $total_downloads = (int) $download_data["total"];

    $stmt->close();
}

$recent_uploads = [];

$stmt = $conn->prepare(
    "SELECT m.title, m.status, m.created_at,
            s.subject_name
     FROM materials m
     LEFT JOIN subjects s
        ON m.subject_id = s.subject_id
     WHERE m.user_id = ?
     ORDER BY m.created_at DESC
     LIMIT 5"
);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $recent_uploads[] = $row;
    }

    $stmt->close();
}
?>

<html>
<head>
    <title>Student Dashboard | StudyCircle</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #F3F6F5;
            color: #263445;
        }

        nav {
            background: #106638;
            color: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: #B8E4C8;
        }

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 35px auto;
        }

        .welcome {
            background: white;
            padding: 28px;
            border-radius: 12px;
            border-left: 5px solid #16834A;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px #00000008;
        }

        .welcome h1 {
            color: #123665;
            margin-bottom: 10px;
            font-size: 27px;
        }

        .welcome p {
            color: #657386;
            line-height: 1.6;
        }

        .section-title {
            color: #123665;
            margin: 28px 0 16px;
            font-size: 21px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 16px;
        }

        .stat-card {
            background: white;
            padding: 22px 18px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000008;
            border-bottom: 3px solid #16834A;
        }

        .stat-card p {
            color: #657386;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .stat-card h2 {
            color: #123665;
            font-size: 28px;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .action-card {
            display: block;
            background: white;
            padding: 22px;
            border-radius: 12px;
            text-decoration: none;
            color: #263445;
            box-shadow: 0 3px 12px #00000008;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px #00000012;
        }

        .action-card h3 {
            color: #16834A;
            font-size: 17px;
            margin-bottom: 9px;
        }

        .action-card p {
            color: #657386;
            font-size: 13px;
            line-height: 1.6;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            padding: 22px;
            overflow-x: auto;
            box-shadow: 0 3px 12px #00000008;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 15px 12px;
            border-bottom: 1px solid #E9EDF0;
            font-size: 14px;
        }

        th {
            color: #123665;
            background: #F3F6F5;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .pending {
            background: #FFF1CC;
            color: #8A6100;
        }

        .approved {
            background: #E8F5EC;
            color: #106638;
        }

        .rejected {
            background: #FCE8E8;
            color: #A32626;
        }

        .empty {
            color: #657386;
            padding: 20px 0;
            line-height: 1.6;
        }

        footer {
            text-align: center;
            padding: 25px;
            margin-top: 35px;
            color: #657386;
            font-size: 13px;
        }

        @media (max-width: 1000px) {
            .stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .container {
                width: 92%;
                margin-top: 20px;
            }

            nav {
                padding: 18px 5%;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .actions {
                grid-template-columns: 1fr;
            }

            .welcome h1 {
                font-size: 23px;
            }

            .stat-card {
                padding: 18px 14px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a href="index.php" class="logo">StudyCircle</a>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="materials.php">Materials</a>
        <a href="discussions.php">Forum</a>
        <a href="groups.php">Study Groups</a>
        <a href="upload_material.php">Upload</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">

    <section class="welcome">
        <h1>
            Welcome back,
            <?= htmlspecialchars($student_name, ENT_QUOTES, "UTF-8") ?>!
        </h1>

        <p>
            Welcome to your StudyCircle dashboard.
            Track your study materials and continue learning with your
            university community.
        </p>
    </section>

    <h2 class="section-title">Your Overview</h2>

    <section class="stats">

        <div class="stat-card">
            <p>Total Uploads</p>
            <h2><?= (int) $material_counts["total"] ?></h2>
        </div>

        <div class="stat-card">
            <p>Pending</p>
            <h2><?= (int) $material_counts["pending"] ?></h2>
        </div>

        <div class="stat-card">
            <p>Approved</p>
            <h2><?= (int) $material_counts["approved"] ?></h2>
        </div>

        <div class="stat-card">
            <p>Rejected</p>
            <h2><?= (int) $material_counts["rejected"] ?></h2>
        </div>

        <div class="stat-card">
            <p>Total Downloads</p>
            <h2><?= $total_downloads ?></h2>
        </div>

    </section>

    <h2 class="section-title">Quick Access</h2>

    <section class="actions">

        <a href="upload_material.php" class="action-card">
            <h3>Upload Materials</h3>
            <p>Share your notes and educational resources with other students.</p>
        </a>

        <a href="materials.php" class="action-card">
            <h3>Browse Materials</h3>
            <p>Find and download approved study resources.</p>
        </a>

        <a href="discussions.php" class="action-card">
            <h3>Discussion Forum</h3>
            <p>Ask academic questions and learn from other students.</p>
        </a>

        <a href="groups.php" class="action-card">
            <h3>Study Groups</h3>
            <p>Explore groups and connect with fellow learners.</p>
        </a>

    </section>

    <h2 class="section-title">My Recent Uploads</h2>

    <section class="table-container">

        <?php if (count($recent_uploads) > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>Material Title</th>
                        <th>Subject</th>
                        <th>Uploaded Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($recent_uploads as $material): ?>
                        <?php
                        $status = strtolower($material["status"] ?? "pending");
                        $allowed_statuses = ["pending", "approved", "rejected"];

                        if (!in_array($status, $allowed_statuses, true)) {
                            $status = "pending";
                        }
                        ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    $material["title"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $material["subject_name"] ?? "Not specified",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $material["created_at"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <span class="status <?= $status ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>

            <p class="empty">
                You haven't uploaded any materials yet.
                Start by sharing your first study resource!
            </p>

            <a href="upload_material.php">Upload your first material</a>

        <?php endif; ?>

    </section>

</main>

<footer>
    &copy; <?= date("Y") ?> StudyCircle | NSBM Green University
</footer>

</body>
</html>