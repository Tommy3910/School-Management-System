<?php
require_once 'config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to use the chat assistant.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if ($userMessage === '') {
    echo json_encode(['error' => 'Please type a question.']);
    exit;
}

$msgLower = strtolower($userMessage);
$userId = $_SESSION['user']['id'] ?? 0;

// ── Save the incoming user message immediately ──
$stmt = $conn->prepare("INSERT INTO chatbot_messages (user_id, role, message) VALUES (?, 'user', ?)");
$stmt->bind_param('is', $userId, $userMessage);
$stmt->execute();
$stmt->close();

// ── Step 1: Detect planning-style questions FIRST ─────────
// These need real AI reasoning, not keyword matching, so they're
// checked before the rule-based system and routed differently.
$planningKeywords = [
    'plan my', 'plan for', 'plan the week', 'plan this week',
    'how should i study', 'how do i study', 'study plan',
    'organize my', 'manage my time', 'time management',
    'catch up on', 'prioritize', 'prioritise',
    'what should i do first', 'which subject should i',
    'help me plan', 'busy week', 'too much homework',
    'falling behind', 'overwhelmed'
];

$isPlanningQuestion = false;
foreach ($planningKeywords as $kw) {
    if (str_contains($msgLower, $kw)) {
        $isPlanningQuestion = true;
        break;
    }
}

if (!$isPlanningQuestion && preg_match('/\bplan(ning)?\b/', $msgLower)) {
    $isPlanningQuestion = true;
}

