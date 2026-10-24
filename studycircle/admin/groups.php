<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

$message = "";
$error = "";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_group"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $error = "Invalid request. Please try again.";
    } else {
        $group_id = filter_input(
            INPUT_POST,
            "group_id",
            FILTER_VALIDATE_INT
        );

        if (!$group_id || $group_id < 1) {
            $error = "Invalid group ID.";
        } else {
            try {
                $conn->begin_transaction();

                $stmt = $conn->prepare(
                    "DELETE FROM study_sessions WHERE group_id = ?"
                );
                $stmt->bind_param("i", $group_id);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare(
                    "DELETE FROM group_members WHERE group_id = ?"
                );
                $stmt->bind_param("i", $group_id);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare(
                    "DELETE FROM study_groups WHERE group_id = ?"
                );
                $stmt->bind_param("i", $group_id);
                $stmt->execute();

                $deleted = $stmt->affected_rows;
                $stmt->close();

                $conn->commit();

                $message = $deleted > 0
                    ? "Study group deleted successfully."
                    : "The study group was not found.";

            } catch (Throwable $e) {
                $conn->rollback();
                $error = "Could not delete this group. Check related database records.";
            }
        }
    }
}

$search = trim($_GET["search"] ?? "");

$sql = "
    SELECT
        g.group_id,
        g.group_name,
        g.description,
        g.created_at,
        u.name AS creator_name,
        s.subject_name,
        (
            SELECT COUNT(*)
            FROM group_members gm
            WHERE gm.group_id = g.group_id
        ) AS member_count
    FROM study_groups g
    LEFT JOIN users u ON g.creator_id = u.user_id
    LEFT JOIN subjects s ON g.subject_id = s.subject_id
";

if ($search !== "") {
    $sql .= "
        WHERE g.group_name LIKE ?
           OR g.description LIKE ?
           OR u.name LIKE ?
           OR s.subject_name LIKE ?
    ";
}

$sql .= " ORDER BY g.created_at DESC";

$stmt = $conn->prepare($sql);

if ($search !== "") {
    $search_term = "%" . $search . "%";
    $stmt->bind_param(
        "ssss",
        $search_term,
        $search_term,
        $search_term,
        $search_term
    );
}

$stmt->execute();
$result = $stmt->get_result();
?>

<html>
<head>
    <title>Manage Study Groups - StudyCircle</title>

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

        h1 {
            color: #18264f;
        }

        a {
            color: #5b3cc4;
            text-decoration: none;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
        }

        .search-box,
        .group-card {
            background: white;
            padding: 18px;
            margin-bottom: 16px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        input[type="text"] {
            padding: 10px;
            width: 60%;
            max-width: 450px;
            border: 1px solid #ccc;
            border-radius: 6px;
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

        .search-button {
            background: #5b3cc4;
            color: white;
        }

        .delete-button {
            background: #c62828;
            color: white;
        }

        .message {
            background: #e2f5e8;
            color: #176b35;
            padding: 12px;
            border-radius: 6px;
        }

        .error {
            background: #fde8e8;
            color: #a61b1b;
            padding: 12px;
            border-radius: 6px;
        }

        .details {
            color: #666;
            font-size: 14px;
        }

        .description {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            input[type="text"] {
                width: 100%;
                box-sizing: border-box;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <a class="back" href="dashboard.php">
        ← Back to Admin Dashboard
    </a>

    <h1>Manage Study Groups</h1>
    <p>View study groups, their creators, subjects, and members.</p>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="GET" class="search-box">
        <input
            type="text"
            name="search"
            placeholder="Search group, creator, or subject..."
            value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>"
        >

        <button class="search-button" type="submit">
            Search
        </button>

        <a href="groups.php">Show All</a>
    </form>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($group = $result->fetch_assoc()): ?>
            <div class="group-card">

                <h2>
                    <?= htmlspecialchars(
                        $group["group_name"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </h2>

                <p class="description">
                    <?= htmlspecialchars(
                        $group["description"] ?? "No description provided.",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

                <p class="details">
                    <strong>Creator:</strong>
                    <?= htmlspecialchars(
                        $group["creator_name"] ?? "Unknown",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                    <br>

                    <strong>Subject:</strong>
                    <?= htmlspecialchars(
                        $group["subject_name"] ?? "No subject",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                    <br>

                    <strong>Members:</strong>
                    <?= (int) $group["member_count"] ?>
                    <br>

                    <strong>Created:</strong>
                    <?= htmlspecialchars(
                        $group["created_at"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

                <form method="POST"
                    onsubmit="return confirm('Delete this study group, its memberships, and its scheduled sessions? This cannot be undone.');">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION["csrf_token"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="group_id"
                        value="<?= (int) $group["group_id"] ?>"
                    >

                    <button
                        type="submit"
                        name="delete_group"
                        class="delete-button"
                    >
                        Delete Group
                    </button>
                </form>

            </div>
        <?php endwhile; ?>

    <?php else: ?>
        <div class="group-card">
            No study groups found.
        </div>
    <?php endif; ?>

</div>
</body>
</html>

<?php
$stmt->close();
?>