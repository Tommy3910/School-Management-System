<?php
require_once 'config.php';

if (empty($_SESSION['user'])) {
    redirect('login.php');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Material not found.');
}

$stmt = $conn->prepare("SELECT * FROM course_materials WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();
$stmt->close();

if (!$material) {
    http_response_code(404);
    exit('Material not found.');
}

$filePath = __DIR__ . '/uploads/materials/' . $material['filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    exit('File missing on server.');
}

$mimeTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];
$ext = strtolower(pathinfo($material['filename'], PATHINFO_EXTENSION));
$mime = $mimeTypes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($material['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;