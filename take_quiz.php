<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

$quizzes = [];
$stmt = $conn->prepare("SELECT QuizID, Quiz_Title FROM Quiz");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}
$stmt->close();

$show_results = false;
$results = [];

if (isset($_GET['quiz_id'])) {
    $quiz_id = (int)$_GET['quiz_id'];
    $questions = [];
    $stmt = $conn->prepare("SELECT q.QuestionID, q.Question_Text, o.OptionID, o.Option_Text, o.Is_Correct 
                            FROM Question q 
                            LEFT JOIN Option o ON q.QuestionID = o.QuestionID 
                            WHERE q.QuizID = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[$row['QuestionID']]['text'] = $row['Question_Text'];
        $questions[$row['QuestionID']]['options'][] = [
            'id' => $row['OptionID'],
            'text' => $row['Option_Text'],
            'correct' => $row['Is_Correct']
        ];
    }
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $score = 0;
        $total = count($questions);
        $conn->begin_transaction();
        try {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'answer_') === 0) {
                    $question_id = (int)str_replace('answer_', '', $key);
                    $selected_answer = $value === 'idontknow' ? null : (int)$value;
                    $is_correct = 0;
                    $correct_option_text = '';
                    $selected_option_text = $value === 'idontknow' ? "I don't know" : '';

                    foreach ($questions[$question_id]['options'] as $opt) {
                        if ($opt['correct']) {
                            $correct_option_text = $opt['text'];
                        }
                        if ($selected_answer == $opt['id']) {
                            $selected_option_text = $opt['text'];
                        }
                    }

                    if ($selected_answer !== null) {
                        $stmt = $conn->prepare("SELECT Is_Correct FROM Option WHERE OptionID = ?");
                        $stmt->bind_param("i", $selected_answer);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $option = $result->fetch_assoc();
                            $is_correct = $option['Is_Correct'] ? 1 : 0;
                            if ($is_correct) $score++;
                        }
                        $stmt->close();
                    }

                    $stmt = $conn->prepare("INSERT INTO Student_Answer (UserID, QuestionID, Selected_Answer, Score) VALUES (?, ?, ?, ?)");
                    $null_value = null;
                    if ($selected_answer === null) {
                        $stmt->bind_param("iisi", $_SESSION['user_id'], $question_id, $null_value, $is_correct);
                    } else {
                        $stmt->bind_param("iiii", $_SESSION['user_id'], $question_id, $selected_answer, $is_correct);
                    }
                    $stmt->execute();
                    $stmt->close();

                    $results[$question_id] = [
                        'question_text' => $questions[$question_id]['text'],
                        'correct_answer' => $correct_option_text,
                        'selected_answer' => $selected_option_text,
                        'is_correct' => $is_correct
                    ];
                }
            }
            $conn->commit();
            $success = "Quiz submitted! Score: $score/$total";
            $show_results = true;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error submitting quiz: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - E-Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --card-shadow: 0 20px 40px rgba(0,0,0,0.1);
            --card-hover-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        /* Animated background particles */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Glassmorphism navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            font-size: 1.5rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--success-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-2px);
        }

        /* Main container */
        .container {
            padding: 40px 20px;
        }

        /* Quiz container with glassmorphism */
        .quiz-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--card-shadow);
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .quiz-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            opacity: 0;
        }

        .quiz-container:hover::before {
            animation: shimmer 2s ease-in-out;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); opacity: 0; }
        }

        /* Typography */
        h2 {
            color: #fff;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--success-gradient);
            border-radius: 2px;
        }

        h3, h5 {
            color: #fff;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Alert styles */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            animation: slideIn 0.5s ease-out;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #fff;
            border-left: 4px solid #4caf50;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.2);
            color: #fff;
            border-left: 4px solid #f44336;
        }

        .alert-info {
            background: rgba(33, 150, 243, 0.2);
            color: #fff;
            border-left: 4px solid #2196f3;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Quiz list styling */
        .quiz-list {
            list-style: none;
            padding: 0;
        }

        .quiz-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            margin-bottom: 15px;
            padding: 20px;
            display: flex;
            justify-content: between;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .quiz-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .quiz-item:hover::before {
            left: 100%;
        }

        .quiz-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .quiz-title {
            color: #fff;
            font-weight: 500;
            font-size: 1.1rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Button styles */
        .btn-creative {
            background: var(--primary-gradient);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-creative::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-creative:hover::before {
            left: 100%;
        }

        .btn-creative:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            color: #fff;
        }

        .btn-secondary-creative {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .btn-secondary-creative:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        /* Timer styling */
        #timer {
            background: var(--danger-gradient);
            color: #fff;
            padding: 15px 30px;
            border-radius: 50px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: pulse 2s infinite;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Question box styling */
        .question-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .question-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--success-gradient);
            border-radius: 20px 20px 0 0;
        }

        .question-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .question-text {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Custom radio buttons */
        .form-check {
            margin-bottom: 15px;
            position: relative;
        }

        .form-check-input {
            display: none;
        }

        .form-check-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            cursor: pointer;
            padding: 15px 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            display: block;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .form-check-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .form-check-label:hover::before {
            left: 100%;
        }

        .form-check-label:hover {
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateX(5px);
            color: #fff;
        }

        .form-check-input:checked + .form-check-label {
            background: var(--success-gradient);
            border-color: transparent;
            color: #fff;
            font-weight: 600;
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .idontknow {
            font-style: italic;
            opacity: 0.8;
        }

        .form-check-input:checked + .idontknow {
            background: var(--secondary-gradient);
        }

        /* Results styling */
        .result-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .result-box.correct::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--success-gradient);
            border-radius: 20px 20px 0 0;
        }

        .result-box.incorrect::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--danger-gradient);
            border-radius: 20px 20px 0 0;
        }

        .correct-answer {
            color: #4caf50;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(76, 175, 80, 0.3);
        }

        .text-success {
            color: #4caf50 !important;
            font-weight: 600;
        }

        .text-danger {
            color: #f44336 !important;
            font-weight: 600;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px;
                margin: 10px;
            }
            
            .quiz-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            #timer {
                font-size: 1.1rem;
                padding: 12px 20px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated background particles -->
    <div class="bg-particles" id="particles"></div>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>E-Learning Platform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="quiz-container">
            <h2><i class="fas fa-brain me-3"></i>Take Quiz</h2>
            
            <?php if (isset($error)) { echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>$error</div>"; } ?>
            <?php if (isset($success)) { echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>$success</div>"; } ?>
            
            <?php if (!isset($_GET['quiz_id'])) { ?>
                <?php if (empty($quizzes)) { ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No quizzes available at the moment.
                    </div>
                <?php } else { ?>
                    <ul class="quiz-list">
                        <?php foreach ($quizzes as $quiz) { ?>
                            <li class="quiz-item">
                                <div class="quiz-title">
                                    <i class="fas fa-clipboard-question me-2"></i>
                                    <?php echo htmlspecialchars($quiz['Quiz_Title']); ?>
                                </div>
                                <a href="take_quiz.php?quiz_id=<?php echo $quiz['QuizID']; ?>" class="btn btn-creative">
                                    <i class="fas fa-play me-2"></i>Start Quiz
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            <?php } elseif ($show_results) { ?>
                <h3 class="mb-4">
                    <i class="fas fa-chart-line me-2"></i>Quiz Results
                </h3>
                <?php foreach ($results as $qid => $result) { ?>
                    <div class="result-box <?php echo $result['is_correct'] ? 'correct' : 'incorrect'; ?>">
                        <h5 class="question-text">
                            <i class="fas fa-question-circle me-2"></i>
                            <?php echo htmlspecialchars($result['question_text']); ?>
                        </h5>
                        <div class="mb-2">
                            <i class="fas fa-check-circle me-2"></i>
                            <span class="correct-answer">Correct Answer: <?php echo htmlspecialchars($result['correct_answer']); ?></span>
                        </div>
                        <p class="mb-0" style="color: rgba(255,255,255,0.9);">
                            <i class="fas fa-user-edit me-2"></i>
                            <strong>Your Answer:</strong> 
                            <span class="<?php echo $result['is_correct'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo htmlspecialchars($result['selected_answer']); ?>
                            </span>
                            <i class="fas fa-<?php echo $result['is_correct'] ? 'check' : 'times'; ?> ms-2"></i>
                            (<?php echo $result['is_correct'] ? 'Correct' : 'Incorrect'; ?>)
                        </p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <form method="POST" id="quizForm">
                    <div id="timer">
                        <i class="fas fa-clock me-2"></i>Time Left: 05:00
                    </div>
                    
                    <?php foreach ($questions as $qid => $q) { ?>
                        <div class="question-box">
                            <h5 class="question-text">
                                <i class="fas fa-question-circle me-2"></i>
                                <?php echo htmlspecialchars($q['text']); ?>
                            </h5>
                            <?php foreach ($q['options'] as $opt) { ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_<?php echo $qid; ?>" value="<?php echo $opt['id']; ?>" id="option_<?php echo $opt['id']; ?>" required>
                                    <label class="form-check-label" for="option_<?php echo $opt['id']; ?>">
                                        <i class="fas fa-circle me-2"></i>
                                        <?php echo htmlspecialchars($opt['text']); ?>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer_<?php echo $qid; ?>" value="idontknow" id="idontknow_<?php echo $qid; ?>">
                                <label class="form-check-label idontknow" for="idontknow_<?php echo $qid; ?>">
                                    <i class="fas fa-question me-2"></i>
                                    I don't know
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <button type="submit" class="btn btn-creative me-3">
                        <i class="fas fa-paper-plane me-2"></i>Submit Quiz
                    </button>
                </form>
            <?php } ?>
            
            <a href="student_dashboard.php" class="btn btn-secondary-creative btn-creative mt-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Create animated background particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 5 + 2;
                const left = Math.random() * 100;
                const animationDuration = Math.random() * 3 + 3;
                const delay = Math.random() * 2;

                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = left + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDuration = animationDuration + 's';
                particle.style.animationDelay = delay + 's';

                particlesContainer.appendChild(particle);
            }
        }

        // Timer functionality
        function formatTime(seconds) {
            let minutes = Math.floor(seconds / 60);
            let secs = seconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        let timerElement = document.getElementById('timer');
        if (timerElement) {
            let remainingTime = 120; // 2 minutes
            timerElement.innerHTML = `<i class="fas fa-clock me-2"></i>Time Left: ${formatTime(remainingTime)}`;
            
            let timerInterval = setInterval(() => {
                remainingTime--;
                if (remainingTime >= 0) {
                    timerElement.innerHTML = `<i class="fas fa-clock me-2"></i>Time Left: ${formatTime(remainingTime)}`;
                    
                    // Change color when time is running out
                    if (remainingTime <= 30) {
                        timerElement.style.background = 'var(--danger-gradient)';
                        timerElement.style.animation = 'pulse 1s infinite';
                    }
                } else {
                    clearInterval(timerInterval);
                    
                    // Auto-select "I don't know" for unanswered questions
                    let questionNames = [...new Set(Array.from(document.querySelectorAll('input[name^="answer_"]')).map(input => input.name))];
                    questionNames.forEach(name => {
                        let radios = document.querySelectorAll(`input[name="${name}"]`);
                        let isChecked = Array.from(radios).some(radio => radio.checked);
                        if (!isChecked) {
                            let idontknowRadio = document.querySelector(`input[name="${name}"][value="idontknow"]`);
                            if (idontknowRadio) idontknowRadio.checked = true;
                        }
                    });
                    
                    // Show loading state
                    timerElement.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                    document.getElementById('quizForm').submit();
                }
            }, 1000);
        }

        // Add click animations to buttons
        document.querySelectorAll('.btn-creative').forEach(btn => {
            btn.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                ripple.classList.add('ripple');
                this.appendChild(ripple);

                let x = e.clientX - e.target.offsetLeft;
                let y = e.clientY - e.target.offsetTop;

                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add hover effect to question boxes
        document.querySelectorAll('.question-box').forEach(box => {
            box.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            box.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add selection animation
        document.querySelectorAll('.form-check-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.checked) {
                    // Add selection animation
                    this.nextElementSibling.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.nextElementSibling.style.transform = 'scale(1.02)';
                    }, 150);
                    
                    // Deselect other options in the same question
                    const questionName = this.name;
                    document.querySelectorAll(`input[name="${questionName}"]`).forEach(otherInput => {
                        if (otherInput !== this && otherInput.checked) {
                            otherInput.nextElementSibling.style.transform = 'scale(1)';
                        }
                    });
                }
            });
        });

        // Initialize particles on page load
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Add entrance animation to elements
            const elements = document.querySelectorAll('.quiz-container > *');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Add ripple effect styles
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .btn-creative {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>