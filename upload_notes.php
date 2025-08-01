<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $file = $_FILES['file'];

    if (empty($title) || empty($file['name'])) {
        $error = "Title and file are required.";
    } elseif ($file['type'] != 'application/pdf') {
        $error = "Only PDF files are allowed.";
    } else {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_path = $target_dir . uniqid() . "_" . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO Lecture_Note (UserID, Title, File_Path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $title, $file_path);
            if ($stmt->execute()) {
                $success = "Lecture note uploaded successfully.";
            } else {
                $error = "Error uploading note: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Error uploading file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Lecture Notes - E-Learning Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background particles */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.2; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 0.8; }
        }

        .container {
            max-width: 500px;
            width: 90%;
            position: relative;
            z-index: 10;
        }

        .upload-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.8s ease-out forwards;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .icon-wrapper i {
            font-size: 32px;
            color: white;
        }

        h2 {
            color: #2d3748;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718096;
            font-size: 16px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            color: #2d3748;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f7fafc;
        }

        .file-input-wrapper:hover {
            border-color: #667eea;
            background: #eef2ff;
            transform: translateY(-2px);
        }

        .file-input-wrapper.dragover {
            border-color: #667eea;
            background: #eef2ff;
            transform: scale(1.02);
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-content {
            pointer-events: none;
        }

        .file-icon {
            font-size: 48px;
            color: #a0aec0;
            margin-bottom: 16px;
        }

        .file-text {
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .file-subtext {
            color: #718096;
            font-size: 14px;
        }

        .file-selected {
            background: #f0fff4;
            border-color: #38a169;
        }

        .file-selected .file-icon {
            color: #38a169;
        }

        .btn-group {
            display: flex;
            gap: 16px;
            margin-top: 32px;
        }

        .btn {
            flex: 1;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-danger {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .alert-success {
            background: #c6f6d5;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 16px;
            overflow: hidden;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            transition: width 0.3s ease;
            width: 0%;
        }

        @media (max-width: 576px) {
            .upload-card {
                padding: 24px;
                margin: 20px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .icon-wrapper {
                width: 60px;
                height: 60px;
            }
            
            .icon-wrapper i {
                font-size: 24px;
            }
        }

        /* Loading animation */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
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
    <!-- Animated background particles -->
    <script>
        for(let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 8 + 's';
            particle.style.animationDuration = (Math.random() * 4 + 6) + 's';
            document.body.appendChild(particle);
        }
    </script>

    <div class="container">
        <div class="upload-card">
            <div class="header">
                <div class="icon-wrapper">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h2>Upload Lecture Notes</h2>
                <p class="subtitle">Share knowledge with your students</p>
            </div>

            <?php if (isset($error)) { echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i>$error</div>"; } ?>
            <?php if (isset($success)) { echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i>$success</div>"; } ?>

            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="fas fa-heading"></i> Lecture Title
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title"
                           class="form-control" 
                           placeholder="Enter an engaging title for your lecture"
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-file-pdf"></i> PDF Document
                    </label>
                    <div class="file-input-wrapper" id="fileWrapper">
                        <input type="file" 
                               name="file" 
                               id="file"
                               class="file-input" 
                               accept="application/pdf" 
                               required>
                        <div class="file-input-content" id="fileContent">
                            <div class="file-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-text">Drag & drop your PDF here</div>
                            <div class="file-subtext">or click to browse files</div>
                        </div>
                    </div>
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload"></i>
                        Upload Lecture
                    </button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // File input enhancement
        const fileInput = document.getElementById('file');
        const fileWrapper = document.getElementById('fileWrapper');
        const fileContent = document.getElementById('fileContent');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');

        // Drag and drop functionality
        fileWrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileWrapper.classList.add('dragover');
        });

        fileWrapper.addEventListener('dragleave', () => {
            fileWrapper.classList.remove('dragover');
        });

        fileWrapper.addEventListener('drop', (e) => {
            e.preventDefault();
            fileWrapper.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                fileInput.files = files;
                updateFileDisplay(files[0]);
            }
        });

        // File selection handler
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileDisplay(e.target.files[0]);
            }
        });

        function updateFileDisplay(file) {
            fileWrapper.classList.add('file-selected');
            fileContent.innerHTML = `
                <div class="file-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="file-text">${file.name}</div>
                <div class="file-subtext">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
            `;
        }

        // Form submission with loading animation
        uploadForm.addEventListener('submit', (e) => {
            uploadBtn.classList.add('loading');
            uploadBtn.innerHTML = '<span style="opacity: 0;">Uploading...</span>';
            progressBar.style.display = 'block';
            
            // Simulate progress (replace with actual upload progress if needed)
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressFill.style.width = progress + '%';
                
                if (progress >= 90) {
                    clearInterval(interval);
                }
            }, 200);
        });

        // Input animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>