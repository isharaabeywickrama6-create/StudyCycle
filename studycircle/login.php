<?php
session_start();

require_once "config/database.php";

if (
    isset($_SESSION["user_id"]) &&
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "student"
) {
    header("Location: index.php");
    exit;
}

$error = "";
$registered = isset($_GET["registered"]);
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password_hash, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (
                $user &&
                $user["role"] === "student" &&
                password_verify($password, $user["password_hash"])
            ) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = (int) $user["user_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["role"] = "student";

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid student email or password.";
            }

            $stmt->close();
        } else {
            $error = "Unable to process login. Please try again.";
        }
    }
}
?>

<html>
<head>
    <title>Student Login | StudyCircle</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #F3F6F5;
            color: #263445;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #FFFFFF;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 5px 22px rgba(0, 0, 0, 0.08);
        }

        .logo {
            color: #16834A;
            font-size: 27px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #657386;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 16px 0 7px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #CCD4D9;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #16834A;
            border-color: #16834A;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 22px;
            border: none;
            border-radius: 7px;
            background: #16834A;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #106638;
        }

        .error {
            padding: 12px;
            background: #FDECEC;
            color: #A32121;
            border-radius: 7px;
            margin-bottom: 15px;
        }

        .links {
            text-align: center;
            margin-top: 20px;
            line-height: 1.8;
        }

        a {
            color: #174A8B;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo">StudyCircle</div>

        <h1>Student Login</h1>

        <p class="subtitle">
            Welcome back! Sign in to access your academic resources.
        </p>

        <?php if ($error !== ""): ?>
            <?php if ($registered): ?>
                <p style="background:#E8F5EC; color:#106638; padding:12px; border-radius:7px;">
                    Registration successful! You can now log in.
                </p>
        <?php endif; ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Student Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Sign In</button>
        </form>

        <div class="links">
            <a href="index.php">Back to Home</a>
            <br>
            <a href="admin/login.php">Administrator Login</a>
        </div>
    </div>
</body>
</html>