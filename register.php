<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!in_array($role, ['admin', 'student'])) {
        $error = "Invalid role selected.";
    } else {
        $stmt = $conn->prepare("SELECT UserID FROM User WHERE Username = ? OR Email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "Username or Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO User (Username, Email, Password, Role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Learning Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 75%, #f5576c 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .particle:nth-child(1) { 
            width: 120px; height: 120px; 
            left: 5%; top: 20%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
            animation-delay: 0s; 
        }
        .particle:nth-child(2) { 
            width: 80px; height: 80px; 
            left: 85%; top: 10%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.03) 100%);
            animation-delay: 2s; 
        }
        .particle:nth-child(3) { 
            width: 60px; height: 60px; 
            left: 70%; top: 70%;
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0.06) 100%);
            animation-delay: 4s; 
        }
        .particle:nth-child(4) { 
            width: 100px; height: 100px; 
            left: 15%; top: 80%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.04) 100%);
            animation-delay: 6s; 
        }
        .particle:nth-child(5) { 
            width: 140px; height: 140px; 
            left: 50%; top: 60%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%);
            animation-delay: 1s; 
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            25% { transform: translateY(-30px) rotate(90deg); opacity: 1; }
            50% { transform: translateY(-60px) rotate(180deg); opacity: 0.8; }
            75% { transform: translateY(-30px) rotate(270deg); opacity: 1; }
        }

        .register-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 28px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            animation: slideInScale 1s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            border-radius: 28px 28px 0 0;
        }

        .register-container::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(102, 126, 234, 0.1), transparent, rgba(245, 87, 108, 0.1), transparent);
            animation: rotate 20s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: translateY(80px) scale(0.8) rotateX(20deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1) rotateX(0deg);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            border-radius: 24px;
            margin-bottom: 20px;
            animation: morphing 3s ease-in-out infinite;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        @keyframes morphing {
            0%, 100% { border-radius: 24px; transform: rotate(0deg) scale(1); }
            25% { border-radius: 50% 24px 50% 24px; transform: rotate(90deg) scale(1.05); }
            50% { border-radius: 50%; transform: rotate(180deg) scale(1); }
            75% { border-radius: 24px 50% 24px 50%; transform: rotate(270deg) scale(1.05); }
        }

        .logo-container i {
            color: white;
            font-size: 36px;
            z-index: 2;
        }

        .platform-title {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .register-subtitle {
            font-size: 16px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .progress-container {
            width: 100%;
            height: 6px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            width: 0%;
            transition: width 0.3s ease;
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 6px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px) scale(1.02);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .form-group:focus-within .input-icon {
            color: #667eea;
            transform: translateY(-50%) scale(1.1);
        }

        .form-group:focus-within .form-label {
            color: #667eea;
            transform: translateY(-2px);
        }

        .password-strength {
            margin-top: 8px;
            display: flex;
            gap: 4px;
        }

        .strength-bar {
            height: 4px;
            flex: 1;
            background: #e5e7eb;
            border-radius: 2px;
            transition: background-color 0.3s ease;
        }

        .strength-bar.active {
            background: #10b981;
        }

        .strength-bar.medium {
            background: #f59e0b;
        }

        .strength-bar.weak {
            background: #ef4444;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 24px 0;
        }

        .custom-checkbox {
            position: relative;
            width: 24px;
            height: 24px;
            margin-top: 2px;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 24px;
            width: 24px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .custom-checkbox:hover .checkmark {
            border-color: #667eea;
        }

        .custom-checkbox input:checked ~ .checkmark {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 7px;
            top: 3px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-label {
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
        }

        .checkbox-label a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .checkbox-label a:hover {
            text-decoration: underline;
        }

        .register-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .register-btn:active {
            transform: translateY(-1px) scale(1.01);
        }

        .loading-spinner {
            display: none;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .register-btn.loading .loading-spinner {
            display: inline-block;
        }

        .register-btn.loading .btn-text {
            opacity: 0.8;
        }

        .register-footer {
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .alert {
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 12px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInAlert 0.5s ease-out;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }

        @keyframes slideInAlert {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 640px) {
            .register-container {
                padding: 24px;
                margin: 16px;
                border-radius: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .platform-title {
                font-size: 28px;
            }
            
            .logo-container {
                width: 70px;
                height: 70px;
            }
            
            .logo-container i {
                font-size: 28px;
            }
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 8px;
        }

        .role-option {
            position: relative;
            cursor: pointer;
        }

        .role-option input {
            position: absolute;
            opacity: 0;
        }

        .role-card {
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .role-option input:checked + .role-card {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transform: scale(1.02);
        }

        .role-icon {
            font-size: 24px;
            margin-bottom: 8px;
            color: #667eea;
        }

        .role-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .role-desc {
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="register-wrapper">
        <div class="register-container">
            <div class="register-header">
                <div class="logo-container">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="platform-title">✦ EduLearn</h1>
                <p class="register-subtitle">Create your account to start learning</p>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div id="error-alert" class="alert alert-danger" style="display: flex;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="error-message"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div id="success-alert" class="alert alert-success" style="display: flex;">
                    <i class="fas fa-check-circle"></i>
                    <span id="success-message"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div style="position: relative;">
                                <i class="fas fa-user input-icon"></i>
                                <input 
                                    type="text" 
                                    name="username" 
                                    id="username" 
                                    class="form-input" 
                                    placeholder="Choose a username"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div style="position: relative;">
                                <i class="fas fa-envelope input-icon"></i>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    class="form-input" 
                                    placeholder="Enter your email"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div style="position: relative;">
                                <i class="fas fa-lock input-icon"></i>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    class="form-input" 
                                    placeholder="Create password"
                                    required
                                >
                            </div>
                            <div class="password-strength" id="passwordStrength" style="display: none;">
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div style="position: relative;">
                                <i class="fas fa-shield-alt input-icon"></i>
                                <input 
                                    type="password" 
                                    name="confirm_password" 
                                    id="confirm_password" 
                                    class="form-input" 
                                    placeholder="Confirm password"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Select Your Role</label>
                        <div class="role-options">
                            <label class="role-option">
                                <input type="radio" name="role" value="student" checked>
                                <div class="role-card">
                                    <div class="role-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="role-title">Student</div>
                                    <div class="role-desc">Access courses and learn</div>
                                </div>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="role" value="admin">
                                <div class="role-card">
                                    <div class="role-icon">
                                        <i class="fas fa-user-cog"></i>
                                    </div>
                                    <div class="role-title">Admin</div>
                                    <div class="role-desc">Manage platform content</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="checkmark"></span>
                    </label>
                    <label for="terms" class="checkbox-label">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="register-btn" id="registerBtn">
                    <div class="loading-spinner"></div>
                    <span class="btn-text">Create Account</span>
                </button>
            </form>

            <div class="register-footer">
                Already have an account? 
                <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const progressBar = document.getElementById('progressBar');
        const inputs = form.querySelectorAll('input[required]');
        
        function updateProgress() {
            const filledInputs = Array.from(inputs).filter(input => {
                if (input.type === 'checkbox') return input.checked;
                return input.value.trim() !== '';
            });
            
            const progress = (filledInputs.length / inputs.length) * 100;
            progressBar.style.width = progress + '%';
        }

        inputs.forEach(input => {
            input.addEventListener('input', updateProgress);
            input.addEventListener('change', updateProgress);
        });

        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('passwordStrength');
        const strengthBars = strengthIndicator.querySelectorAll('.strength-bar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            if (password.length > 0) {
                strengthIndicator.style.display = 'flex';
                updateStrengthBars(strength);
            } else {
                strengthIndicator.style.display = 'none';
            }
        });

        function calculatePasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            return Math.min(score, 4);
        }

        function updateStrengthBars(strength) {
            strengthBars.forEach((bar, index) => {
                bar.classList.remove('active', 'weak', 'medium');
                if (index < strength) {
                    bar.classList.add('active');
                    if (strength <= 2) bar.classList.add('weak');
                    else if (strength === 3) bar.classList.add('medium');
                }
            });
        }

        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            hideAlerts();
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showError('Passwords do not match');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showError('Password must be at least 6 characters long');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                showError('Please accept the Terms and Conditions');
                return;
            }

            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading');
        });

        const formInputs = document.querySelectorAll('.form-input');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        function showError(message) {
            const alert = document.getElementById('error-alert');
            const messageSpan = document.getElementById('error-message');
            messageSpan.textContent = message;
            alert.style.display = 'flex';
            
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            const alert = document.getElementById('success-alert');
            const messageSpan = document.getElementById('success-message');
            messageSpan.innerHTML = message;
            alert.style.display = 'flex';
        }

        function hideAlerts() {
            const errorAlert = document.getElementById('error-alert');
            const successAlert = document.getElementById('success-alert');
            if (errorAlert) errorAlert.style.display = 'none';
            if (successAlert) successAlert.style.display = 'none';
        }

        updateProgress();

        <?php if (isset($success)): ?>
            document.getElementById('registerBtn').classList.remove('loading');
            form.reset();
            updateProgress();
        <?php endif; ?>
    </script>
</body>
</html>