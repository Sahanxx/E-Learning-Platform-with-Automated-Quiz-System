<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

// Initialize conversation array to store chat history for the session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

$response = '';
$confidence_score = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = trim(strtolower($_POST['query']));
    if (empty($query)) {
        $error = "Please enter a query.";
    } else {
        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful assistant for an e-learning platform. Assist students with queries about quizzes, lecture notes, and general study tips.'],
                ['role' => 'user', 'content' => $query]
            ];
            $ai_response = callOpenRouterAPI($messages);

            // Custom response for specific keywords
            if (strpos($query, 'css') !== false || strpos($query, 'what is css') !== false) {
                $response = "- **Definition**: CSS (Cascading Style Sheets) is a language to style HTML or XML documents, controlling layout and appearance.\n" .
                            "- **Key Features**:\n" .
                            "  1. Separates content (HTML) from design.\n" .
                            "  2. Styles elements (colors, fonts, etc.).\n" .
                            "  3. Enables responsive design with media queries.\n" .
                            "  4. Allows reusable styles across pages.\n" .
                            "  5. Uses cascading rules for style priority.\n" .
                            "- **Basic Syntax**: `selector { property: value; }` (e.g., `h1 { color: blue; }`).\n" .
                            "- **Benefits**: Enhances aesthetics, saves time, ensures responsiveness.";
            } elseif (strpos($query, 'quiz') !== false) {
                $response = "Quizzes help you test your knowledge! Visit the 'Take Quiz' section on your dashboard to start one.";
            } else {
                $response = $ai_response['choices'][0]['message']['content'] ?? "Sorry, I couldn't process your request.";
            }

            $confidence_score = $ai_response['choices'][0]['logprobs'] ?? null;

            // Log the interaction in the database
            $stmt = $conn->prepare("INSERT INTO Chatbot_Log (UserID, Query, Response, Confidence_Score) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $_SESSION['user_id'], $query, $response, $confidence_score);
            $stmt->execute();
            $stmt->close();

            // Add to session chat history with timestamp
            $_SESSION['chat_history'][] = [
                'user' => $query,
                'bot' => $response,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $error = "Error processing your query: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Assistant - EduLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            --warning-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            --dark-gradient: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-muted: rgba(255, 255, 255, 0.5);
            --shadow-soft: 0 8px 32px rgba(31, 38, 135, 0.2);
            --shadow-medium: 0 20px 60px rgba(31, 38, 135, 0.3);
            --shadow-strong: 0 30px 80px rgba(31, 38, 135, 0.4);
            --ai-glow: 0 0 30px rgba(79, 172, 254, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-gradient);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
        }

        /* Futuristic Background */
        .cyber-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: 
                radial-gradient(circle at 20% 20%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(240, 147, 251, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(79, 172, 254, 0.1) 0%, transparent 50%);
        }

        /* Animated Grid */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.03;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Floating Particles */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(79, 172, 254, 0.6);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite;
        }

        .particle:nth-child(odd) {
            background: rgba(240, 147, 251, 0.6);
            animation-duration: 20s;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Navigation */
        .navbar {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--accent-gradient);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover::before {
            left: 0;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        /* Main Container */
        .main-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .ai-assistant-wrapper {
            width: 100%;
            max-width: 1000px;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            box-shadow: var(--shadow-strong);
            position: relative;
            overflow: hidden;
        }

        .ai-assistant-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gradient);
            border-radius: 30px 30px 0 0;
        }

        /* Header Section */
        .ai-header {
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
            position: relative;
            background: rgba(0, 0, 0, 0.1);
        }

        .ai-avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .ai-avatar {
            width: 100px;
            height: 100px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: var(--ai-glow);
            position: relative;
            animation: aiPulse 3s ease-in-out infinite;
        }

        @keyframes aiPulse {
            0%, 100% {
                box-shadow: var(--ai-glow);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 50px rgba(79, 172, 254, 0.5);
                transform: scale(1.05);
            }
        }

        .ai-avatar::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid transparent;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent, rgba(79, 172, 254, 0.5), transparent);
            animation: rotate 4s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .ai-status {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            background: var(--success-gradient);
            border-radius: 50%;
            border: 3px solid var(--text-primary);
            animation: statusBlink 2s ease-in-out infinite;
        }

        @keyframes statusBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .ai-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .ai-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 1rem;
        }

        .ai-capabilities {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .capability-tag {
            background: rgba(79, 172, 254, 0.1);
            border: 1px solid rgba(79, 172, 254, 0.3);
            color: #4facfe;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Chat Container */
        .chat-container {
            height: 500px;
            display: flex;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.2);
            margin: 0 2rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            position: relative;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(79, 172, 254, 0.3) transparent;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(79, 172, 254, 0.3);
            border-radius: 3px;
        }

        .message {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: messageSlide 0.5s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            position: relative;
        }

        .message.user .message-avatar {
            background: var(--secondary-gradient);
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        }

        .message.bot .message-avatar {
            background: var(--accent-gradient);
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }

        .message.bot .message-avatar::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 1px solid rgba(79, 172, 254, 0.5);
            border-radius: 50%;
            animation: avatarGlow 2s ease-in-out infinite;
        }

        @keyframes avatarGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .message-content {
            max-width: 75%;
            position: relative;
        }

        .message-bubble {
            padding: 1.2rem 1.5rem;
            border-radius: 20px;
            line-height: 1.6;
            word-wrap: break-word;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .message.user .message-bubble {
            background: var(--secondary-gradient);
            border-bottom-right-radius: 8px;
            box-shadow: 0 4px 20px rgba(240, 147, 251, 0.2);
        }

        .message.bot .message-bubble {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-bottom-left-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .message.user .message-time {
            justify-content: flex-end;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        .typing-dots {
            display: flex;
            gap: 0.2rem;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background: var(--accent-gradient);
            border-radius: 50%;
            animation: typingBounce 1.4s ease-in-out infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-secondary);
            padding: 2rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0.7;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .empty-state p {
            font-size: 1rem;
            max-width: 300px;
        }

        .suggested-questions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .suggested-question {
            background: rgba(79, 172, 254, 0.1);
            border: 1px solid rgba(79, 172, 254, 0.3);
            color: #4facfe;
            padding: 0.6rem 1rem;
            border-radius: 15px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .suggested-question:hover {
            background: rgba(79, 172, 254, 0.2);
            transform: translateY(-2px);
        }

        /* Input Area */
        .chat-input-area {
            padding: 1.5rem 2rem 2rem;
            background: rgba(0, 0, 0, 0.1);
            border-top: 1px solid var(--glass-border);
        }

        .input-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            position: relative;
        }

        .form-control {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 1rem 1.5rem;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            flex: 1;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
            color: var(--text-primary);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .btn-send {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: var(--accent-gradient);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-send::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-send:hover::before {
            left: 100%;
        }

        .btn-send:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 25px rgba(79, 172, 254, 0.4);
        }

        .btn-send:active {
            transform: translateY(-1px) scale(0.98);
        }

        /* Back Button */
        .back-button-container {
            padding: 0 2rem 1.5rem;
        }

        .btn-back {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            border-radius: 25px;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }

        /* Alert Styles */
        .alert {
            background: rgba(245, 87, 108, 0.1);
            border: 1px solid rgba(245, 87, 108, 0.3);
            border-radius: 15px;
            color: #ff6b8a;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .ai-header {
                padding: 2rem 1.5rem 1rem;
            }

            .ai-title {
                font-size: 2rem;
            }

            .chat-container {
                margin: 0 1rem;
                height: 400px;
            }

            .chat-input-area {
                padding: 1rem 1.5rem;
            }

            .message-content {
                max-width: 85%;
            }

            .ai-capabilities {
                gap: 0.5rem;
            }

            .capability-tag {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }
        }

        /* Loading States */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 20px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(79, 172, 254, 0.3);
            border-top: 3px solid #4facfe;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Cyber Background -->
    <div class="cyber-bg"></div>
    <div class="grid-overlay"></div>
    
    <!-- Particles -->
    <div class="particles-container" id="particles"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-robot"></i>
                EduLearn AI
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="student_dashboard.php">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="main-container">
        <div class="ai-assistant-wrapper">
            <!-- AI Header -->
            <div class="ai-header">
                <div class="ai-avatar-container">
                    <div class="ai-avatar">
                        <i class="fas fa-brain"></i>
                        <div class="ai-status"></div>
                    </div>
                </div>
                <h1 class="ai-title">AI Learning Assistant</h1>
                <p class="ai-subtitle">Your intelligent companion for academic success</p>
                <div class="ai-capabilities">
                    <span class="capability-tag">Quiz Help</span>
                    <span class="capability-tag">Study Tips</span>
                    <span class="capability-tag">Course Content</span>
                    <span class="capability-tag">24/7 Support</span>
                </div>
            </div>

            <!-- Error Alert -->
            <?php if (isset($error)): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Chat Container -->
            <div class="chat-container">
                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($_SESSION['chat_history'])): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>Start Your Learning Journey</h3>
                            <p>Ask me anything about your studies, quizzes, or course materials!</p>
                            <div class="suggested-questions">
                                <div class="suggested-question" onclick="fillQuery('What is CSS?')">
                                    What is CSS?
                                </div>
                                <div class="suggested-question" onclick="fillQuery('How do I prepare for quizzes?')">
                                    How do I prepare for quizzes?
                                </div>
                                <div class="suggested-question" onclick="fillQuery('Show me study tips')">
                                    Show me study tips
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($_SESSION['chat_history'] as $chat): ?>
                            <!-- User Message -->
                            <div class="message user">
                                <div class="message-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo htmlspecialchars($chat['user']); ?>
                                    </div>
                                    <div class="message-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('g:i A', strtotime($chat['timestamp'])); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Bot Message -->
                            <div class="message bot">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($chat['bot'])); ?>
                                    </div>
                                    <div class="message-time">
                                        <i class="fas fa-brain"></i>
                                        AI Assistant
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Input Area -->
                <div class="chat-input-area">
                    <form method="POST" id="chatForm">
                        <div class="input-container">
                            <input 
                                type="text" 
                                name="query" 
                                id="queryInput"
                                class="form-control" 
                                placeholder="Ask me anything about your studies..."
                                required
                                autocomplete="off"
                            >
                            <button type="submit" class="btn-send" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Back Button -->
            <div class="back-button-container">
                <a href="student_dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create particles
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 30;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
                container.appendChild(particle);
            }
        }

        // Auto-scroll to bottom of chat
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Fill query input with suggested question
        function fillQuery(question) {
            document.getElementById('queryInput').value = question;
            document.getElementById('queryInput').focus();
        }

        // Handle form submission with loading state
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            const sendBtn = document.getElementById('sendBtn');
            const queryInput = document.getElementById('queryInput');
            
            if (queryInput.value.trim() === '') {
                e.preventDefault();
                return;
            }

            // Show loading state
            sendBtn.innerHTML = '<div class="loading-spinner"></div>';
            sendBtn.disabled = true;
            
            // Add typing indicator
            const chatMessages = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <div class="typing-indicator">
                            <span>AI is thinking</span>
                            <div class="typing-dots">
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            scrollToBottom();
        });

        // Handle Enter key in input
        document.getElementById('queryInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').submit();
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            scrollToBottom();
            
            // Focus on input
            document.getElementById('queryInput').focus();
            
            // Remove typing indicator if it exists (after page reload)
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        });

        // Add some interactive effects
        document.querySelectorAll('.message-bubble').forEach(bubble => {
            bubble.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            bubble.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Animate messages on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const messageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.message').forEach(message => {
            messageObserver.observe(message);
        });
    </script>
</body>
</html>

