<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user'])) {
    die("Access denied. Please log in.");
}

$hwId = (int) ($_GET['id'] ?? 0);
if ($hwId <= 0) {
    die("Invalid request.");
}

$stmt = $conn->prepare("SELECT id, student_id, title, submission_file FROM homework WHERE id = ?");
$stmt->bind_param('i', $hwId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || empty($row['submission_file'])) {
    die("No submission file found.");
}

// Security: If logged in as student, make sure it's their own submission
$user = $_SESSION['user'];
if ($user['role'] === 'student' && (int)$user['id'] !== (int)$row['student_id']) {
    die("Access denied. You can only download your own submission.");
}

$filepath = __DIR__ . '/uploads/homework/' . $row['submission_file'];
if (!file_exists($filepath)) {
    die("File not found on server.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filepath);
finfo_close($finfo);

header('Content-Description: File Transfer');
header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($row['submission_file']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
