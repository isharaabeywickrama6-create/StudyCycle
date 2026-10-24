<html>
<head>
    <title>StudyCircle | NSBM Learning Community</title>

    <style>
        :root {
            --green: #16834A;
            --dark-green: #106638;
            --blue: #174A8B;
            --dark-blue: #123665;
            --light-green: #E8F5EC;
            --background: #F3F6F5;
            --text: #263445;
            --muted: #657386;
            --white: #FFFFFF;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--text);
            background: var(--background);
            line-height: 1.6;
            background-image: url("nsbm_jpg.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        nav {
            background: var(--white);
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px #26344510;
        }

        .logo {
            color: var(--dark-green);
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
        }

        .nav-links a:hover {
            color: var(--green);
        }

        .login-link {
            border: 1px solid var(--green);
            padding: 9px 17px;
            border-radius: 8px;
            color: var(--dark-green) !important;
        }

        .hero {
            padding: 75px 6%;
            text-align: center;
            background: linear-gradient(
                var(--dark-green),
                var(--green),
                var(--dark-green)
                );
            color: var(--white);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero h1 {
            font-size: clamp(32px, 5vw, 52px);
            line-height: 1.2;
            margin: 0 auto 20px;
            max-width: 850px;
        }

        .hero p {
            max-width: 650px;
            margin: 0 auto 28px;
            color: #E8F5EC;
            font-size: 18px;
        }

        .button {
            display: inline-block;
            padding: 13px 22px;
            background: var(--white);
            color: var(--dark-green);
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 5px;
        }

        .button:hover {
            background: var(--light-green);
        }

        .button-outline {
            border: 1px solid var(--white);
            background: transparent;
            color: var(--white);
        }

        .button-outline:hover {
            background: #FFFFFF20;
            color: var(--white);
        }

        .container {
            max-width: 1150px;
            margin: auto;
            padding: 50px 22px;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 30px;
            background-color: #FFFFFF;
            border-radius: 20px;
            padding: 30px 22px;
        }

        .section-heading h2 {
            color: var(--dark-blue);
            font-size: 30px;
            margin-bottom: 8px;
        }

        .section-heading p {
            color: var(--muted);
            margin-top: 0;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(
                auto-fit, minmax(220px, 1fr)
            );
            gap: 22px;
        }

        .feature {
            background: var(--white);
            padding: 25px;
            border-radius: 14px;
            border-top: 4px solid var(--green);
            box-shadow: 0 4px 15px #2634450D;
        }

        .feature-icon {
            font-size: 32px;
        }

        .feature h3 {
            color: var(--dark-blue);
            margin: 12px 0 8px;
        }

        .feature p {
            color: var(--muted);
            font-size: 15px;
        }

        .feature a {
            color: var(--dark-green);
            font-weight: bold;
            text-decoration: none;
        }

        .join-section {
            background: var(--light-green);
            text-align: center;
            padding: 45px 20px;
        }

        .join-section h2 {
            color: var(--dark-blue);
        }

        .join-button {
            display: inline-block;
            background: var(--green);
            color: var(--white);
            padding: 12px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .join-button:hover {
            background: var(--dark-green);
        }

        footer {
            background: var(--dark-blue);
            color: var(--white);
            text-align: center;
            padding: 22px;
        }

        footer p {
            margin: 5px;
        }

        @media (max-width: 600px) {
            nav {
                padding: 15px 5%;
            }

            .nav-links {
                gap: 12px;
            }

            .hero {
                padding: 55px 20px;
            }

            .container {
                padding: 35px 18px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a href="index.php" class="logo">StudyCircle</a>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="#features">Features</a>
        <a href="materials.php">Materials</a>
        <a href="discussions.php">Forum</a>
        <a href="groups.php">Study Groups</a>
        <a href="upload_material.php">Upload Materials</a>
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<section class="hero">
    <h1>Learn Together. Grow Together.</h1>

    <p>
        Welcome to StudyCircle, a student learning community
        designed to share knowledge, discover study resources,
        discuss academic questions, and learn together.
    </p>

    <a href="register.php" class="button">Join StudyCircle</a>
    <a href="#features" class="button button-outline">
        Explore Features
    </a>
</section>

<main>
    <section class="container" id="features">

        <div class="section-heading">
            <h2>Explore StudyCircle</h2>
            <p>Everything you need to support your academic journey.</p>
        </div>

        <div class="features">

            <div class="feature">
                <div class="feature-icon">📚</div>
                <h3>Study Materials</h3>
                <p>
                    Discover lecture notes, revision resources,
                    and other academic materials shared by students.
                </p><br>
                <a href="materials.php">Explore Materials →</a>
            </div>

            <div class="feature">
                <div class="feature-icon">💬</div>
                <h3>Academic Forum</h3>
                <p>
                    Ask academic questions, share ideas, and
                    learn from discussions with other students.
                </p>
                <a href="discussions.php">Visit Forum →</a>
            </div>

            <div class="feature">
                <div class="feature-icon">🎓</div>
                <h3>Study Groups</h3>
                <p>
                    Discover study groups, connect with classmates,
                    and collaborate on your learning goals.
                </p>
                <a href="groups.php">Explore Groups →</a>
            </div>

            <div class="feature">
                <div class="feature-icon">🤝</div>
                <h3>Student Community</h3>
                <p>
                    Share knowledge, support fellow students,
                    and build a collaborative learning community.
                </p>
                <a href="register.php">Join the Community →</a>
            </div>

        </div>
    </section>

    <section class="join-section">
        <h2>Your Learning Journey Starts Here</h2>
        <p>Become part of a community that learns and grows together.</p>
        <a href="register.php" class="join-button">
            Get Started
        </a>
    </section>
</main>

<footer>
    <p><strong>StudyCircle | NSBM Learning Community</strong></p>
    <p>Learn together. Share knowledge. Grow together.</p>
</footer>

</body>
</html>