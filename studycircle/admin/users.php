<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $sql = "SELECT user_id, name, email, role, created_at
            FROM users
            WHERE name LIKE ? OR email LIKE ?
            ORDER BY user_id DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database query preparation failed.");
    }

    $searchValue = "%" . $search . "%";
    $stmt->bind_param("ss", $searchValue, $searchValue);
    $stmt->execute();

    $result = $stmt->get_result();
} else {
    $sql = "SELECT user_id, name, email, role, created_at
            FROM users
            ORDER BY user_id DESC";

    $result = $conn->query($sql);

    if (!$result) {
        die("Could not retrieve users.");
    }
}

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

function e($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<html>
<head>
    <title>Manage Users | StudyCircle</title>

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
            max-width: 1100px;
            margin: 0 auto;
        }

        h1 {
            color: #172554;
        }

        .back {
            color: #5b45b5;
            text-decoration: none;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        input {
            flex: 1;
            min-width: 0;
            padding: 12px;
            border: 1px solid #d7d9e8;
            border-radius: 8px;
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

        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px #17255412;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ececf4;
        }

        th {
            background: #eae8fb;
            color: #27215d;
        }

        .role {
            font-weight: bold;
            text-transform: capitalize;
        }

        .message {
            padding: 18px;
            color: #555;
        }

        @media (max-width: 600px) {
            body {
                padding: 14px;
            }

            .search-box {
                flex-wrap: wrap;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <a class="back" href="dashboard.php">
        &larr; Back to Dashboard
    </a>

    <h1>Manage Users</h1>
    <p>View registered StudyCircle users and search their details.</p>

    <form class="search-box" method="GET">
        <input
            type="text"
            name="search"
            placeholder="Search by name or email"
            value="<?= e($search) ?>"
        >
        <button type="submit">Search</button>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                </tr>
            </thead>

            <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user["user_id"]) ?></td>
                        <td><?= e($user["name"]) ?></td>
                        <td><?= e($user["email"]) ?></td>
                        <td class="role"><?= e($user["role"]) ?></td>
                        <td><?= e($user["created_at"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="message">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p>Total users displayed: <?= count($users) ?></p>

</div>
</body>
</html>
