<?php
require_once 'config.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required.',
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, useremail, year, PASSWORD, role FROM users WHERE useremail = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        'success' => false,
        'message' => 'No account found with that email.',
    ]);
    exit;
}

$row = $result->fetch_assoc();

if ($password !== $row['PASSWORD']) {
    echo json_encode([
        'success' => false,
        'message' => 'Incorrect password.',
    ]);
    exit;
}

$_SESSION['user'] = [
    'id' => (int) $row['id'],
    'role' => $row['role'],
    'name' => $row['name'],
    'email' => $row['useremail'],
    'year' => (int) $row['year'],
];

echo json_encode([
    'success' => true,
    'message' => 'Login successful.',
    'user' => [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['useremail'],
        'role' => $row['role'],
        'year' => (int) $row['year'],
    ],
]);
