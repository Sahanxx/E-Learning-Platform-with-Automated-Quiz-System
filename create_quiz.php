<?php
session_start();
include 'config.php';

// Include Smalot/PdfParser
use Smalot\PdfParser\Parser;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$notes = [];
$stmt = $conn->prepare("SELECT NoteID, Title, File_Path FROM Lecture_Note WHERE UserID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}
$stmt->close();

$ai_error = false; // Flag to indicate if AI generation failed
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $note_id = $_POST['note_id'];
    $generate_ai = isset($_POST['generate_ai']) && $_POST['generate_ai'] == '1';

    if (empty($title) || empty($note_id)) {
        $error = "Quiz title and lecture note are required.";
    } else {
        $conn->begin_transaction();
        try {
            // Insert quiz
            $stmt = $conn->prepare("INSERT INTO Quiz (NoteID, Quiz_Title) VALUES (?, ?)");
            $stmt->bind_param("is", $note_id, $title);
            $stmt->execute();
            $quiz_id = $conn->insert_id;
            $stmt->close();

            if ($generate_ai) {
                // Get lecture note file path
                $stmt = $conn->prepare("SELECT File_Path FROM Lecture_Note WHERE NoteID = ?");
                $stmt->bind_param("i", $note_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $note = $result->fetch_assoc();
                $stmt->close();

                // Extract text from PDF using Smalot/PdfParser
                try {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($note['File_Path']);
                    $lecture_content = $pdf->getText();

                    // Clean up the extracted text
                    $lecture_content = preg_replace('/\s+/', ' ', trim($lecture_content));
                    if (empty($lecture_content)) {
                        throw new Exception("No text could be extracted from the PDF. Please ensure the PDF contains readable text.");
                    }

                    // Validate content length (increased to 200 characters for 10 questions)
                    if (strlen($lecture_content) < 200) {
                        throw new Exception("The extracted text is too short to generate 10 questions. Please use a more detailed lecture note or try manual entry.");
                    }
                } catch (Exception $e) {
                    throw new Exception("Error extracting text from PDF: " . $e->getMessage());
                }

                // Generate 10 questions using OpenRouter API
                $prompt = "You are a quiz generator for an e-learning platform. Based on the following lecture content, generate exactly 10 multiple-choice questions to comprehensively cover the material, ensuring each question focuses on a different aspect of the content (e.g., definitions, applications, examples, comparisons, implications, processes, history, challenges, benefits, and tools). Each question must have 4 options (A, B, C, D) and specify the correct answer. Use the following format for each question:\n\nQuestion 1: [Question text]\nA) [Option A]\nB) [Option B]\nC) [Option C]\nD) [Option D]\nCorrect Answer: [A/B/C/D]\n\nHere is the lecture content:\n\n$lecture_content";
                $messages = [
                    ['role' => 'user', 'content' => $prompt]
                ];
                $ai_response = callOpenRouterAPI($messages);
                $ai_text = $ai_response['choices'][0]['message']['content'] ?? '';

                // Log the raw API response for debugging
                file_put_contents('api_response.log', "API Response for NoteID $note_id:\n$ai_text\n\n", FILE_APPEND);

                // Parse AI response
                $questions = [];
                $lines = explode("\n", $ai_text);
                $current_question = null;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^Question \d+:/', $line)) {
                        $current_question = ['text' => trim(preg_replace('/^Question \d+:/', '', $line)), 'options' => []];
                    } elseif (preg_match('/^[A-D]\)/', $line)) {
                        $current_question['options'][substr($line, 0, 1)] = trim(substr($line, 3));
                    } elseif (preg_match('/^Correct Answer:/', $line)) {
                        $current_question['correct'] = trim(str_replace('Correct Answer:', '', $line));
                        if (!empty($current_question['text']) && count($current_question['options']) == 4 && in_array($current_question['correct'], ['A', 'B', 'C', 'D'])) {
                            $questions[] = $current_question;
                        }
                    }
                }

                // Validate AI-generated questions
                if (count($questions) < 10) {
                    $ai_error = true;
                    $warning = "AI failed to generate enough questions (only " . count($questions) . " generated). Please use manual entry to add questions.";
                } else {
                    // Insert AI-generated questions
                    foreach ($questions as $q) {
                        $stmt = $conn->prepare("INSERT INTO Question (QuizID, Question_Text) VALUES (?, ?)");
                        $stmt->bind_param("is", $quiz_id, $q['text']);
                        $stmt->execute();
                        $question_id = $conn->insert_id;
                        $stmt->close();

                        foreach ($q['options'] as $key => $opt_text) {
                            $is_correct = ($key == $q['correct']) ? 1 : 0;
                            $stmt = $conn->prepare("INSERT INTO Option (QuestionID, Option_Text, Is_Correct) VALUES (?, ?, ?)");
                            $stmt->bind_param("isi", $question_id, $opt_text, $is_correct);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }

            if (!$generate_ai || $ai_error) {
                // Manual question entry
                $questions = [];
                for ($i = 1; $i <= 10; $i++) { // Increased to 10 questions
                    if (!empty(trim($_POST["question_$i"]))) {
                        $questions[$i] = [
                            'text' => trim($_POST["question_$i"]),
                            'options' => [
                                'A' => trim($_POST["option_a_$i"]),
                                'B' => trim($_POST["option_b_$i"]),
                                'C' => trim($_POST["option_c_$i"]),
                                'D' => trim($_POST["option_d_$i"]),
                            ],
                            'correct' => $_POST["correct_$i"]
                        ];
                    }
                }

                if (empty($questions)) {
                    throw new Exception("At least one question is required.");
                }

                foreach ($questions as $q) {
                    $stmt = $conn->prepare("INSERT INTO Question (QuizID, Question_Text) VALUES (?, ?)");
                    $stmt->bind_param("is", $quiz_id, $q['text']);
                    $stmt->execute();
                    $question_id = $conn->insert_id;
                    $stmt->close();

                    foreach ($q['options'] as $key => $opt_text) {
                        $is_correct = ($key == $q['correct']) ? 1 : 0;
                        $stmt = $conn->prepare("INSERT INTO Option (QuestionID, Option_Text, Is_Correct) VALUES (?, ?, ?)");
                        $stmt->bind_param("isi", $question_id, $opt_text, $is_correct);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }

            $conn->commit();
            $success = "Quiz created successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error creating quiz: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Creator Studio - E-Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            --dark-bg: #0f0f0f;
            --card-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --input-bg: rgba(255, 255, 255, 0.08);
            --shadow-primary: 0 20px 40px rgba(0, 0, 0, 0.3);
            --shadow-secondary: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 200, 255, 0.2) 0%, transparent 50%);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .main-container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px rgba(102, 126, 234, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
            }
            to {
                text-shadow: 0 0 30px rgba(102, 126, 234, 0.8), 0 0 40px rgba(118, 75, 162, 0.5);
            }
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            font-weight: 300;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            padding: 2.5rem;
            box-shadow: var(--shadow-primary);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .glass-card:hover::before {
            left: 100%;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-primary), 0 0 50px rgba(102, 126, 234, 0.2);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            padding: 0.5rem;
            border-radius: 10px;
            background: var(--primary-gradient);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.8rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .custom-input, .custom-select {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .custom-input:focus, .custom-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.8);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: rgba(255, 255, 255, 0.1);
        }

        .custom-input::placeholder {
            color: var(--text-secondary);
        }

        .ai-toggle {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid var(--glass-border);
            margin: 2rem 0;
            transition: all 0.3s ease;
        }

        .ai-toggle:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .ai-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
            margin-right: 1rem;
        }

        .ai-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.4s;
            border-radius: 30px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background: var(--primary-gradient);
        }

        input:checked + .slider:before {
            transform: translateX(30px);
        }

        .question-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .question-card:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }

        .question-number {
            position: absolute;
            top: -10px;
            left: 20px;
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 15px;
            padding: 1.5rem;
            border: none;
            margin-bottom: 2rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: var(--success-gradient);
            color: white;
        }

        .alert-danger {
            background: var(--danger-gradient);
            color: white;
        }

        .alert-warning {
            background: var(--warning-gradient);
            color: #333;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .glass-card {
                padding: 1.5rem;
            }
            
            .option-grid {
                grid-template-columns: 1fr;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="floating-particles"></div>
    
    <div class="main-container">
        <div class="hero-section fade-in">
            <h1 class="hero-title">
                <i class="fas fa-brain"></i>
                Quiz Creator Studio
            </h1>
            <p class="hero-subtitle">Craft intelligent assessments with AI-powered question generation</p>
        </div>

        <div class="glass-card fade-in">
            <?php if (isset($error)) { echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>$error</div>"; } ?>
            <?php if (isset($warning)) { echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>$warning</div>"; } ?>
            <?php if (isset($success)) { echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>$success</div>"; } ?>
            
            <form method="POST" id="quizForm">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-clipboard-list"></i>
                        Quiz Configuration
                    </div>
                    
                    <div class="mb-4">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i>
                            Quiz Title
                        </label>
                        <input type="text" name="title" class="form-control custom-input" 
                               placeholder="Enter an engaging quiz title..." 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="note_id" class="form-label">
                            <i class="fas fa-book"></i>
                            Source Lecture Note
                        </label>
                        <select name="note_id" class="form-select custom-select" required>
                            <option value="">Choose your knowledge source...</option>
                            <?php foreach ($notes as $note) { ?>
                                <option value="<?php echo $note['NoteID']; ?>" <?php echo (isset($_POST['note_id']) && $_POST['note_id'] == $note['NoteID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($note['Title']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="ai-toggle">
                    <div class="d-flex align-items-center">
                        <label class="ai-switch">
                            <input type="checkbox" name="generate_ai" value="1" id="generate_ai" <?php echo $ai_error ? '' : 'checked'; ?>>
                            <span class="slider"></span>
                        </label>
                        <div>
                            <strong>AI-Powered Question Generation</strong>
                            <p class="mb-0 text-muted">Let our AI analyze your lecture content and create comprehensive questions automatically</p>
                        </div>
                    </div>
                </div>

                <div id="manual-questions" style="<?php echo $ai_error ? 'display: block;' : 'display: none;'; ?>">
                    <div class="section-title">
                        <i class="fas fa-edit"></i>
                        Manual Question Entry
                    </div>
                    
                    <?php for ($i = 1; $i <= 10; $i++) { ?>
                        <div class="question-card fade-in" style="animation-delay: <?php echo $i * 0.1; ?>s">
                            <div class="question-number">Question <?php echo $i; ?></div>
                            
                            <div class="mb-3 mt-4">
                                <label class="form-label">
                                    <i class="fas fa-question-circle"></i>
                                    Question Text
                                </label>
                                <input type="text" name="question_<?php echo $i; ?>" class="form-control custom-input" 
                                       placeholder="What would you like to ask about this topic?"
                                       value="<?php echo isset($_POST["question_$i"]) ? htmlspecialchars($_POST["question_$i"]) : ''; ?>">
                            </div>
                            
                            <div class="option-grid">
                                <div>
                                    <label class="form-label">
                                        <span class="badge bg-primary">A</span> Option A
                                    </label>
                                    <input type="text" name="option_a_<?php echo $i; ?>" class="form-control custom-input" 
                                           placeholder="First option..."
                                           value="<?php echo isset($_POST["option_a_$i"]) ? htmlspecialchars($_POST["option_a_$i"]) : ''; ?>">
                                </div>
                                
                                <div>
                                    <label class="form-label">
                                        <span class="badge bg-info">B</span> Option B
                                    </label>
                                    <input type="text" name="option_b_<?php echo $i; ?>" class="form-control custom-input" 
                                           placeholder="Second option..."
                                           value="<?php echo isset($_POST["option_b_$i"]) ? htmlspecialchars($_POST["option_b_$i"]) : ''; ?>">
                                </div>
                                
                                <div>
                                    <label class="form-label">
                                        <span class="badge bg-warning">C</span> Option C
                                    </label>
                                    <input type="text" name="option_c_<?php echo $i; ?>" class="form-control custom-input" 
                                           placeholder="Third option..."
                                           value="<?php echo isset($_POST["option_c_$i"]) ? htmlspecialchars($_POST["option_c_$i"]) : ''; ?>">
                                </div>
                                
                                <div>
                                    <label class="form-label">
                                        <span class="badge bg-success">D</span> Option D
                                    </label>
                                    <input type="text" name="option_d_<?php echo $i; ?>" class="form-control custom-input" 
                                           placeholder="Fourth option..."
                                           value="<?php echo isset($_POST["option_d_$i"]) ? htmlspecialchars($_POST["option_d_$i"]) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="form-label">
                                    <i class="fas fa-check-circle"></i>
                                    Correct Answer
                                </label>
                                <select name="correct_<?php echo $i; ?>" class="form-select custom-select">
                                    <option value="">Select the correct answer...</option>
                                    <option value="A" <?php echo (isset($_POST["correct_$i"]) && $_POST["correct_$i"] == 'A') ? 'selected' : ''; ?>>A</option>
                                    <option value="B" <?php echo (isset($_POST["correct_$i"]) && $_POST["correct_$i"] == 'B') ? 'selected' : ''; ?>>B</option>
                                    <option value="C" <?php echo (isset($_POST["correct_$i"]) && $_POST["correct_$i"] == 'C') ? 'selected' : ''; ?>>C</option>
                                    <option value="D" <?php echo (isset($_POST["correct_$i"]) && $_POST["correct_$i"] == 'D') ? 'selected' : ''; ?>>D</option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="d-flex gap-3 justify-content-center mt-4">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-magic me-2"></i>
                        Create Quiz
                    </button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="loading-spinner"></div>
            <p class="mt-3">Creating your quiz...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize floating particles
        function createParticles() {
            const particlesContainer = document.querySelector('.floating-particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // AI toggle functionality
        document.getElementById('generate_ai').addEventListener('change', function() {
            const manualQuestions = document.getElementById('manual-questions');
            const isChecked = this.checked;
            
            if (isChecked) {
                manualQuestions.style.display = 'none';
                manualQuestions.style.opacity = '0';
            } else {
                manualQuestions.style.display = 'block';
                setTimeout(() => {
                    manualQuestions.style.opacity = '1';
                }, 100);
            }
        });

        // Form submission with loading animation
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;
            loadingOverlay.style.display = 'flex';
            
            // Add a slight delay to show the loading state
            setTimeout(() => {
                // Form will submit normally
            }, 500);
        });

        // Smooth scroll animation for question cards
        function animateOnScroll() {
            const cards = document.querySelectorAll('.question-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });
        }

        // Enhanced input interactions
        function setupInputInteractions() {
            const inputs = document.querySelectorAll('.custom-input, .custom-select');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
                
                // Auto-expand textarea effect for text inputs
                if (input.type === 'text') {
                    input.addEventListener('input', function() {
                        if (this.value.length > 50) {
                            this.style.height = 'auto';
                            this.style.height = this.scrollHeight + 'px';
                        }
                    });
                }
            });
        }

        // Dynamic progress indicator
        function createProgressIndicator() {
            const form = document.getElementById('quizForm');
            const progressBar = document.createElement('div');
            progressBar.className = 'progress-indicator';
            progressBar.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 4px; background: rgba(255,255,255,0.1); z-index: 9999;">
                    <div id="progress-fill" style="height: 100%; width: 0%; background: var(--primary-gradient); transition: width 0.3s ease;"></div>
                </div>
            `;
            document.body.appendChild(progressBar);
            
            // Update progress based on filled inputs
            function updateProgress() {
                const requiredInputs = form.querySelectorAll('input[required], select[required]');
                const filledInputs = Array.from(requiredInputs).filter(input => input.value.trim() !== '');
                const progress = (filledInputs.length / requiredInputs.length) * 100;
                document.getElementById('progress-fill').style.width = progress + '%';
            }
            
            form.addEventListener('input', updateProgress);
            form.addEventListener('change', updateProgress);
            updateProgress();
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S to save (prevent default and show message)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const toast = document.createElement('div');
                toast.innerHTML = `
                    <div style="position: fixed; top: 20px; right: 20px; background: var(--success-gradient); 
                         color: white; padding: 1rem 2rem; border-radius: 10px; z-index: 9999;
                         animation: slideIn 0.3s ease;">
                        <i class="fas fa-save me-2"></i>Use the Create Quiz button to save your work!
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
            
            // Ctrl + Enter to submit form
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('quizForm').submit();
            }
        });

        // Add slide in animation for toast
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); }
                to { transform: translateX(0); }
            }
        `;
        document.head.appendChild(style);

        // Initialize all features when page loads
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            animateOnScroll();
            setupInputInteractions();
            createProgressIndicator();
            
            // Add entrance animation to main elements
            const elements = document.querySelectorAll('.glass-card, .hero-section');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.8s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        // Enhanced form validation with visual feedback
        function setupFormValidation() {
            const form = document.getElementById('quizForm');
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.style.borderColor = '#ff6b6b';
                        this.style.boxShadow = '0 0 0 0.2rem rgba(255, 107, 107, 0.25)';
                    } else if (this.value.trim()) {
                        this.style.borderColor = '#38ef7d';
                        this.style.boxShadow = '0 0 0 0.2rem rgba(56, 239, 125, 0.25)';
                    } else {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }
                });
            });
        }

        // Call validation setup
        document.addEventListener('DOMContentLoaded', setupFormValidation);
    </script>
</body>
</html>