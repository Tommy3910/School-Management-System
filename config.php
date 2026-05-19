<?php
session_start();

// DB connection MUST be first
$conn = mysqli_connect("localhost", "root", "", "rad_project");

// ── Helpers ──────────────────────────────────────────────
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function flash($key, $val = null) {
    if ($val !== null) { $_SESSION['_flash'][$key] = $val; return; }
    $v = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $v;
}

function redirect($url) { header("Location: $url"); exit; }

function requireAuth($role) {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        redirect('login.php');
    }
}

// ── Demo users ────────────────────────────────────────────
define('USERS', [
    'student1' => ['password' => 'pass123', 'role' => 'student', 'name' => 'Ali Hassan',    'year' => 10],
    'teacher1' => ['password' => 'pass123', 'role' => 'teacher', 'name' => 'Cikgu Aminah',  'year' => null],
]);

// ── Student data ──────────────────────────────────────────
$COURSES = [
    ['code'=>'BM101',  'name'=>'Bahasa Malaysia',  'icon'=>'📖', 'pct'=>78],
    ['code'=>'MT102',  'name'=>'Mathematics',       'icon'=>'📐', 'pct'=>85],
    ['code'=>'SC103',  'name'=>'Science',           'icon'=>'🔬', 'pct'=>72],
    ['code'=>'EN104',  'name'=>'English',           'icon'=>'✏️', 'pct'=>90],
    ['code'=>'HI105',  'name'=>'History',           'icon'=>'🏛️', 'pct'=>65],
    ['code'=>'GE106',  'name'=>'Geography',         'icon'=>'🌍', 'pct'=>70],
    ['code'=>'PE107',  'name'=>'Physical Education','icon'=>'⚽', 'pct'=>95],
    ['code'=>'AR108',  'name'=>'Art',               'icon'=>'🎨', 'pct'=>88],
];

// ── Load timetable from DB ──────────────────────────
$TIMETABLE = [];
$TT_GROUP  = '10-11'; // default

if (isset($_SESSION['user']['year'])) {
    $yr = (int)$_SESSION['user']['year'];
    if ($yr <= 6)      $TT_GROUP = '1-6';
    elseif ($yr <= 9)  $TT_GROUP = '7-9';
    else               $TT_GROUP = '10-11';

    $tt_res = $conn->query(
        "SELECT * FROM timetable
         WHERE year = $yr AND year_group = '$TT_GROUP'
         ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday')"
    );
    if ($tt_res) {
        while ($r = $tt_res->fetch_assoc()) {
            $TIMETABLE[] = $r;
        }
    }
}
$HOMEWORK = [
    ['item'=>'Chapter 5 Essay',      'type'=>'Homework','due'=>'10 May 2024','status'=>'submitted'],
    ['item'=>'Algebra Quiz',         'type'=>'Quiz',    'due'=>'12 May 2024','status'=>'submitted'],
    ['item'=>'Science Lab Report',   'type'=>'Homework','due'=>'14 May 2024','status'=>'missing'],
    ['item'=>'History Test',         'type'=>'Test',    'due'=>'16 May 2024','status'=>'submitted'],
    ['item'=>'English Composition',  'type'=>'Homework','due'=>'18 May 2024','status'=>'missing'],
    ['item'=>'Geography Map Work',   'type'=>'Homework','due'=>'20 May 2024','status'=>'missing'],
];

$PAYMENTS = [
    ['student'=>'Ali Hassan', 'year'=>10, 'date'=>'1 May 2024', 'amount'=>'RM 450.00','status'=>'Paid'],
    ['student'=>'Siti Aminah', 'year'=>10, 'date'=>'1 Apr 2024', 'amount'=>'RM 450.00','status'=>'Paid'],
    ['student'=>'Rajan Kumar', 'year'=>11, 'date'=>'1 Mar 2024', 'amount'=>'RM 450.00','status'=>'Invoice Sent'],
];

// ── Teacher data ──────────────────────────────────────────
$SPORT = [
    ['group'=>'Year 7',  'day'=>'Monday',    'bg'=>'rgba(212,168,71,.08)', 'col'=>'#8a6200'],
    ['group'=>'Year 8',  'day'=>'Tuesday',   'bg'=>'rgba(45,106,79,.08)',  'col'=>'#1b5e20'],
    ['group'=>'Year 9',  'day'=>'Wednesday', 'bg'=>'rgba(212,168,71,.08)', 'col'=>'#8a6200'],
    ['group'=>'Year 10', 'day'=>'Thursday',  'bg'=>'rgba(45,106,79,.08)',  'col'=>'#1b5e20'],
    ['group'=>'Year 11', 'day'=>'Friday',    'bg'=>'rgba(212,168,71,.08)', 'col'=>'#8a6200'],
];
$DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday'];

$GRADES = [
    ['Ali Hassan',    10, 'A','A-','B+','A','B','A-'],
    ['Siti Aminah',   10, 'B','B+','A-','B','A','B+'],
    ['Rajan Kumar',   11, 'A-','A','A','A-','A','A'],
    ['Mei Ling',      9,  'B+','B','B-','A-','B+','B'],
    ['Hafiz Ismail',  8,  'C+','B','C','B-','C+','C+'],
    ['Nurul Huda',    10, 'B','A-','B+','B','B+','B+'],
];
// Add overall grade
foreach ($GRADES as &$g) { $g[] = $g[2]; } unset($g);

$ALL_STUDENTS = [
    ['name'=>'Ali Hassan',   'year'=>10,'hw'=>'Chapter 5 Essay',     'due'=>'10 May 2024'],
    ['name'=>'Siti Aminah',  'year'=>10,'hw'=>'Algebra Quiz',        'due'=>'12 May 2024'],
    ['name'=>'Rajan Kumar',  'year'=>11,'hw'=>'Science Lab Report',  'due'=>'14 May 2024'],
    ['name'=>'Mei Ling',     'year'=>9, 'hw'=>'History Test',        'due'=>'16 May 2024'],
    ['name'=>'Hafiz Ismail', 'year'=>8, 'hw'=>'English Composition', 'due'=>'18 May 2024'],
    ['name'=>'Nurul Huda',   'year'=>10,'hw'=>'Geography Map Work',  'due'=>'20 May 2024'],
];

// Init homework session statuses
if (!isset($_SESSION['hw_statuses'])) {
    $_SESSION['hw_statuses'] = array_fill(0, count($ALL_STUDENTS), 'missing');
    $_SESSION['hw_statuses'][0] = 'submitted';
    $_SESSION['hw_statuses'][1] = 'submitted';
}