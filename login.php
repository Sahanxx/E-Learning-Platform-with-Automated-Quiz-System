<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username and Password are required.";
    } else {
        $stmt = $conn->prepare("SELECT UserID, Password, Role FROM User WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            if ($user['Role'] == 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: student_dashboard.php');
            }
            exit();
        } else {
            $error = "Invalid Username or Password.";
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
    <title>Login - E-Learning Platform</title>
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

        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
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

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            border-radius: 28px 28px 0 0;
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

        .login-header {
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

        .login-title {
            font-size: 16px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group {
            position: relative;
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-input:focus {
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

        .login-btn {
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

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px) scale(1.01);
        }

        .loading {
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

        .login-btn.loading .loading {
            display: inline-block;
        }

        .login-btn.loading .btn-text {
            opacity: 0.8;
        }

        .login-footer {
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
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
            .login-container {
                padding: 24px;
                margin: 16px;
                border-radius: 20px;
            }
            .platform-title { font-size: 28px; }
            .logo-container { width: 70px; height: 70px; }
            .logo-container i { font-size: 28px; }
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

    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo-container">
                    <i class="fas fa-book-open"></i>
                </div>
                <h1 class="platform-title">✦ EduLearn</h1>
                <p class="login-title">Welcome back! Please sign in</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" style="display: flex;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div style="position: relative;">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            class="form-input" 
                            placeholder="Enter your username"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <div class="loading"></div>
                    <span class="btn-text">Sign In</span>
                </button>
            </form>

            <div class="login-footer">
                Don't have an account? 
                <a href="register.php">Create one here</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            setTimeout(() => {
                btn.classList.remove('loading');
            }, 2000);
        });

        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>