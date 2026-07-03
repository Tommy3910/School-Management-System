    <?php

    session_start();

    // DB connection MUST be first
    $conn = mysqli_connect("localhost", "root", "", "rad_project");

    // ── Helpers ──────────────────────────────────────────────
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }

    function flash($key, $val = null)
    {
        if ($val !== null) {
            $_SESSION['_flash'][$key] = $val;
            return;
        }
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }

    function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    function requireAuth($role)
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
            redirect('login.php');
        }
    }

    // ── Demo users ────────────────────────────────────────────
    define('USERS', [
        'student1' => ['password' => 'pass123', 'role' => 'student', 'name' => 'Ali Hassan', 'year' => 10],
        'teacher1' => ['password' => 'pass123', 'role' => 'teacher', 'name' => 'Cikgu Aminah', 'year' => null],
    ]);

    // ── Student data ──────────────────────────────────────────
    // ── Student data ──────────────────────────────────────────
$COURSES = [
    ['code' => 'BM101', 'name' => 'Bahasa Malaysia',    'icon' => '📖', 'pct' => 0],
    ['code' => 'MT102', 'name' => 'Mathematics',         'icon' => '📐', 'pct' => 0],
    ['code' => 'SC103', 'name' => 'Science',             'icon' => '🔬', 'pct' => 0],
    ['code' => 'EN104', 'name' => 'English',             'icon' => '✏️', 'pct' => 0],
    ['code' => 'HI105', 'name' => 'History',             'icon' => '🏛️', 'pct' => 0],
    ['code' => 'GE106', 'name' => 'Geography',           'icon' => '🌍', 'pct' => 0],
    ['code' => 'PE107', 'name' => 'Physical Education',  'icon' => '⚽', 'pct' => 0],
    ['code' => 'AR108', 'name' => 'Art',                 'icon' => '🎨', 'pct' => 0],
];

