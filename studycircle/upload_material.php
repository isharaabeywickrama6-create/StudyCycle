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

$userId = (int) $_SESSION["user_id"];

$errors = [];
$title = "";
$description = "";
$subjectId = "";

$subjects = $conn->query(
    "SELECT subject_id, subject_name
     FROM subjects
     ORDER BY subject_name"
);

if (empty($_SESSION["upload_csrf"])) {
    $_SESSION["upload_csrf"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $subjectId = $_POST["subject_id"] ?? "";

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals(
            $_SESSION["upload_csrf"],
            $_POST["csrf_token"]
        )
    ) {
        $errors[] = "Invalid form request. Please refresh the page.";
    }

    if ($title === "") {
        $errors[] = "Please enter a material title.";
    } elseif (strlen($title) > 200) {
        $errors[] = "The title must not exceed 200 characters.";
    }

    if (strlen($description) > 5000) {
        $errors[] = "The description must not exceed 5000 characters.";
    }

    $subjectCheck = $conn->prepare(
        "SELECT subject_id FROM subjects WHERE subject_id = ?"
    );

    if ($subjectCheck) {
        $selectedSubjectId = (int) $subjectId;
        $subjectCheck->bind_param("i", $selectedSubjectId);
        $subjectCheck->execute();
        $subjectResult = $subjectCheck->get_result();

        if (
            $subjectId === "" ||
            $subjectResult->num_rows === 0
        ) {
            $errors[] = "Please select a valid subject.";
        }

        $subjectCheck->close();
    } else {
        $errors[] = "Unable to validate the subject.";
    }

    if (
        !isset($_FILES["material_file"]) ||
        $_FILES["material_file"]["error"] !== UPLOAD_ERR_OK
    ) {
        $errors[] = "Please select a PDF file to upload.";
    } else {
        $file = $_FILES["material_file"];

        if ($file["size"] > 10 * 1024 * 1024) {
            $errors[] = "The PDF must be 10 MB or smaller.";
        }

        $extension = strtolower(
            pathinfo($file["name"], PATHINFO_EXTENSION)
        );

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file["tmp_name"]);

        if (
            $extension !== "pdf" ||
            $mimeType !== "application/pdf"
        ) {
            $errors[] = "Only valid PDF files are accepted.";
        }
    }

    if (empty($errors)) {

        $uploadDirectory = __DIR__
            . DIRECTORY_SEPARATOR . "uploads"
            . DIRECTORY_SEPARATOR . "materials";

        if (!is_dir($uploadDirectory)) {
            $errors[] = "The materials upload folder does not exist.";
        } else {
            $newFileName = bin2hex(random_bytes(16)) . ".pdf";

            $destination = $uploadDirectory
                . DIRECTORY_SEPARATOR . $newFileName;

            $relativePath = "uploads/materials/" . $newFileName;

            if (!move_uploaded_file(
                $_FILES["material_file"]["tmp_name"],
                $destination
            )) {
                $errors[] = "The file could not be saved. Please try again.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO materials
                    (user_id, subject_id, title, description,
                     file_path, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())"
                );

                if ($stmt) {
                    $selectedSubjectId = (int) $subjectId;

                    $stmt->bind_param(
                        "iisss",
                        $userId,
                        $selectedSubjectId,
                        $title,
                        $description,
                        $relativePath
                    );

                    if ($stmt->execute()) {
                        // Prevent accidental form resubmission.
                        $_SESSION["upload_csrf"] =
                            bin2hex(random_bytes(32));

                        header(
                            "Location: upload_material.php?success=1"
                        );
                        exit;
                    } else {
                        // Remove the saved file if the database insert fails.
                        if (is_file($destination)) {
                            unlink($destination);
                        }

                        $errors[] =
                            "The material could not be saved to the database.";
                    }

                    $stmt->close();
                } else {
                    if (is_file($destination)) {
                        unlink($destination);
                    }

                    $errors[] = "Unable to prepare the database query.";
                }
            }
        }
    }
}

$success = isset($_GET["success"]);
?>

