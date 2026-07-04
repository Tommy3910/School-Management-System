<?php
// api_manage_homework.php
// JSON endpoint for the Flutter teacher app — mirrors the "Homework
// Oversight" tab in teacherHomePage.php (?tab=homework):
//   - list all homework across students, plus the student roster
//     (for the "Assign To" dropdown)
//   - assign new homework to a single student or all students,
//     sending a notification exactly like the add_homework POST
//     handler in teacherHomePage.php
//   - update a homework row's status (approve / mark missing)
//
// Usage:
//   GET  ?action=list
//   POST action=add            hw_student ('all' or id), hw_title, hw_type, hw_due, created_by
//   POST action=update_status  hw_id, status ('submitted' | 'missing')

require_once 'config.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? 'list';

// ── LIST: all homework + student roster ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $homework = [];
    $hwRes = $conn->query(
        "SELECT h.*, u.name AS student_name, u.year AS student_year
         FROM homework h JOIN users u ON h.student_id = u.id
         ORDER BY h.due_date ASC"
    );
    if ($hwRes) {
        while ($r = $hwRes->fetch_assoc()) {
            $homework[] = [
                'id'           => (int) $r['id'],
                'studentId'    => (int) $r['student_id'],
                'studentName'  => $r['student_name'],
                'studentYear'  => (int) $r['student_year'],
                'title'        => $r['title'],
                'type'         => $r['type'],
                'due'          => date('d M Y', strtotime($r['due_date'])),
                'status'       => $r['status'],
            ];
        }
    }

    $students = [];
    $stuRes = $conn->query("SELECT id, name, year FROM users WHERE role='student' ORDER BY name");
    if ($stuRes) {
        while ($r = $stuRes->fetch_assoc()) {
            $students[] = [
                'id'   => (int) $r['id'],
                'name' => $r['name'],
                'year' => (int) $r['year'],
            ];
        }
    }

    echo json_encode([
        'homework' => $homework,
        'students' => $students,
    ]);
    exit;
}

// ── ADD: assign new homework (single student or all students) ────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $target    = $_POST['hw_student'] ?? ''; // 'all' or a specific student id
    $title     = trim($_POST['hw_title'] ?? '');
    $type      = $_POST['hw_type'] ?? 'Homework';
    $due       = $_POST['hw_due'] ?? '';
    $createdBy = (int) ($_POST['created_by'] ?? 0);

    if ($title === '' || $due === '' || $createdBy <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please fill in the assignment title and due date.']);
        exit;
    }

    $titleEsc = $conn->real_escape_string($title);
    $typeEsc  = $conn->real_escape_string($type);
    $dueEsc   = $conn->real_escape_string($due);

    if ($target === 'all') {
        $res = $conn->query("SELECT id FROM users WHERE role='student'");
        $count = 0;
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $sid = (int) $r['id'];
                $conn->query("INSERT INTO homework (student_id, title, type, due_date, status, created_by)
                              VALUES ($sid, '$titleEsc', '$typeEsc', '$dueEsc', 'missing', $createdBy)");
                sendNotification($conn, $sid, 'New ' . $type, "A new {$type} \"{$title}\" is due on " . date('d M Y', strtotime($due)) . ".");
                $count++;
            }
        }
        echo json_encode(['success' => true, 'message' => "Assigned \"$title\" to $count students."]);
        exit;
    } else {
        $sid = (int) $target;
        if ($sid > 0) {
            $conn->query("INSERT INTO homework (student_id, title, type, due_date, status, created_by)
                          VALUES ($sid, '$titleEsc', '$typeEsc', '$dueEsc', 'missing', $createdBy)");
            sendNotification($conn, $sid, 'New ' . $type, "A new {$type} \"{$title}\" is due on " . date('d M Y', strtotime($due)) . ".");
            echo json_encode(['success' => true, 'message' => "Assigned \"$title\" to the selected student."]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Please select a student.']);
            exit;
        }
    }
}

// ── UPDATE STATUS: approve / mark missing ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    $hwId   = (int) ($_POST['hw_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($hwId <= 0 || !in_array($status, ['submitted', 'missing'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid homework selection.']);
        exit;
    }

    $conn->query("UPDATE homework SET status='$status' WHERE id=$hwId");
    echo json_encode(['success' => true, 'message' => 'Homework status updated to ' . ($status === 'submitted' ? 'Submitted' : 'Missing') . '.']);
    exit;
}

echo json_encode(['error' => 'Unknown action']);