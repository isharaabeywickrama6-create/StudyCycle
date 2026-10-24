<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

function getCount($conn, $sql) {
    $result = $conn->query($sql);

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int) ($row["total"] ?? 0);
}

$totalUsers = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

$totalStudents = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'student'"
);

$totalMaterials = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM materials"
);

$pendingMaterials = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM materials WHERE status = 'pending'"
);

$approvedMaterials = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM materials WHERE status = 'approved'"
);

$rejectedMaterials = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM materials WHERE status = 'rejected'"
);

$totalDiscussions = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM discussions"
);

$totalGroups = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM study_groups"
);

$totalSubjects = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM subjects"
);

$totalDownloads = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM resource_downloads"
);

$recentUsers = $conn->query(
    "SELECT name, email, created_at
     FROM users
     WHERE role = 'student'
     ORDER BY created_at DESC
     LIMIT 5"
);

$popularMaterials = $conn->query(
    "SELECT m.title, COUNT(rd.download_id) AS download_count
     FROM materials m
     LEFT JOIN resource_downloads rd
        ON m.material_id = rd.material_id
     GROUP BY m.material_id, m.title
     ORDER BY download_count DESC, m.title ASC
     LIMIT 5"
);
?>

<html>
<head>
    <title>Admin Reports - StudyCircle</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #222;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1, h2 {
            color: #18264f;
        }

        .back {
            display: inline-block;
            margin-bottom: 18px;
            color: #5b3cc4;
            text-decoration: none;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }

        .card, .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .card h3 {
            color: #666;
            font-size: 15px;
            margin-top: 0;
        }

        .number {
            color: #5b3cc4;
            font-size: 30px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            overflow-wrap: anywhere;
        }

        th {
            background: #f0edff;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .empty {
            color: #666;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <a class="back" href="dashboard.php">
        ← Back to Admin Dashboard
    </a>

    <h1>StudyCircle Admin Reports</h1>
    <p>Overview of users, study materials, discussions, and study groups.</p>

    <div class="stats">

        <div class="card">
            <h3>Total Users</h3>
            <div class="number"><?= $totalUsers ?></div>
        </div>

        <div class="card">
            <h3>Registered Students</h3>
            <div class="number"><?= $totalStudents ?></div>
        </div>

        <div class="card">
            <h3>Total Study Materials</h3>
            <div class="number"><?= $totalMaterials ?></div>
        </div>

        <div class="card">
            <h3>Pending Materials</h3>
            <div class="number"><?= $pendingMaterials ?></div>
        </div>

        <div class="card">
            <h3>Approved Materials</h3>
            <div class="number"><?= $approvedMaterials ?></div>
        </div>

        <div class="card">
            <h3>Rejected Materials</h3>
            <div class="number"><?= $rejectedMaterials ?></div>
        </div>

        <div class="card">
            <h3>Total Discussions</h3>
            <div class="number"><?= $totalDiscussions ?></div>
        </div>

        <div class="card">
            <h3>Total Study Groups</h3>
            <div class="number"><?= $totalGroups ?></div>
        </div>

        <div class="card">
            <h3>Total Subjects</h3>
            <div class="number"><?= $totalSubjects ?></div>
        </div>

        <div class="card">
            <h3>Material Downloads</h3>
            <div class="number"><?= $totalDownloads ?></div>
        </div>

    </div>

    <div class="section">
        <h2>Recent Student Registrations</h2>

        <div class="table-wrap">
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Registration Date</th>
            </tr>

            <?php if ($recentUsers && $recentUsers->num_rows > 0): ?>
                <?php while ($user = $recentUsers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user["name"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($user["created_at"], ENT_QUOTES, "UTF-8") ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="empty">No student registrations found.</td>
                </tr>
            <?php endif; ?>
        </table>
        </div>
    </div>

    <div class="section">
        <h2>Most Downloaded Materials</h2>

        <div class="table-wrap">
        <table>
            <tr>
                <th>Material Title</th>
                <th>Download Count</th>
            </tr>

            <?php if ($popularMaterials && $popularMaterials->num_rows > 0): ?>
                <?php while ($material = $popularMaterials->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($material["title"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= (int) $material["download_count"] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="empty">No study materials found.</td>
                </tr>
            <?php endif; ?>
        </table>
        </div>
    </div>

</div>
</body>
</html>