// Load pct from DB if student is logged in
if (isset($_SESSION['user']['id']) && $_SESSION['user']['role'] === 'student') {
    $uid = (int) $_SESSION['user']['id'];
    try {
        $pct_res = $conn->query(
            "SELECT course_code, pct FROM course_progress WHERE user_id = $uid"
        );
        if ($pct_res) {
            $pct_map = [];
            while ($row = $pct_res->fetch_assoc()) {
                $pct_map[$row['course_code']] = (int) $row['pct'];
            }
            foreach ($COURSES as &$c) {
                if (isset($pct_map[$c['code']])) {
                    $c['pct'] = $pct_map[$c['code']];
                }
            }
            unset($c);
        }
    } catch (mysqli_sql_exception $e) {
        // course_progress table doesn't exist yet — keep default 0
    }
}

    // ── Load timetable from DB ──────────────────────────
    $TIMETABLE = [];
    $TT_GROUP = '10-11'; // default

    if (isset($_SESSION['user']['year'])) {
        $yr = (int) $_SESSION['user']['year'];
        if ($yr <= 6)
            $TT_GROUP = '1-6';
        elseif ($yr <= 9)
            $TT_GROUP = '7-9';
        else
            $TT_GROUP = '10-11';

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
// ── Student's own homework (for studentHomePage.php) ─────
$HOMEWORK = [];
if ($conn && isset($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'student') {
    $sid = (int) $_SESSION['user']['id'];
    $hwRes2 = $conn->query("SELECT * FROM homework WHERE student_id=$sid ORDER BY due_date ASC");
    if ($hwRes2) {
        while ($r = $hwRes2->fetch_assoc()) {
            $HOMEWORK[] = [
                'item'   => $r['title'],
                'type'   => $r['type'],
                'due'    => date('d M Y', strtotime($r['due_date'])),
                'status' => $r['status'],
            ];
        }
    }
}

    $PAYMENTS = [
        ['student'=>'Ali Hassan', 'year'=>10, 'date'=>'1 May 2024', 'amount'=>'RM 450.00','status'=>'Paid'],
        ['student'=>'Siti Aminah', 'year'=>10, 'date'=>'1 Apr 2024', 'amount'=>'RM 450.00','status'=>'Paid'],
        ['student'=>'Rajan Kumar', 'year'=>11, 'date'=>'1 Mar 2024', 'amount'=>'RM 450.00','status'=>'Invoice Sent'],
    ];

    // ── Teacher data ──────────────────────────────────────────
    $SPORT = [
        ['group' => 'Year 7', 'day' => 'Monday', 'bg' => 'rgba(212,168,71,.08)', 'col' => '#8a6200'],
        ['group' => 'Year 8', 'day' => 'Tuesday', 'bg' => 'rgba(45,106,79,.08)', 'col' => '#1b5e20'],
        ['group' => 'Year 9', 'day' => 'Wednesday', 'bg' => 'rgba(212,168,71,.08)', 'col' => '#8a6200'],
        ['group' => 'Year 10', 'day' => 'Thursday', 'bg' => 'rgba(45,106,79,.08)', 'col' => '#1b5e20'],
        ['group' => 'Year 11', 'day' => 'Friday', 'bg' => 'rgba(212,168,71,.08)', 'col' => '#8a6200'],
    ];
    $DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    $GRADES = [
        ['Ali Hassan', 10, 'A', 'A-', 'B+', 'A', 'B', 'A-'],
        ['Siti Aminah', 10, 'B', 'B+', 'A-', 'B', 'A', 'B+'],
        ['Rajan Kumar', 11, 'A-', 'A', 'A', 'A-', 'A', 'A'],
        ['Mei Ling', 9, 'B+', 'B', 'B-', 'A-', 'B+', 'B'],
        ['Hafiz Ismail', 8, 'C+', 'B', 'C', 'B-', 'C+', 'C+'],
        ['Nurul Huda', 10, 'B', 'A-', 'B+', 'B', 'B+', 'B+'],
    ];
    // Add overall grade
    foreach ($GRADES as &$g) {
        $g[] = $g[2];
    }
    unset($g);

// ── Load real students from DB ──────────────────────────
$ALL_STUDENTS = [];
if ($conn) {
    $stuRes = $conn->query("SELECT id, name, year FROM users WHERE role='student' ORDER BY name");
    if ($stuRes) {
        while ($r = $stuRes->fetch_assoc()) {
            $ALL_STUDENTS[] = [
                'id'   => (int) $r['id'],
                'name' => $r['name'],
                'year' => (int) $r['year'],
            ];
        }
    }
}

// ── Load homework from DB ────────────────────────────────
$HOMEWORK_ALL = [];
if ($conn) {
    $hwRes = $conn->query(
        "SELECT h.*, u.name AS student_name, u.year AS student_year
         FROM homework h JOIN users u ON h.student_id = u.id
         ORDER BY h.due_date ASC"
    );
    if ($hwRes) {
        while ($r = $hwRes->fetch_assoc()) {
            $HOMEWORK_ALL[] = $r;
        }
    }
}
// Fallback to demo data only if DB has no students
if (empty($ALL_STUDENTS)) {
    $ALL_STUDENTS = [
        ['name' => 'Ali Hassan', 'year' => 10, 'hw' => 'Chapter 5 Essay', 'due' => '10 May 2024'],
        ['name' => 'Siti Aminah', 'year' => 10, 'hw' => 'Algebra Quiz', 'due' => '12 May 2024'],
        ['name' => 'Rajan Kumar', 'year' => 11, 'hw' => 'Science Lab Report', 'due' => '14 May 2024'],
    ];
}



    // ── Grade calculator ─────────────────────────────────
    function calcGrade($marks, $max = 100)
    {
        $pct = ($max > 0) ? ($marks / $max) * 100 : 0;
        if ($pct >= 90)
            return 'A+';
        if ($pct >= 80)
            return 'A';
        if ($pct >= 75)
            return 'A-';
        if ($pct >= 70)
            return 'B+';
        if ($pct >= 65)
            return 'B';
        if ($pct >= 60)
            return 'B-';
        if ($pct >= 55)
            return 'C+';
        if ($pct >= 50)
            return 'C';
        if ($pct >= 45)
            return 'C-';
        if ($pct >= 40)
            return 'D';
        return 'E';
    }

    function gradeColor($grade)
    {
        if (in_array($grade, ['A+', 'A', 'A-']))
            return ['#e8f5e9', '#1b5e20'];
        if (in_array($grade, ['B+', 'B', 'B-']))
            return ['#e3f2fd', '#0d47a1'];
        if (in_array($grade, ['C+', 'C', 'C-']))
            return ['#fff3e0', '#bf360c'];
        if ($grade === 'D')
            return ['#fce4ec', '#880e4f'];
        return ['#ffebee', '#b71c1c']; // E
    }

    // ── Notification helpers ──────────────────────────────
    function sendNotification($conn, $user_id, $title, $message)
    {
        $uid = (int) $user_id;
        $t = $conn->real_escape_string($title);
        $m = $conn->real_escape_string($message);
        $conn->query("INSERT INTO notifications (user_id, title, message)
                    VALUES ($uid, '$t', '$m')");
    }

    function getUnreadCount($conn, $user_id)
    {
        $uid = (int) $user_id;
        $r = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id=$uid AND is_read=0");
        return $r ? (int) $r->fetch_assoc()['cnt'] : 0;
    }

    function getNotifications($conn, $user_id, $limit = 10)
    {
        $uid = (int) $user_id;
        $r = $conn->query("SELECT * FROM notifications WHERE user_id=$uid ORDER BY created_at DESC LIMIT $limit");
        $out = [];
        if ($r)
            while ($row = $r->fetch_assoc())
                $out[] = $row;
        return $out;
    }

    function markAllRead($conn, $user_id)
    {
        $uid = (int) $user_id;
        $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");
    }