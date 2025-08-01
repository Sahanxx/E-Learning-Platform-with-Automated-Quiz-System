<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$notes = null;
$error = null;
$success = null;

try {
    // Fetch all lecture notes
    $stmt = $conn->prepare("SELECT NoteID, UserID, Title, File_Path, Upload_Date FROM Lecture_Note");
    $stmt->execute();
    $notes = $stmt->get_result();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['note_id'])) {
    $note_id = (int)$_POST['note_id'];
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Fetch file path to delete the file
        $stmt_select = $conn->prepare("SELECT File_Path FROM Lecture_Note WHERE NoteID = ?");
        $stmt_select->bind_param("i", $note_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $file_path = $result->fetch_assoc()['File_Path'];
        $stmt_select->close();

        // Delete record from database
        $stmt_delete = $conn->prepare("DELETE FROM Lecture_Note WHERE NoteID = ?");
        $stmt_delete->bind_param("i", $note_id);
        if ($stmt_delete->execute()) {
            // Delete the file from the server
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $conn->commit();
            $success = "Lecture note deleted successfully.";
        } else {
            $conn->rollback();
            $error = "Error deleting note from database.";
        }
        $stmt_delete->close();

        // Refresh notes list
        $stmt = $conn->prepare("SELECT NoteID, UserID, Title, File_Path, Upload_Date FROM Lecture_Note");
        $stmt->execute();
        $notes = $stmt->get_result();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Lecture Notes - E-Learning Platform</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 50%, #ff4757 100%);
            min-height: 100vh;
            color: #2d3748;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 15s infinite ease-in-out;
        }

        .bg-shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 10%;
            animation-delay: 5s;
        }

        .bg-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 40%;
            left: 80%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 0.7; }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 10;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: slideDown 0.8s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 20px 40px rgba(255, 107, 107, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .icon-wrapper i {
            font-size: 40px;
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        h2 {
            color: white;
            font-weight: 700;
            font-size: 36px;
            margin-bottom: 12px;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            font-weight: 400;
        }

        .alert {
            padding: 20px 24px;
            border-radius: 16px;
            margin-bottom: 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease-out;
            backdrop-filter: blur(10px);
        }

        @keyframes slideIn {
            from {
                transform: translateX(-30px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.9);
            color: white;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9;
        }

        .table-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notes-count {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .notes-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }

        .note-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .note-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
        }

        .note-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .note-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .note-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .note-info {
            flex: 1;
        }

        .note-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .note-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #64748b;
        }

        .meta-item i {
            width: 16px;
            color: #94a3b8;
        }

        .note-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-delete::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-delete:hover::before {
            left: 100%;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.9);
            color: #4a5568;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 16px 32px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-top: 30px;
        }

        .btn-back:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            color: #2d3748;
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .empty-icon {
            width: 120px;
            height: 120px;
            background: #f8fafc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 3px solid #e2e8f0;
        }

        .empty-icon i {
            font-size: 48px;
            color: #cbd5e0;
        }

        .empty-title {
            font-size: 24px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 12px;
        }

        .empty-text {
            color: #718096;
            font-size: 16px;
        }

        /* Custom modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: scale(0.8);
            animation: modalScale 0.3s ease-out forwards;
        }

        @keyframes modalScale {
            to {
                transform: scale(1);
            }
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .modal-icon i {
            font-size: 32px;
            color: #dc2626;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .modal-text {
            color: #64748b;
            margin-bottom: 24px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-confirm {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-confirm:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            h2 {
                font-size: 28px;
            }
            
            .notes-grid {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                padding: 20px;
            }
            
            .note-card {
                padding: 20px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>

    <div class="container">
        <div class="header">
            <div class="icon-wrapper">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2>Delete Lecture Notes</h2>
            <p class="subtitle">Manage your lecture notes collection</p>
        </div>

        <?php if (isset($error)) { echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i>$success</div>"; } ?>

        <div class="table-container">
            <?php if ($notes && $notes->num_rows > 0) { ?>
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-file-alt"></i>
                        Lecture Notes
                    </div>
                    <div class="notes-count">
                        <?php echo $notes->num_rows; ?> notes
                    </div>
                </div>

                <div class="notes-grid">
                    <?php while ($row = $notes->fetch_assoc()) { ?>
                        <div class="note-card">
                            <div class="note-header">
                                <div class="note-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="note-info">
                                    <div class="note-title"><?php echo htmlspecialchars($row['Title']); ?></div>
                                    <div class="note-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-hashtag"></i>
                                            <span>ID: <?php echo htmlspecialchars($row['NoteID']); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <span>User ID: <?php echo htmlspecialchars($row['UserID']); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('M j, Y', strtotime($row['Upload_Date'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="note-actions">
                                <form method="POST" style="display:inline;" class="delete-form">
                                    <input type="hidden" name="note_id" value="<?php echo $row['NoteID']; ?>">
                                    <button type="button" class="btn-delete" onclick="showDeleteModal(this, '<?php echo htmlspecialchars($row['Title']); ?>')">
                                        <i class="fas fa-trash"></i>
                                        Delete Note
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="empty-title">No Lecture Notes Found</div>
                    <div class="empty-text">There are currently no lecture notes to manage.</div>
                </div>
            <?php } ?>
        </div>

        <a href="admin_dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="modal-title">Delete Lecture Note</div>
            <div class="modal-text" id="modalText">
                Are you sure you want to delete this lecture note? This action cannot be undone.
            </div>
            <div class="modal-actions">
                <button class="btn-confirm" onclick="confirmDelete()">Delete</button>
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let currentForm = null;

        function showDeleteModal(button, title) {
            currentForm = button.closest('form');
            const modal = document.getElementById('deleteModal');
            const modalText = document.getElementById('modalText');
            
            modalText.innerHTML = `Are you sure you want to delete "<strong>${title}</strong>"? This action cannot be undone.`;
            modal.style.display = 'flex';
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'none';
            currentForm = null;
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }

        function confirmDelete() {
            if (currentForm) {
                // Add loading state
                const confirmBtn = document.querySelector('.btn-confirm');
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                confirmBtn.disabled = true;
                
                // Submit the form
                currentForm.submit();
            }
            closeModal();
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Add stagger animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.note-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeInUp 0.6s ease-out forwards';
            });
        });

        // Add hover sound effect (optional)
        document.querySelectorAll('.note-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
<?php
if (isset($stmt) && $stmt) $stmt->close();
if (isset($conn)) $conn->close();
?>