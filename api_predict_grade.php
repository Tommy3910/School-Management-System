<?php
// api_predict_grade.php
// Bridge endpoint for ML.NET C# ASP.NET Core Grade Prediction API
// Communicates with GradeInsightsService (http://localhost:5000/api/insights/predict)
// with built-in intelligent fallback when the C# service is starting or offline.

require_once 'config.php';
header('Content-Type: application/json');

// Health check endpoint
if (isset($_GET['action']) && $_GET['action'] === 'check_health') {
    $csharpUrl = defined('GRADE_PREDICTOR_API_URL') ? GRADE_PREDICTOR_API_URL : 'http://localhost:5000/api/insights/predict';
    $ch = curl_init($csharpUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'StudyHours' => 10,
        'AttendancePercentage' => 90,
        'PreviousGrade' => 80
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $isOnline = ($httpCode >= 200 && $httpCode < 300);
    echo json_encode([
        'online' => $isOnline,
        'httpCode' => $httpCode,
        'serviceUrl' => $csharpUrl,
        'engine' => $isOnline ? 'C# ASP.NET Core + ML.NET' : 'Local Fallback Regressor'
    ]);
    exit;
}

// Read input (support JSON body or standard POST)
$inputRaw = file_get_contents('php://input');
$data = json_decode($inputRaw, true);
if (!$data || !is_array($data)) {
    $data = $_POST;
}

$studyHours = floatval($data['study_hours'] ?? $data['studyHours'] ?? 10);
$attendance = floatval($data['attendance_percentage'] ?? $data['attendancePercentage'] ?? 90);
$previousGrade = floatval($data['previous_grade'] ?? $data['previousGrade'] ?? 75);
$subjectName = trim($data['subject'] ?? $data['subject_name'] ?? 'Overall');

// Clamp input values to valid domains
$studyHours = max(0, min(60, $studyHours));
$attendance = max(0, min(100, $attendance));
$previousGrade = max(0, min(100, $previousGrade));

$csharpUrl = defined('GRADE_PREDICTOR_API_URL') ? GRADE_PREDICTOR_API_URL : 'http://localhost:5000/api/insights/predict';
$source = 'local_smart_estimator';
$predictedScore = null;
$csharpSuccess = false;
$csharpError = null;

// Prepare C# ML.NET input model matching StudentGradeInput schema
$payload = json_encode([
    'StudyHours' => (float) $studyHours,
    'AttendancePercentage' => (float) $attendance,
    'PreviousGrade' => (float) $previousGrade,
]);

// Call C# ASP.NET Core service
if (extension_loaded('curl')) {
    $ch = curl_init($csharpUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1500);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2500);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $resObj = json_decode($response, true);
        if (isset($resObj['predictedScore'])) {
            $predictedScore = round((float) $resObj['predictedScore'], 1);
            $source = 'csharp_ml_net';
            $csharpSuccess = true;
        } elseif (isset($resObj['PredictedScore'])) {
            $predictedScore = round((float) $resObj['PredictedScore'], 1);
            $source = 'csharp_ml_net';
            $csharpSuccess = true;
        } elseif (isset($resObj['score'])) {
            $predictedScore = round((float) $resObj['score'], 1);
            $source = 'csharp_ml_net';
            $csharpSuccess = true;
        }
    } else {
        $csharpError = $curlErr ? $curlErr : "HTTP status {$httpCode}";
    }
}

// Fallback intelligent regression if C# API is offline or hasn't started yet
if ($predictedScore === null) {
    // Statistically weighted academic formula based on ML.NET training characteristics:
    // Base weight on previous score (50%), attendance consistency (32%), study hours return (1.25 pts/hr capped) + baseline (2.5)
    $studyFactor = min(30.0, $studyHours) * 1.25;
    $rawEstimate = ($previousGrade * 0.50) + ($attendance * 0.32) + $studyFactor + 2.5;
    $predictedScore = round(max(0, min(100, $rawEstimate)), 1);
    $source = 'local_smart_estimator';
}

// Ensure bounds 0 - 100
$predictedScore = max(0, min(100, $predictedScore));

// Determine Letter Grade using system helper
$letterGrade = calcGrade($predictedScore, 100);
$gradeCol = gradeColor($letterGrade);

// Score differential vs previous score
$delta = round($predictedScore - $previousGrade, 1);

// Generate personalized study tips
$insights = [];
if ($studyHours < 8) {
    $boostTarget = round($predictedScore + (8 - $studyHours) * 1.25, 1);
    $insights[] = "Increasing weekly study to 8-10 hours could lift your score to ~{$boostTarget}% (+".round((8 - $studyHours) * 1.25, 1)."%).";
} elseif ($studyHours >= 15) {
    $insights[] = "Outstanding study dedication ({$studyHours} hrs/wk). Consistent spaced repetition will maximize retention.";
} else {
    $insights[] = "Good study rhythm ({$studyHours} hrs/wk). Maintaining this balance supports steady academic gains.";
}

if ($attendance < 80) {
    $insights[] = "Attendance is at {$attendance}%. Improving attendance to 90%+ directly correlates with higher exam confidence.";
} elseif ($attendance >= 95) {
    $insights[] = "Excellent attendance rate ({$attendance}%) — classroom engagement is a major asset for your predicted grade.";
}

if ($delta > 0) {
    $insights[] = "Projected upward trend of +{$delta}% compared to previous performance.";
} elseif ($delta < 0) {
    $insights[] = "Projected slight dip of {$delta}%. Review challenging topics early to reverse this trend.";
} else {
    $insights[] = "Projected to maintain your current grade bracket with stable results.";
}

// Performance tier
$tier = 'Average';
if ($predictedScore >= 90) $tier = 'Distinction Level';
elseif ($predictedScore >= 80) $tier = 'High Distinction';
elseif ($predictedScore >= 70) $tier = 'Merit Standard';
elseif ($predictedScore >= 60) $tier = 'Pass with Credit';
elseif ($predictedScore >= 50) $tier = 'Pass Standard';
else $tier = 'Requires Targeted Intervention';

echo json_encode([
    'success' => true,
    'source' => $source,
    'csharpOnline' => $csharpSuccess,
    'serviceUrl' => $csharpUrl,
    'subject' => $subjectName,
    'inputs' => [
        'studyHours' => $studyHours,
        'attendancePercentage' => $attendance,
        'previousGrade' => $previousGrade,
    ],
    'prediction' => [
        'predictedScore' => $predictedScore,
        'letterGrade' => $letterGrade,
        'badgeBg' => $gradeCol[0],
        'badgeText' => $gradeCol[1],
        'tier' => $tier,
        'delta' => $delta,
    ],
    'insights' => $insights,
    'statusNote' => $csharpSuccess
        ? 'Computed by live C# ASP.NET Core & ML.NET Microservice'
        : 'Computed by smart local estimator (C# ASP.NET Core service offline on localhost:5000)'
]);
