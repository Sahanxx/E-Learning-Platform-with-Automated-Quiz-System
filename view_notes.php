<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

$stmt = $conn->prepare("SELECT NoteID, Title, File_Path, Upload_Date FROM Lecture_Note");
$stmt->execute();
$result = $stmt->get_result();
$notes = [];
while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Lecture Notes - E-Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Lecture Notes</h2>
        <?php if (empty($notes)) { ?>
            <div class="alert alert-info">No lecture notes available.</div>
        <?php } else { ?>
            <ul class="list-group">
                <?php foreach ($notes as $note) { ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($note['Title']); ?></strong>
                            <small class="text-muted"> (Uploaded: <?php echo $note['Upload_Date']; ?>)</small>
                        </div>
                        <a href="<?php echo htmlspecialchars($note['File_Path']); ?>" download class="btn btn-primary btn-sm">Download</a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <a href="student_dashboard.php" class="btn btn-secondary mt-3">Back</a>
    </div>
</body>
</html>