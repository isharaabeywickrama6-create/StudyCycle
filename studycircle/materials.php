<?php
require_once "config/database.php";

$sql = "SELECT
            materials.material_id,
            materials.title,
            materials.description,
            materials.file_path,
            materials.created_at,
            subjects.subject_name,
            users.name AS uploader_name
        FROM materials
        LEFT JOIN subjects
            ON materials.subject_id = subjects.subject_id
        LEFT JOIN users
            ON materials.user_id = users.user_id
        WHERE materials.status = 'approved'
        ORDER BY materials.created_at DESC";

$result = $conn->query($sql);
?>

<html>
<head>

    <title>Study Materials | StudyCircle</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #F3F6F5;
            color: #263445;
            line-height: 1.6;
        }

        .navbar {
            background: white;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px #0000000D;
        }

        .logo {
            color: #16834A;
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
        }

        .nav-links a {
            text-decoration: none;
            color: #263445;
            font-size: 14px;
            font-weight: bold;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #16834A;
        }

        .hero {
            background: linear-gradient(#106638, #16834A, #106638);
            color: white;
            padding: 55px 7%;
        }

        .hero p {
            margin-top: 10px;
            max-width: 650px;
            color: #F0F5FF;
        }

        .hero h1 {
            font-size: 34px;
            margin-bottom: 5px;
        }

        .container {
            max-width: 1150px;
            width: 86%;
            margin: 35px auto 60px;
        }

        .section-heading {
            margin-bottom: 20px;
        }

        .section-heading h2 {
            font-size: 25px;
            color: #263445;
        }

        .section-heading p {
            color: #657386;
            margin-top: 4px;
        }

        .search-box {
            margin-bottom: 28px;
        }

        .search-box input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #D7E0E5;
            border-radius: 9px;
            background: white;
            font-size: 15px;
            outline: none;
        }

        .search-box input:focus {
            border-color: #16834A;
            box-shadow: 0 0 0 3px #16834A18;
        }

        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }

        .material-card {
            background: white;
            border: 1px solid #E4EAE7;
            border-radius: 12px;
            padding: 23px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 14px #2634450A;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .material-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 22px #26344512;
        }

        .file-icon {
            width: 48px;
            height: 48px;
            background: #E8F5EC;
            color: #16834A;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            margin-bottom: 17px;
        }

        .subject-tag {
            display: inline-block;
            align-self: flex-start;
            background: #E8F5EC;
            color: #106638;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .material-card h3 {
            font-size: 19px;
            line-height: 1.4;
            margin-bottom: 10px;
            overflow-wrap: anywhere;
        }

        .description {
            color: #657386;
            font-size: 14px;
            margin-bottom: 18px;
            overflow-wrap: anywhere;
        }

        .material-meta {
            color: #657386;
            font-size: 13px;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #EDF0F2;
        }

        .download-btn {
            display: block;
            text-align: center;
            background: #16834A;
            color: white;
            padding: 11px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 17px;
        }

        .download-btn:hover {
            background: #106638;
        }

        .unavailable {
            background: #F1F3F5;
            color: #657386;
            padding: 10px;
            text-align: center;
            border-radius: 7px;
            margin-top: 17px;
            font-size: 13px;
        }

        .empty-state {
            background: white;
            border: 1px dashed #C9D6CF;
            border-radius: 12px;
            padding: 45px 20px;
            text-align: center;
            grid-column: 1 / -1;
        }

        .empty-state h3 {
            color: #263445;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #657386;
        }

        .no-search-results {
            display: none;
            color: #657386;
            margin-top: 20px;
        }

        footer {
            text-align: center;
            background: var(--dark-blue);
            border-top: 1px solid #E4EAE7;
            padding: 22px;
            color: #657386;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 15px 5%;
            }

            .nav-links {
                gap: 14px;
            }

            .hero {
                padding: 38px 6%;
            }

            .hero h1 {
                font-size: 27px;
            }

            .container {
                width: 90%;
                margin-top: 25px;
            }
        }

        .search-box::selection {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            max-width: 850px;
            margin: 28px auto 35px;
        }

        .search-box input[type="search"],
        .search-box select {
            box-sizing: border-box;
            height: 50px;
            padding: 0 16px;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            background: #ffffff;
            color: #263445;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box input[type="search"] {
            flex: 1;
            min-width: 0;
        }

        .search-box select {
            flex: 0 0 220px;
            cursor: pointer;
        }

        .search-box input[type="search"]:focus,
        .search-box select:focus {
            border-color: #16834a;
            box-shadow: 0 0 0 3px rgba(22, 131, 74, 0.12);
        }

        .search-box input::placeholder {
            color: #8793a2;
        }

        @media (max-width: 600px) {
            .search-box {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .search-box input[type="search"],
            .search-box select {
                width: 100%;
                flex: auto;
            }
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            max-width: 850px;
            margin: 28px auto 35px;
            box-sizing: border-box;
        }

        .search-box input[type="search"],
        .search-box select {
            box-sizing: border-box;
            height: 50px;
            padding: 0 16px;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            background-color: #ffffff;
            color: #263445;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box input[type="search"] {
            flex: 1;
            min-width: 0;
        }

        .search-box select {
            flex: 0 0 220px;
            width: 220px;
            cursor: pointer;
            appearance: auto;
        }

        .search-box input[type="search"]:focus,
        .search-box select:focus {
            border-color: #16834a;
            box-shadow: 0 0 0 3px rgba(22, 131, 74, 0.12);
        }

        .search-box input::placeholder {
            color: #8793a2;
        }

        @media (max-width: 600px) {
            .search-box {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .search-box input[type="search"],
            .search-box select {
                width: 100%;
                flex: auto;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a class="logo" href="index.php">StudyCircle</a>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="materials.php" class="active">Materials</a>
        <a href="discussions.php">Discussions</a>
        <a href="groups.php">Study Groups</a>
        <a href="login.php">Login</a>
        <a href="upload_material.php">Upload Materials</a>
    </div>
</nav>

<section class="hero">
    <h1>Study Materials</h1>
    <p>
        Explore academic notes, lecture materials and other learning
        resources shared by the StudyCircle community.
    </p>
</section>

<main class="container">

    <div class="section-heading">
        <h2>Explore Resources</h2>
        <p>Find learning materials to support your studies.</p>
    </div>

    <div class="search-box">
        <input
            type="search"
            id="materialSearch"
            placeholder="Search by material title, subject or description..."
            aria-label="Search study materials"
        >
        <select id="subjectFilter">
            <option value="">All Subjects</option>
            <?php
            $subjectSql = "SELECT subject_name
            FROM subjects
            ORDER BY subject_name ASC";

            $subjectResult = $conn->query($subjectSql);

            if ($subjectResult) {
                while ($subject = $subjectResult->fetch_assoc()) {
                    echo '<option value="' .
                        htmlspecialchars($subject["subject_name"], ENT_QUOTES, "UTF-8") .
                        '">' .
                        htmlspecialchars($subject["subject_name"], ENT_QUOTES, "UTF-8") .
                        '</option>';
                }
            }
            ?>
        </select>
    </div>

    <div class="materials-grid" id="materialsGrid">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($material = $result->fetch_assoc()): ?>

                <?php
                $filePath = $material["file_path"] ?? "";
                $fullPath = __DIR__ . DIRECTORY_SEPARATOR
                    . str_replace("/", DIRECTORY_SEPARATOR, $filePath);

                $realFile = realpath($fullPath);
                $realRoot = realpath(__DIR__);

                $fileAvailable = (
                    $filePath !== "" &&
                    $realFile !== false &&
                    $realRoot !== false &&
                    strpos(
                        $realFile,
                        $realRoot . DIRECTORY_SEPARATOR
                    ) === 0 &&
                    is_file($realFile)
                );
                ?>

                <article class="material-card">

                    <div class="file-icon" aria-hidden="true">
                        &#128196;
                    </div>

                    <span class="subject-tag material-subject">
                        <?= htmlspecialchars(
                            $material["subject_name"] ?? "General"
                        ) ?>
                    </span>

                    <h3>
                        <?= htmlspecialchars($material["title"]) ?>
                    </h3>

                    <p class="description">
                        <?= htmlspecialchars(
                            $material["description"] ?? "No description provided."
                        ) ?>
                    </p>

                    <div class="material-meta">
                        <p>
                            <strong>Shared by:</strong>
                            <?= htmlspecialchars(
                                $material["uploader_name"] ?? "Student"
                            ) ?>
                        </p>

                        <p>
                            <strong>Date added:</strong>
                            <?= htmlspecialchars(
                                date(
                                    "d M Y",
                                    strtotime($material["created_at"])
                                )
                            ) ?>
                        </p>
                    </div>

                    <?php if ($fileAvailable): ?>

                        <a
                            class="download-btn"
                            href="<?= htmlspecialchars($filePath) ?>"
                            download
                        >
                            Download Material
                        </a>

                    <?php else: ?>

                        <div class="unavailable">
                            File currently unavailable
                        </div>

                    <?php endif; ?>

                </article>

            <?php endwhile; ?>

            <div class="empty-state" id="noSearchResults"
                 style="display: none;">
                <h3>No matching materials found</h3>
                <p>Try another title, subject or keyword.</p>
            </div>

        <?php else: ?>

            <div class="empty-state">
                <h3>No study materials available yet</h3>
                <p>
                    Approved learning resources will appear here
                    when they are shared with the community.
                </p>
            </div>

        <?php endif; ?>

    </div>
</main>

<footer>
    <p>StudyCircle | Learn Together, Grow Together</p>
    <p>&copy; <?= date("Y") ?> StudyCircle. All rights reserved.</p>
</footer>


<script>
    const searchInput = document.getElementById("materialSearch");
    const subjectFilter = document.getElementById("subjectFilter");
    const cards = document.querySelectorAll(".material-card");
    const noResults = document.getElementById("noSearchResults");

    function filterMaterials() {
        const keyword = searchInput.value.toLowerCase().trim();
        const selectedSubject = subjectFilter.value.toLowerCase().trim();

        let visibleCount = 0;

        cards.forEach(function (card) {
            const cardText = card.textContent.toLowerCase();

            const subjectElement = card.querySelector(".material-subject");
            const cardSubject = subjectElement
                ? subjectElement.textContent.toLowerCase().trim()
                : "";

            const matchesSearch = cardText.includes(keyword);

            const matchesSubject =
                selectedSubject === "" ||
                cardSubject === selectedSubject;

            const shouldShow = matchesSearch && matchesSubject;

            card.style.display = shouldShow ? "flex" : "none";

            if (shouldShow) {
                visibleCount++;
            }
        });

        if (noResults) {
            noResults.style.display =
                cards.length > 0 && visibleCount === 0
                    ? "block"
                    : "none";
        }
    }

    searchInput.addEventListener("input", filterMaterials);
    subjectFilter.addEventListener("change", filterMaterials);
</script>


</body>
</html>