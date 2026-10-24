<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT user_id, name, password_hash, role
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (
        $user &&
        $user["role"] === "admin" &&
        password_verify($password, $user["password_hash"])
    ) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["name"] = $user["name"];

        header("Location: dashboard.php");
        exit;
    }

    $error = "Invalid email or password, or admin access is not permitted.";
    $stmt->close();
}
?>

<html>
<head>
    <title>Admin Login | StudyCircle</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
            background: linear-gradient(
                135deg,
                #eef2ff,
                #f8f7ff,
                #ede9fe
            );
            color: #1e293b;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 38px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(30, 27, 75, 0.12);
        }

        .logo {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            border-radius: 17px;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 30px;
            font-weight: bold;
        }

        h1 {
            text-align: center;
            color: #172554;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .field {
            margin-bottom: 21px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
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

        .error-message {
            padding: 12px 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 27px 22px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">

        <div class="logo">S</div>

        <h1>Welcome Back!</h1>

        <p class="subtitle">
            Sign in to the StudyCircle administrator portal
            to manage your academic community.
        </p>

        <?php if ($error !== "") { ?>
            <div class="error-message" role="alert">
                <?php
                echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </div>
        <?php } ?>

        <form method="POST">

            <div class="field">
                <label for="email">Admin Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your admin email"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="field">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit">
                Sign In
            </button>

        </form>

        <p class="footer">
            StudyCircle &copy; 2026<br>
            NSBM Academic Community
        </p>

    </div>

</body>
</html>

<?php
$conn->close();
?>