<html>
<head>
    <title>Upload Study Material | StudyCircle</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #F3F6F5;
            color: #263445;
            line-height: 1.6;
        }

        nav {
            background: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 10px #0000000D;
        }

        .logo {
            color: #16834A;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
        }

        nav a:not(.logo) {
            color: #174A8B;
            text-decoration: none;
            font-weight: bold;
            margin-left: 16px;
            font-size: 14px;
        }

        .container {
            max-width: 760px;
            width: 90%;
            margin: 40px auto;
        }

        .heading {
            margin-bottom: 22px;
        }

        .heading h1 {
            margin-bottom: 6px;
            color: #174A8B;
        }

        .heading p {
            color: #657386;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 18px #2634450D;
        }

        .form-group {
            margin-bottom: 20px;

        input[type="file"] {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            background: #F3F6F5;
            border: 1px solid #CCD4D9;
            border-radius: 8px;
            color: #263445;
            font-size: 14px;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            background: #16834A;
            color: #FFFFFF;
            border: none;
            padding: 10px 16px;
            margin-right: 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        input[type="file"]::file-selector-button:hover {
            background: #106638;
        }

        input[type="file"]:focus-visible {
            outline: 2px solid #174A8B;
            outline-offset: 3px;
        }
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input[type="text"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #D7E0E5;
            border-radius: 7px;
            font-size: 15px;
            background: white;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: 2px solid #16834A;
            border-color: #16834A;
        }

        .hint {
            color: #657386;
            font-size: 13px;
            margin-top: 5px;
        }

        button {
            border: none;
            border-radius: 7px;
            padding: 13px 20px;
            background: #16834A;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #106638;
        }

        .alert {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 7px;
        }

        .error {
            background: #FDECEC;
            color: #9B2226;
        }

        .success {
            background: #E8F5EC;
            color: #106638;
        }

        .notice {
            background: #E8F0FF;
            color: #174A8B;
            padding: 14px;
            margin-bottom: 22px;
            border-radius: 7px;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 20px;
            }

            nav {
                padding: 15px 5%;
            }

            nav a:not(.logo) {
                margin-left: 0;
                margin-right: 12px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a href="index.php" class="logo">StudyCircle</a>

    <div>
        <a href="index.php">Home</a>
        <a href="materials.php">Materials</a>
        <a href="upload_material.php">Upload</a>
    </div>
</nav>

<main class="container">

    <div class="heading">
        <h1>Share Study Materials</h1>
        <p>
            Help your fellow students by sharing useful academic resources.
        </p>
    </div>

    <?php if ($success): ?>
        <div class="alert success">
            Your material was submitted successfully and is waiting
            for admin approval.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="notice">
        Your upload will not appear in the public materials list
        until an administrator approves it. Upload PDF files only,
        up to 10 MB.
    </div>

    <div class="form-card">

        <form method="POST"
              action="upload_material.php"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION["upload_csrf"]) ?>">

            <div class="form-group">
                <label for="title">Material Title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="200"
                    value="<?= htmlspecialchars($title) ?>"
                    placeholder="e.g. Introduction to Programming Notes"
                    required
                >
            </div>

            <div class="form-group">
                <label for="subject_id">Subject *</label>

                <select id="subject_id" name="subject_id" required>
                    <option value="">-- Select a subject --</option>

                    <?php if ($subjects): ?>
                        <?php while ($subject = $subjects->fetch_assoc()): ?>
                            <option
                                value="<?= (int) $subject["subject_id"] ?>"
                                <?= (string) $subjectId ===
                                    (string) $subject["subject_id"]
                                    ? "selected" : "" ?>
                            >
                                <?= htmlspecialchars($subject["subject_name"]) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    maxlength="5000"
                    placeholder="Briefly describe the material..."
                ><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="form-group">
                <label for="material_file">Choose PDF File *</label>
                <input
                    type="file"
                    id="material_file"
                    name="material_file"
                    accept=".pdf,application/pdf"
                    required
                >
                <p class="hint">PDF format only. Maximum file size: 10 MB.</p>
            </div>

            <button type="submit">Submit for Approval</button>

        </form>

    </div>

</main>

</body>
</html>