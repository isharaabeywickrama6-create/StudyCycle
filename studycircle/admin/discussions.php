<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_discussion"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION['csrf_token'], $_POST["csrf_token"])
    ) {
        $error = "Invalid request. Please try again.";
    } else {
        $discussion_id = filter_input(
            INPUT_POST,
            "discussion_id",
            FILTER_VALIDATE_INT
        );

        if (!$discussion_id || $discussion_id < 1) {
            $error = "Invalid discussion ID.";
        } else {
            try {
                $conn->begin_transaction();

                $stmt = $conn->prepare(
                    "DELETE FROM answers WHERE discussion_id = ?"
                );
                $stmt->bind_param("i", $discussion_id);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare(
                    "DELETE FROM discussions WHERE discussion_id = ?"
                );
                $stmt->bind_param("i", $discussion_id);
                $stmt->execute();

                $deleted = $stmt->affected_rows;
                $stmt->close();

                $conn->commit();

                if ($deleted > 0) {
                    $message = "Discussion deleted successfully.";
                } else {
                    $message = "That discussion no longer exists.";
                }
            } catch (Throwable $e) {
                $conn->rollback();
                $error = "Unable to delete the discussion. Check its related database records.";
            }
        }
    }
}

$search = trim($_GET["search"] ?? "");

$sql = "
    SELECT
        d.discussion_id,
        d.title,
        d.body,
        d.created_at,
        u.name AS author_name,
        s.subject_name
    FROM discussions d
    LEFT JOIN users u ON d.user_id = u.user_id
    LEFT JOIN subjects s ON d.subject_id = s.subject_id
";

if ($search !== "") {
    $sql .= "
        WHERE d.title LIKE ? OR d.body LIKE ? OR u.name LIKE ?
    ";
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);

if ($search !== "") {
    $search_term = "%" . $search . "%";
    $stmt->bind_param(
        "sss",
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
    <title>Manage Discussions - StudyCircle</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            margin: 0;
            padding: 25px;
            color: #222;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        h1 {
            color: #18264f;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #5b3cc4;
            text-decoration: none;
        }

        .search-box, .discussion {
            background: white;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        input[type="text"] {
            width: 65%;
            max-width: 450px;
            padding: 10px;
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

        .discussion-body {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .empty {
            background: white;
            padding: 20px;
            border-radius: 8px;
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

    <a class="back" href="dashboard.php">← Back to Admin Dashboard</a>

    <h1>Manage Discussions</h1>
    <p>Review student discussions and remove inappropriate posts.</p>

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
            placeholder="Search title, content, or author..."
            value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>"
        >
        <button type="submit" class="search-button">Search</button>
        <a href="discussions.php">Show All</a>
    </form>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($discussion = $result->fetch_assoc()): ?>
            <div class="discussion">

                <h2>
                    <?= htmlspecialchars(
                        $discussion["title"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </h2>

                <p class="details">
                    <strong>Author:</strong>
                    <?= htmlspecialchars(
                        $discussion["author_name"] ?? "Unknown",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                    |
                    <strong>Subject:</strong>
                    <?= htmlspecialchars(
                        $discussion["subject_name"] ?? "No subject",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                    |
                    <strong>Date:</strong>
                    <?= htmlspecialchars(
                        $discussion["created_at"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

                <p class="discussion-body">
                    <?= htmlspecialchars(
                        $discussion["body"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

                <form method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this discussion and its answers? This cannot be undone.');">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="discussion_id"
                        value="<?= (int) $discussion["discussion_id"] ?>"
                    >

                    <button
                        type="submit"
                        name="delete_discussion"
                        class="delete-button"
                    >
                        Delete Discussion
                    </button>
                </form>

            </div>
        <?php endwhile; ?>

    <?php else: ?>
        <div class="empty">
            No discussions found.
        </div>
    <?php endif; ?>

</div>
</body>
</html>

<?php
$stmt->close();
?>