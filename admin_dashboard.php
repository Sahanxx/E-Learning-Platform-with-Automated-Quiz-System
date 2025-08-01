<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Function to check if table exists
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}

// Initialize variables
$students = null;
$quiz_results = null;
$chat_logs = null;
$selected_user = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$tables_missing = false;

try {
    // Fetch student list
    if (tableExists($conn, 'user')) {
        $stmt_students = $conn->prepare("SELECT UserID, Username FROM user WHERE Role = 'student'");
        $stmt_students->execute();
        $students = $stmt_students->get_result();
    } else {
        $tables_missing = true;
    }

    // Fetch quiz results for selected user
    if (tableExists($conn, 'student_answer')) {
        $stmt_quiz = $conn->prepare("
            SELECT sa.UserID, q.QuizID, q.Question_Text, o.Option_Text, sa.Selected_Answer, sa.Score, sa.Answer_Date
            FROM student_answer sa
            JOIN question q ON sa.QuestionID = q.QuestionID
            JOIN option o ON sa.Selected_Answer = o.OptionID
            WHERE sa.UserID = ?
            ORDER BY sa.Answer_Date DESC
        ");
        $stmt_quiz->bind_param("i", $selected_user);
        $stmt_quiz->execute();
        $quiz_results = $stmt_quiz->get_result();
    } else {
        $tables_missing = true;
    }

    // Fetch chatbot chats for selected user
    if (tableExists($conn, 'chatbot_log')) {
        $stmt_chats = $conn->prepare("SELECT LogID, UserID, Query, Response, Log_Date FROM chatbot_log WHERE UserID = ? ORDER BY Log_Date DESC");
        $stmt_chats->bind_param("i", $selected_user);
        $stmt_chats->execute();
        $chat_logs = $stmt_chats->get_result();
    } else {
        $tables_missing = true;
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Center - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --shadow-strong: 0 30px 60px rgba(0, 0, 0, 0.2);
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
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        /* Header */
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
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Action Cards */
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
            text-decoration: none;
        }

        .action-card:hover::before {
            left: 100%;
        }

        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        /* Content Cards */
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

        /* Tabs */
        .custom-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .tab-button {
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid var(--glass-border);
            border-radius: 15px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            min-width: 200px;
            justify-content: center;
        }

        .tab-button.active {
            background: var(--accent-gradient);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .tab-button:hover:not(.active) {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        /* Data Display */
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

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInAlert 0.5s ease-out;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            border: 1px solid #fed7aa;
            color: #f97316;
        }

        @keyframes slideInAlert {
            from { opacity: 0; transform: translateY(-20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Student Selector */
        .student-selector {
            margin-bottom: 2rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-primary);
        }

        .form-select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 6px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px) scale(1.02);
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1.25rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h4 {
            font-size: 1.25rem;
            font-weight: 600;
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

            .custom-tabs {
                flex-direction: column;
            }

            .tab-button {
                min-width: auto;
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
            
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard')">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="upload_notes.php" class="nav-link">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Upload Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="delete_notes.php" class="nav-link">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="create_quiz.php" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Quiz</span>
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
            <!-- Header -->
            <div class="content-header">
                <h1 class="header-title">
                    <i class="fas fa-crown" style="margin-right: 1rem;"></i>
                    Admin Control Center
                </h1>
                <p class="header-subtitle">Manage your e-learning platform</p>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section">
                <!-- Action Cards -->
                <div class="action-grid">
                    <a href="upload_notes.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="action-title">Upload Notes</div>
                        <div class="action-description">Add new study materials</div>
                    </a>
                    <a href="delete_notes.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <div class="action-title">Delete Notes</div>
                        <div class="action-description">Remove unwanted materials</div>
                    </a>
                    <a href="create_quiz.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="action-title">Create Quiz</div>
                        <div class="action-description">Design new assessments</div>
                    </a>
                </div>

                <!-- Student Selector -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-user-graduate"></i>
                            Select Student
                        </h2>
                    </div>
                    <form method="post" class="student-selector">
                        <label for="user_id" class="form-label">
                            <i class="fas fa-user-graduate me-2"></i>
                            Choose a Student
                        </label>
                        <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Choose a Student --</option>
                            <?php if ($students && $students->num_rows > 0) {
                                while ($row = $students->fetch_assoc()) {
                                    $selected = ($selected_user == $row['UserID']) ? 'selected' : '';
                                    echo "<option value='{$row['UserID']}' $selected>{$row['Username']} (ID: {$row['UserID']})</option>";
                                }
                                $students->data_seek(0);
                            } ?>
                        </select>
                    </form>
                </div>

                <!-- Alerts -->
                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php } ?>
                <?php if ($tables_missing) { ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-database me-2"></i>
                        One or more database tables are missing.
                    </div>
                <?php } ?>

                <!-- Tabs -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Student Analytics
                        </h2>
                    </div>
                    <div class="custom-tabs">
                        <button class="tab-button active" onclick="showTab('quiz')">Quiz Analytics</button>
                        <button class="tab-button" onclick="showTab('chats')">Chat History</button>
                    </div>
                    <div id="quiz-tab" class="tab-content">
                        <?php if ($selected_user && $quiz_results && $quiz_results->num_rows > 0) { ?>
                            <div class="data-container">
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Quiz ID</th>
                                                <th>Question</th>
                                                <th>Selected Option</th>
                                                <th>Score</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $quiz_results->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['QuizID']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Question_Text']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Option_Text']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Score']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Answer_Date']); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php } elseif ($selected_user && !$tables_missing) { ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <h4>No Quiz Results</h4>
                                <p>This student hasn't taken any quizzes.</p>
                            </div>
                        <?php } else { ?>
                            <div class="empty-state">
                                <i class="fas fa-user-plus"></i>
                                <h4>Select a Student</h4>
                                <p>Choose a student to view their quiz performance.</p>
                            </div>
                        <?php } ?>
                    </div>
                    <div id="chats-tab" class="tab-content" style="display: none;">
                        <?php if ($selected_user && $chat_logs && $chat_logs->num_rows > 0) { ?>
                            <div class="data-container">
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Log ID</th>
                                                <th>Query</th>
                                                <th>Response</th>
                                                <th>Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $chat_logs->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['LogID']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Query']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Response']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['Log_Date']); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php } elseif ($selected_user && !$tables_missing) { ?>
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                <h4>No Chat History</h4>
                                <p>This student hasn't used the chatbot.</p>
                            </div>
                        <?php } else { ?>
                            <div class="empty-state">
                                <i class="fas fa-user-plus"></i>
                                <h4>Select a Student</h4>
                                <p>Choose a student to view their chat history.</p>
                            </div>
                        <?php } ?>
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

        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.style.display = 'none');
            document.getElementById(tabName + '-tab').style.display = 'block';
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
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
if (isset($stmt_students)) $stmt_students->close();
if (isset($stmt_quiz)) $stmt_quiz->close();
if (isset($stmt_chats)) $stmt_chats->close();
if (isset($conn)) $conn->close();
?>