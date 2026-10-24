<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $materialId = filter_input(
        INPUT_POST,
        "material_id",
        FILTER_VALIDATE_INT
    );

    $newStatus = $_POST["status"] ?? "";

    if ($materialId && in_array($newStatus, ["approved", "rejected"], true)) {

        $stmt = $conn->prepare(
            "UPDATE materials SET status = ? 
             WHERE material_id = ? AND status = 'pending'"
        );

        if ($stmt) {
            $stmt->bind_param("si", $newStatus, $materialId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $message = "Material status updated successfully.";
            } else {
                $message = "No pending material was updated.";
            }

            $stmt->close();
        } else {
            $message = "Could not prepare the update.";
        }
    } else {
        $message = "Invalid material details.";
    }
}

$sql = "SELECT
            m.material_id,
            m.title,
            m.description,
            m.file_path,
            m.status,
            m.created_at,
            u.name AS uploader,
            s.subject_name
        FROM materials m
        LEFT JOIN users u ON m.user_id = u.user_id
        LEFT JOIN subjects s ON m.subject_id = s.subject_id
        ORDER BY
            CASE WHEN m.status = 'pending' THEN 0 ELSE 1 END,
            m.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Could not load materials. Please check the database tables.");
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<html>
<head>
    <title>Material Approval | StudyCircle</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f5f6fc;
            color: #202747;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            color: #172554;
        }

        a {
            color: #5141a5;
            text-decoration: none;
        }

        .notice {
            padding: 12px;
            margin: 18px 0;
            background: #eae8fb;
            border-radius: 8px;
        }

        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px #17255412;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #ececf4;
            vertical-align: top;
        }

        th {
            background: #eae8fb;
            color: #27215d;
        }

        .status {
            font-weight: bold;
            text-transform: capitalize;
        }

        .pending {
            color: #a16207;
        }

        .approved {
            color: #15803d;
        }

        .rejected {
            color: #b91c1c;
        }

        button {
            background: #16834A;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 7px;
            cursor: pointer;
        }

        button:hover {
            background: #106638;
        }

        .approve {
            background: #15803d;
        }

        .reject {
            background: #b91c1c;
        }

        .empty {
            padding: 20px;
            text-align: center;
        }

        @media (max-width: 600px) {
            body {
                padding: 14px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <a href="dashboard.php">&larr; Back to Dashboard</a>

    <h1>Study Material Approval</h1>
    <p>Review student-submitted materials and manage their approval status.</p>

    <?php if ($message !== ""): ?>
        <div class="notice"><?= e($message) ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title and Description</th>
                    <th>Uploaded By</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($material = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= e($material["material_id"]) ?></td>

                        <td>
                            <strong><?= e($material["title"]) ?></strong>
                            <p><?= e($material["description"]) ?></p>
                        </td>

                        <td><?= e($material["uploader"] ?? "Unknown") ?></td>

                        <td><?= e($material["subject_name"] ?? "Not assigned") ?></td>

                        <td><?= e($material["created_at"]) ?></td>

                        <td class="status <?= e($material["status"]) ?>">
                            <?= e($material["status"]) ?>
                        </td>

                        <td>
                            <?php if ($material["status"] === "pending"): ?>
                                <form method="POST"
                                      onsubmit="return confirm('Approve this material?');">
                                    <input type="hidden" name="material_id"
                                           value="<?= e($material["material_id"]) ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button class="approve" type="submit">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST"
                                      onsubmit="return confirm('Reject this material?');">
                                    <input type="hidden" name="material_id"
                                           value="<?= e($material["material_id"]) ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="reject" type="submit">
                                        Reject
                                    </button>
                                </form>
                            <?php else: ?>
                                No action needed
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">
                        No study materials have been submitted yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>