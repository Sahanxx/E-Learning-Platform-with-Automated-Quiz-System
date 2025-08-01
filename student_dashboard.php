<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

// Fetch student's details
$stmt = $conn->prepare("SELECT * FROM User WHERE UserID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            --warning-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --shadow-soft: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 75%, #f5576c 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        .bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            animation: float 20s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) { width: 180px; height: 180px; top: 15%; left: 8%; animation-delay: 0s; }
        .floating-shape:nth-child(2) { width: 120px; height: 120px; top: 70%; right: 15%; animation-delay: 7s; }
        .floating-shape:nth-child(3) { width: 90px; height: 90px; bottom: 25%; left: 20%; animation-delay: 14s; }
        .floating-shape:nth-child(4) { width: 140px; height: 140px; top: 40%; right: 25%; animation-delay: 21s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            25% { transform: translateY(-40px) rotate(90deg); opacity: 0.6; }
            50% { transform: translateY(-80px) rotate(180deg); opacity: 0.4; }
            75% { transform: translateY(-40px) rotate(270deg); opacity: 0.7; }
        }

        /* Main Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 10;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border-right: 1px solid var(--glass-border);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .logo i {
            font-size: 2rem;
            background: var(--accent-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid var(--glass-border);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .user-name {
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .user-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--accent-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .content-header {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #f0f0f0);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-medium);
            color: white;
        }

        .action-card:hover::before {
            left: 100%;
        }

        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--accent-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .action-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .action-description {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .content-card {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            color: var(--text-primary);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .user-details-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            color: var(--text-primary);
        }

        .detail-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1000;
                background: var(--glass-bg);
                backdrop-filter: blur(25px);
                border: 1px solid var(--glass-border);
                border-radius: 10px;
                padding: 0.75rem;
                color: white;
                cursor: pointer;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .content-header {
                padding: 1.5rem;
            }

            .header-title {
                font-size: 2rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }
        }

        .mobile-menu-btn {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Background Decoration -->
    <div class="bg-decoration">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>EduLearn</span>
                </a>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name"><?php echo htmlspecialchars($user['Username']); ?></div>
                <div class="user-role">Student</div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard')">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#profile" class="nav-link" onclick="showSection('profile')">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#notes" class="nav-link" onclick="showSection('notes')">
                            <i class="fas fa-file-alt"></i>
                            <span>Lecture Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#quizzes" class="nav-link" onclick="showSection('quizzes')">
                            <i class="fas fa-chart-line"></i>
                            <span>Quiz Results</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#chatbot" class="nav-link" onclick="showSection('chatbot')">
                            <i class="fas fa-robot"></i>
                            <span>Chatbot History</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="header-title">
                    <i class="fas fa-user-graduate" style="margin-right: 1rem;"></i>
                    Student Dashboard
                </h1>
                <p class="header-subtitle">Welcome back, <?php echo htmlspecialchars($user['Username']); ?>! Ready to continue your learning journey?</p>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section">
                <div class="action-grid">
                    <a href="take_quiz.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="action-title">Take Quiz</div>
                        <div class="action-description">Test your knowledge with interactive quizzes</div>
                    </a>
                    <a href="chatbot.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="action-title">Chatbot Support</div>
                        <div class="action-description">Get instant help with your questions</div>
                    </a>
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profile-section" class="content-section" style="display: none;">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-user-circle"></i>
                            My Profile
                        </h2>
                    </div>
                    <div class="user-details-card">
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Username</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['Username']); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Email Address</ metalloprotein
                                <div class="detail-value"><?php echo htmlspecialchars($user['Email']); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Student ID</div>
                                <div class="detail-value">STU<?php echo $_SESSION['user_id']; ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Role</div>
                                <div class="detail-value">Student</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div id="notes-section" class="content-section" style="display: none;">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-file-alt"></i>
                            Lecture Notes
                        </h2>
                    </div>
                    <div class="data-container">
                        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">
                            <i class="fas fa-book" style="margin-right: 0.5rem;"></i>
                            Available Study Materials
                        </h3>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Upload Date</th>
                                        <th>Download</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->prepare("SELECT Title, Upload_Date, File_Path FROM Lecture_Note");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
                                        echo "<td>" . $row['Upload_Date'] . "</td>";
                                        echo "<td><a href='" . $row['File_Path'] . "' class='btn btn-sm' style='background: var(--primary-gradient); color: white; border: none; border-radius: 8px;'><i class='fas fa-download me-1'></i>Download</a></td>";
                                        echo "</tr>";
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quizzes Section -->
            <div id="quizzes-section" class="content-section" style="display: none;">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Quiz Results
                        </h2>
                    </div>
                    <div class="data-container">
                        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">
                            <i class="fas fa-trophy" style="margin-right: 0.5rem;"></i>
                            Your Quiz Performance
                        </h3>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Date Answered</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->prepare("SELECT q.Quiz_Title, sa.Answer_Date, SUM(sa.Score) as TotalScore, COUNT(sa.QuestionID) as TotalQuestions
                                                            FROM Student_Answer sa
                                                            JOIN Question qs ON sa.QuestionID = qs.QuestionID
                                                            JOIN Quiz q ON qs.QuizID = q.QuizID
                                                            WHERE sa.UserID = ?
                                                            GROUP BY q.QuizID, sa.Answer_Date");
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        $score_percentage = ($row['TotalQuestions'] > 0) ? round(($row['TotalScore'] / $row['TotalQuestions']) * 100, 2) : 0;
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['Quiz_Title']) . "</td>";
                                        echo "<td>" . $row['Answer_Date'] . "</td>";
                                        echo "<td><span style='background: var(--success-gradient); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600;'>" . $score_percentage . "%</span></td>";
                                        echo "</tr>";
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chatbot Section -->
            <div id="chatbot-section" class="content-section" style="display: none;">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-robot"></i>
                            Chatbot History
                        </h2>
                    </div>
                    <div class="data-container">
                        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">
                            <i class="fas fa-comments" style="margin-right: 0.5rem;"></i>
                            Your Chat History
                        </h3>
                        <div class="list-group">
                            <?php
                            $stmt = $conn->prepare("SELECT Query, Response, Log_Date FROM Chatbot_Log WHERE UserID = ? ORDER BY Log_Date DESC");
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<div class='list-group-item' style='background: white; border: 1px solid #e2e8f0; margin-bottom: 1rem; border-radius: 10px; padding: 1rem;'>";
                                    echo "<strong>You:</strong> " . htmlspecialchars($row['Query']) . "<br>";
                                    echo "<strong>Chatbot:</strong> " . htmlspecialchars($row['Response']) . "<br>";
                                    echo "<small style='color: var(--text-secondary);'>" . $row['Log_Date'] . "</small>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<div class='empty-state' style='color: var(--text-primary);'>";
                                echo "<i class='fas fa-comments'></i>";
                                echo "<h3>No Chat History</h3>";
                                echo "<p>Start a conversation with our AI assistant to get help with your studies.</p>";
                                echo "<a href='chatbot.php' class='btn' style='background: var(--accent-gradient); color: white; border: none; border-radius: 10px; padding: 0.75rem 2rem;'><i class='fas fa-comments me-2'></i>Start Chatting</a>";
                                echo "</div>";
                            }
                            $stmt->close();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(sectionName) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => section.style.display = 'none');
            document.getElementById(sectionName + '-section').style.display = 'block';
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(event.target) && 
                !menuBtn.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>
<?php
if (isset($conn)) $conn->close();
?>