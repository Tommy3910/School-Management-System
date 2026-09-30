<?php
require_once __DIR__ . '/config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}
echo "Connected successfully!\n";

// Check homework columns
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM homework");
while ($r = $res->fetch_assoc()) {
    $cols[] = $r['Field'];
}
echo "Homework columns: " . implode(', ', $cols) . "\n";

// Add submission_file, submission_notes, submitted_at if they don't exist
if (!in_array('submission_file', $cols)) {
    $conn->query("ALTER TABLE homework ADD COLUMN submission_file VARCHAR(255) DEFAULT NULL");
    echo "Added submission_file\n";
}
if (!in_array('submission_notes', $cols)) {
    $conn->query("ALTER TABLE homework ADD COLUMN submission_notes TEXT DEFAULT NULL");
    echo "Added submission_notes\n";
}
if (!in_array('submitted_at', $cols)) {
    $conn->query("ALTER TABLE homework ADD COLUMN submitted_at TIMESTAMP NULL DEFAULT NULL");
    echo "Added submitted_at\n";
}

// Check notifications table columns
$nCols = [];
$res = $conn->query("SHOW COLUMNS FROM notifications");
while ($r = $res->fetch_assoc()) {
    $nCols[] = $r['Field'];
}
echo "Notifications columns: " . implode(', ', $nCols) . "\n";

// Add course_code or subject_id to notifications if needed for requirement 8 & 9
if (!in_array('course_code', $nCols)) {
    $conn->query("ALTER TABLE notifications ADD COLUMN course_code VARCHAR(20) DEFAULT NULL");
    echo "Added course_code to notifications\n";
}
if (!in_array('sender_name', $nCols)) {
    $conn->query("ALTER TABLE notifications ADD COLUMN sender_name VARCHAR(100) DEFAULT NULL");
    echo "Added sender_name to notifications\n";
}

// Check if calendar_events table exists
$res = $conn->query("SHOW TABLES LIKE 'calendar_events'");
if ($res->num_rows === 0) {
    $conn->query("CREATE TABLE calendar_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        event_date DATE NOT NULL,
        event_time VARCHAR(50) DEFAULT NULL,
        event_type ENUM('holiday', 'special_event', 'exam', 'sport', 'homework') NOT NULL DEFAULT 'special_event',
        description TEXT DEFAULT NULL,
        target_year INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created calendar_events table\n";

    // Insert sample school events
    $conn->query("INSERT INTO calendar_events (title, event_date, event_time, event_type, description, target_year) VALUES
        ('Annual Sports Day', '2026-06-15', '08:00 AM - 02:00 PM', 'sport', 'All year groups participate in track and field events at the Main Sports Complex.', NULL),
        ('Mid-Term Examination Week', '2026-06-18', '08:00 AM - 01:00 PM', 'exam', 'Mid-Term Examinations for secondary year groups.', NULL),
        ('Science & STEM Fair', '2026-07-10', '09:00 AM - 03:00 PM', 'special_event', 'Annual student science exhibition and project competition.', NULL),
        ('Parent-Teacher Conference', '2026-07-24', '09:00 AM - 04:00 PM', 'special_event', 'Academic progress review and consultation meetings with parents.', NULL),
        ('Merdeka Day Celebration', '2026-08-28', '08:30 AM - 12:30 PM', 'special_event', 'National Day assembly, cultural performances, and patriotic dress.', NULL)
    ");
    echo "Inserted sample events\n";
} else {
    echo "calendar_events table already exists\n";
}

echo "Migration completed successfully!\n";
