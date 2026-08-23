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

// ── Pull live subjects from DB so the bot reflects real data ──
$subjectList = [];
$sres = $conn->query("SELECT DISTINCT name FROM subjects ORDER BY name");
if ($sres) while ($r = $sres->fetch_assoc()) $subjectList[] = $r['name'];
$subjectText = $subjectList ? implode(', ', $subjectList) : 'not available right now';

// ── Pull custom knowledge entries you've added yourself ──
$knowledgeText = '';
$kres = $conn->query("SELECT topic, content FROM chatbot_knowledge ORDER BY id");
if ($kres) {
    while ($r = $kres->fetch_assoc()) {
        $knowledgeText .= "- {$r['topic']}: {$r['content']}\n";
    }
}
if ($knowledgeText === '') {
    $knowledgeText = "(No custom entries added yet.)";
}

$systemPrompt = <<<PROMPT
You are the Maplewood Learning Center school assistant chatbot. Only answer questions using the information below.

CORE FACTS:
- School fees: RM450 per month, paid via the Payment tab using Stripe checkout. Invoices show as "Invoice Sent" until paid.
- Timetable structure: Year 1-6 runs 8:00am-12:45pm with 1-hour periods. Year 7-11 runs 8:00am-1:45pm with 45-minute periods (plus two 30-minute periods around the midday break). Each year group has one assigned 2-hour Sport Day per week.
- Current subjects offered: {$subjectText}
- Site navigation: Students see Courses, Timetable, Homework Status, and Payment tabs. Teachers see Schedules, Timetable Editor, Student Grades, Homework Oversight, and Payments tabs.

SCHOOL-SPECIFIC KNOWLEDGE (maintained by staff):
{$knowledgeText}

If asked something outside this scope (e.g. specific grades, specific homework due dates, anything requiring personal account data), politely explain you can only help with general school questions and suggest they check the relevant tab or contact the school office. Keep answers short — 2 to 4 sentences, friendly tone.
PROMPT;

// Gemini puts the system prompt in its own field, and wraps user text in "parts"
$payload = [
    'system_instruction' => [
        'parts' => [['text' => $systemPrompt]]
    ],
    'contents' => [
        ['role' => 'user', 'parts' => [['text' => $userMessage]]]
    ],
    'generationConfig' => [
        'maxOutputTokens' => 400
    ]
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;

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
    echo json_encode(['error' => 'Sorry, the assistant is unavailable right now. Please try again later.']);
    exit;
}

echo json_encode(['reply' => $replyText]);