// ── Step 2a: Planning question → build personalized AI prompt ──
if ($isPlanningQuestion) {

    // Pull this student's upcoming/overdue homework
    $hwList = [];
    $hwStmt = $conn->prepare("SELECT title, type, due_date, status FROM homework WHERE student_id = ? AND status != 'submitted' ORDER BY due_date ASC LIMIT 10");
    $hwStmt->bind_param('i', $userId);
    $hwStmt->execute();
    $hwRes = $hwStmt->get_result();
    while ($r = $hwRes->fetch_assoc()) $hwList[] = $r;
    $hwStmt->close();

    $today = new DateTime('today');
    $hwText = '';
    if (empty($hwList)) {
        $hwText = "No pending homework, quizzes, or tests right now.";
    } else {
        foreach ($hwList as $h) {
            $due = new DateTime($h['due_date']);
            $diffDays = (int) $today->diff($due)->format('%r%a');
            if ($diffDays < 0) {
                $status = 'OVERDUE by ' . abs($diffDays) . ' day(s)';
            } elseif ($diffDays === 0) {
                $status = 'DUE TODAY';
            } else {
                $status = 'due in ' . $diffDays . ' day(s)';
            }
            $hwText .= "- {$h['title']} ({$h['type']}), {$status}\n";
        }
    }

    // Pull this student's weekly timetable (already loaded by config.php for their year group)
    $ttText = '';
    if (!empty($TIMETABLE)) {
        foreach ($TIMETABLE as $row) {
            $periods = array_filter([
                $row['p1'] ?? '', $row['p2'] ?? '', $row['p3'] ?? '', $row['p4'] ?? '',
                $row['p5'] ?? '', $row['p6'] ?? '', $row['p7'] ?? ''
            ]);
            $sportNote = !empty($row['sport_day']) ? ' (Sport Day)' : '';
            $ttText .= "- {$row['day']}{$sportNote}: " . implode(', ', $periods) . "\n";
        }
    } else {
        $ttText = "Timetable not available.";
    }

    $planningSystemPrompt = <<<PROMPT
You are the Maplewood Learning Center study planning assistant. A student is asking for help planning their time. Use ONLY the real data below — do not invent homework or classes that aren't listed.

STUDENT'S PENDING HOMEWORK/TESTS/QUIZZES:
{$hwText}

STUDENT'S WEEKLY TIMETABLE:
{$ttText}

Give short, practical, encouraging advice (4-6 sentences or a short bullet list). Reference their actual due dates and schedule by name where relevant. If they have nothing pending, say so positively and suggest general good habits (reviewing notes, staying ahead). Do not discuss grades, payments, or topics outside study planning — redirect those to the relevant tab.
PROMPT;

    // Pull recent real conversation history from DB for multi-turn context
    $geminiContents = [];
    $histStmt = $conn->prepare("SELECT role, message FROM chatbot_messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $histStmt->bind_param('i', $userId);
    $histStmt->execute();
    $histRes = $histStmt->get_result();
    $recentRows = [];
    while ($r = $histRes->fetch_assoc()) $recentRows[] = $r;
    $histStmt->close();
    $recentRows = array_reverse($recentRows); // chronological order

    foreach ($recentRows as $r) {
        $geminiContents[] = [
            'role' => $r['role'] === 'user' ? 'user' : 'model',
            'parts' => [['text' => $r['message']]]
        ];
    }
    if (empty($geminiContents)) {
        $geminiContents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];
    }

    $payload = [
        'system_instruction' => [
            'parts' => [['text' => $planningSystemPrompt]]
        ],
        'contents' => $geminiContents,
        'generationConfig' => [
            'maxOutputTokens' => 1200
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . GEMINI_API_KEY;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($httpCode !== 200 || !$replyText) {
        $replyText = 'Sorry, the planning assistant is unavailable right now. Please try again later.';
        echo json_encode(['error' => $replyText]);
        exit;
    }

    $saveStmt = $conn->prepare("INSERT INTO chatbot_messages (user_id, role, message) VALUES (?, 'bot', ?)");
    $saveStmt->bind_param('is', $userId, $replyText);
    $saveStmt->execute();
    $saveStmt->close();

    echo json_encode(['reply' => $replyText]);
    exit;
}

// ── Step 2b: Not a planning question → rule-based matching ──

$subjectList = [];
$sres = $conn->query("SELECT DISTINCT name FROM subjects ORDER BY name");
if ($sres) while ($r = $sres->fetch_assoc()) $subjectList[] = $r['name'];
$subjectText = $subjectList ? implode(', ', $subjectList) : 'not available right now';

$rules = [
    [
        'keywords' => ['fee', 'fees', 'pay', 'payment', 'invoice', 'cost', 'price', 'how much'],
        'response' => "School fees are RM450 per month. You can pay via the Payment tab using Stripe checkout — invoices show as \"Invoice Sent\" until paid."
    ],
    [
        'keywords' => ['timetable', 'schedule', 'period', 'class time', 'hours', 'sport day'],
        'response' => "Year 1-6 classes run 8:00am to 12:45pm with 1-hour periods. Year 7-11 classes run 8:00am to 1:45pm with 45-minute periods. Each year group has one 2-hour Sport Day per week. Check the Timetable tab for your exact schedule."
    ],
    [
        'keywords' => ['subject', 'subjects', 'course', 'courses', 'what do i study', 'classes offered'],
        'response' => "Current subjects offered: {$subjectText}. You can see your enrolled courses under the Courses tab."
    ],
    [
        'keywords' => ['homework', 'assignment', 'submit', 'missing work', 'due date'],
        'response' => "You can track your homework submissions, quizzes, and tests under the Homework Status tab, which shows what's submitted and what's missing."
    ],
    [
        'keywords' => ['grade', 'grades', 'result', 'results', 'marks', 'score'],
        'response' => "Your released grades are visible under the Grades tab, broken down by term and subject."
    ],
    [
        'keywords' => ['login', 'log in', 'password', 'account', 'sign in'],
        'response' => "Use your school email and password to log in. If you're having trouble accessing your account, please contact the school office."
    ],
    [
        'keywords' => ['notification', 'notifications', 'alert', 'bell'],
        'response' => "Notifications appear under the bell icon at the top of your dashboard. Click it to see announcements, reminders, and updates, and mark them as read."
    ],
    [
        'keywords' => ['hi', 'hello', 'hey', 'greetings'],
        'response' => "Hi there! I am MapleGoat. I can help with questions about fees, timetables, subjects, homework, grades, and even help you plan your study schedule. What would you like to know?"
    ],
];

$kres = $conn->query("SELECT topic, content FROM chatbot_knowledge ORDER BY id");
if ($kres) {
    while ($r = $kres->fetch_assoc()) {
        $topicWords = preg_split('/\s+/', strtolower(trim($r['topic'])));
        $rules[] = [
            'keywords' => $topicWords,
            'response' => $r['content']
        ];
    }
}

$bestRule = null;
$bestScore = 0;

foreach ($rules as $rule) {
    $score = 0;
    foreach ($rule['keywords'] as $kw) {
        $kw = trim($kw);
        if ($kw !== '' && str_contains($msgLower, $kw)) {
            $score++;
        }
    }
    if ($score > $bestScore) {
        $bestScore = $score;
        $bestRule = $rule;
    }
}

$finalReply = ($bestRule && $bestScore > 0)
    ? $bestRule['response']
    : "Sorry, I can only help with general questions about fees, timetables, subjects, homework, grades, site navigation, and study planning. For anything else, please contact the school office.";

$saveStmt = $conn->prepare("INSERT INTO chatbot_messages (user_id, role, message) VALUES (?, 'bot', ?)");
$saveStmt->bind_param('is', $userId, $finalReply);
$saveStmt->execute();
$saveStmt->close();

echo json_encode(['reply' => $finalReply]);