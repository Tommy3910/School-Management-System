<?php
require_once __DIR__ . '/config.php';
requireAuth('student');

$userId = (int) ($_SESSION['user']['id'] ?? 0);
$hwId = (int) ($_POST['hw_id'] ?? 0);
$notes = trim($_POST['submission_notes'] ?? '');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1');

function respond($success, $message, $redirectTab = 'homework') {
    global $isAjax;
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    } else {
        flash('toast', $message);
        redirect("studentHomePage.php?tab=" . $redirectTab);
    }
}

if ($hwId <= 0) {
    respond(false, 'Invalid homework assignment selected.');
}

// Verify this homework belongs to student
$stmt = $conn->prepare("SELECT id, title FROM homework WHERE id = ? AND student_id = ?");
$stmt->bind_param('ii', $hwId, $userId);
$stmt->execute();
$res = $stmt->get_result();
$hwRow = $res->fetch_assoc();
$stmt->close();

if (!$hwRow) {
    respond(false, 'Homework assignment not found.');
}

if (empty($_FILES['homework_file']['name']) || $_FILES['homework_file']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Please select a valid file to upload.');
}

$origName = $_FILES['homework_file']['name'];
$fileSize = $_FILES['homework_file']['size'];
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

$allowedExt = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'png', 'jpg', 'jpeg'];
if (!in_array($ext, $allowedExt, true)) {
    respond(false, 'File type not allowed. Allowed types: PDF, Word, PowerPoint, Excel, Images, Text, ZIP.');
}

if ($fileSize > 20 * 1024 * 1024) {
    respond(false, 'File size exceeds maximum allowed size (20MB).');
}

$uploadDir = __DIR__ . '/uploads/homework/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename preserving original extension
$safeName = 'hw_' . $hwId . '_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
$destPath = $uploadDir . $safeName;

if (!move_uploaded_file($_FILES['homework_file']['tmp_name'], $destPath)) {
    respond(false, 'Failed to save uploaded file. Please try again.');
}

// Update DB
$stmt = $conn->prepare("UPDATE homework SET status = 'submitted', submission_file = ?, submission_notes = ?, submitted_at = NOW() WHERE id = ? AND student_id = ?");
$stmt->bind_param('ssii', $safeName, $notes, $hwId, $userId);
$stmt->execute();
$stmt->close();

// Send in-app notification
sendNotification($conn, $userId, 'Assignment Submitted', 'You have successfully submitted your work for "' . $hwRow['title'] . '".');

respond(true, 'Homework "' . $hwRow['title'] . '" submitted successfully!');
