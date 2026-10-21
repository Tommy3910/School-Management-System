<?php
require_once 'config.php';
require_once 'layout.php';
requireAuth('student');

$user     = $_SESSION['user'];
$userId   = $_SESSION['user']['id'] ?? null;
$code     = $_GET['code'] ?? '';
$tab      = 'courses';

// ── Course catalogue ──────────────────────────────────────────────────────────
// Each entry mirrors the cards shown on the courses tab.
$CATALOGUE = [
  'BM101' => [
    'code'    => 'BM101',
    'name'    => 'Bahasa Malaysia',
    'icon'    => '📖',
    'color'   => '#e8f5e9',
    'accent'  => '#1b5e20',
    'teacher' => 'Cikgu Aminah binti Razali',
    'pct'     => 78,
    'desc'    => 'Kursus ini merangkumi kemahiran membaca, menulis, mendengar dan bertutur dalam Bahasa Malaysia standard. Pelajar akan mendedahkan diri kepada pelbagai genre sastera dan bukan sastera.',
    'topics'  => ['Tatabahasa & Morfologi', 'Penulisan Karangan', 'Komsas – Novel & Puisi', 'Pemahaman Petikan', 'Lisan & Pembentangan'],
    'schedule'=> ['Monday 8:00–9:00', 'Wednesday 10:45–11:45', 'Friday 11:45–12:45'],
    'materials'=> [
      ['title'=>'Nota Tatabahasa Bab 1–3',   'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Latihan Karangan Minggu 4',  'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'Komsas – Soalan Ramalan',    'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Slaid Pembentangan Unit 2',  'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
    ],
    'upcoming'=> [
      ['label'=>'Ujian Tatabahasa',    'date'=>'15 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Hantar Karangan #3', 'date'=>'20 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Kuiz Komsas',        'date'=>'27 Jun 2026', 'badge'=>'bqz'],
    ],
  ],
  'MT102' => [
    'code'    => 'MT102',
    'name'    => 'Mathematics',
    'icon'    => '📐',
    'color'   => '#e3f2fd',
    'accent'  => '#0d47a1',
    'teacher' => 'Mr. Lim Boon Keong',
    'pct'     => 85,
    'desc'    => 'This course covers fundamental and applied mathematics including algebra, geometry, statistics, and pre-calculus concepts. Students develop analytical thinking and problem-solving skills.',
    'topics'  => ['Algebra & Indices', 'Geometry & Trigonometry', 'Statistics & Probability', 'Linear Equations', 'Quadratic Functions'],
    'schedule'=> ['Tuesday 8:00–9:00', 'Thursday 8:45–9:30', 'Friday 10:45–11:45'],
    'materials'=> [
      ['title'=>'Chapter 4 – Quadratics Notes', 'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Practice Set 7',               'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Algebra Worksheet Week 5',      'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'Statistics Slides',             'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
    ],
    'upcoming'=> [
      ['label'=>'Mid-Term Test',        'date'=>'18 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Problem Set 8 Due',   'date'=>'22 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Statistics Quiz',     'date'=>'29 Jun 2026', 'badge'=>'bqz'],
    ],
  ],
  'SC103' => [
    'code'    => 'SC103',
    'name'    => 'Science',
    'icon'    => '🔬',
    'color'   => '#f3e5f5',
    'accent'  => '#6a1b9a',
    'teacher' => 'Puan Norzahra binti Hamid',
    'pct'     => 72,
    'desc'    => 'An integrated science course covering biology, chemistry, and physics concepts. Emphasis on inquiry-based learning, lab experiments, and scientific reporting.',
    'topics'  => ['Cell Biology & Life Processes', 'Matter & States', 'Forces & Motion', 'Energy Transformations', 'Ecosystems'],
    'schedule'=> ['Monday 10:45–11:45', 'Wednesday 8:00–9:00', 'Thursday 11:45–12:30'],
    'materials'=> [
      ['title'=>'Lab Report Template',          'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'Chapter 5 – Energy Notes',     'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Cell Biology Slides',          'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
      ['title'=>'Ecosystem Worksheet',          'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
    ],
    'upcoming'=> [
      ['label'=>'Lab Report Due',     'date'=>'14 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Chapter 5 Test',     'date'=>'21 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Ecosystems Quiz',    'date'=>'28 Jun 2026', 'badge'=>'bqz'],
    ],
  ],
  'EN104' => [
    'code'    => 'EN104',
    'name'    => 'English',
    'icon'    => '✏️',
    'color'   => '#fff8e1',
    'accent'  => '#f57f17',
    'teacher' => 'Ms. Rachel Tan Hui Lin',
    'pct'     => 90,
    'desc'    => 'This course develops proficiency in written and spoken English. Topics include literary analysis, essay writing, grammar, and communication skills aligned with the national curriculum.',
    'topics'  => ['Essay & Creative Writing', 'Literature – Short Stories', 'Grammar in Context', 'Oral Communication', 'Reading Comprehension'],
    'schedule'=> ['Tuesday 10:45–11:45', 'Thursday 8:00–8:45', 'Friday 8:45–9:30'],
    'materials'=> [
      ['title'=>'Short Story Anthology PDF',   'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Essay Writing Guide',         'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Grammar Worksheet 6',         'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'Oral Presentation Rubric',    'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
    ],
    'upcoming'=> [
      ['label'=>'Essay Draft 2 Due',       'date'=>'16 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Literature Test',         'date'=>'23 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Oral Presentation',       'date'=>'30 Jun 2026', 'badge'=>'bqz'],
    ],
  ],
  'HI105' => [
    'code'    => 'HI105',
    'name'    => 'History',
    'icon'    => '🏛️',
    'color'   => '#fce4ec',
    'accent'  => '#880e4f',
    'teacher' => 'Encik Hafiz bin Sulaiman',
    'pct'     => 65,
    'desc'    => 'Students explore Malaysian and world history from early civilisations to the modern era. Critical thinking, source analysis, and historical essay writing are core skills developed.',
    'topics'  => ['Early Malaysian Civilisations', 'Colonial Era & Independence', 'World War II in Southeast Asia', 'Nation-Building 1957–1980', 'Globalisation & Malaysia Today'],
    'schedule'=> ['Monday 11:45–12:45', 'Wednesday 11:45–12:30', 'Friday 10:00–11:00'],
    'materials'=> [
      ['title'=>'Colonial Era Reading Pack',   'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Source Analysis Worksheet',  'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'WW2 SEA Slides',             'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
      ['title'=>'Nation-Building Essay Guide','type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
    ],
    'upcoming'=> [
      ['label'=>'Source Analysis Due',    'date'=>'17 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'WWII Chapter Test',      'date'=>'24 Jun 2026', 'badge'=>'bts'],
      ['label'=>'History Quiz 3',         'date'=>'01 Jul 2026', 'badge'=>'bqz'],
    ],
  ],
  'AC106' => [
    'code'    => 'AC106',
    'name'    => 'Account',
    'icon'    => '🌍',
    'color'   => '#e0f7fa',
    'accent'  => '#006064',
    'teacher' => 'Puan Siti Rohani binti Omar',
    'pct'     => 70,
    'desc'    => 'Geography covers physical and human geography of Malaysia and the world. Topics include landforms, climate, population studies, and sustainable development.',
    'topics'  => ['Landforms & Plate Tectonics', 'Climate & Weather Systems', 'Population & Urbanisation', 'Natural Resources', 'Sustainable Development'],
    'schedule'=> ['Tuesday 11:45–12:30', 'Thursday 12:30–1:15', 'Friday 11:00–11:45'],
    'materials'=> [
      ['title'=>'Landforms Chapter Notes',       'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Climate Data Worksheet',        'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'Population Map Activity',       'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Sustainable Dev Slides',        'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
    ],
    'upcoming'=> [
      ['label'=>'Map Activity Due',        'date'=>'19 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Climate Test',            'date'=>'25 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Population Quiz',         'date'=>'02 Jul 2026', 'badge'=>'bqz'],
    ],
  ],
  'PE107' => [
    'code'    => 'PE107',
    'name'    => 'Physical Education',
    'icon'    => '⚽',
    'color'   => '#e8f5e9',
    'accent'  => '#1b5e20',
    'teacher' => 'Coach Daniel Wong Wei Lun',
    'pct'     => 95,
    'desc'    => 'PE develops physical fitness, sportsmanship, and teamwork. Students rotate through major sports, fitness assessment, and health education throughout the year.',
    'topics'  => ['Fitness Assessment & Training', 'Football & Field Sports', 'Badminton & Racket Sports', 'Health & Nutrition', 'First Aid & Safety'],
    'schedule'=> ['Monday Sport Session 8:00–10:00', 'Wednesday 12:30–1:15'],
    'materials'=> [
      ['title'=>'Fitness Test Standards',       'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Health & Nutrition Notes',     'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Sports Rotation Schedule',     'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'First Aid Slides',             'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
    ],
    'upcoming'=> [
      ['label'=>'Fitness Test',            'date'=>'13 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Health Report Due',       'date'=>'26 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Nutrition Quiz',          'date'=>'03 Jul 2026', 'badge'=>'bqz'],
    ],
  ],
  'AR108' => [
    'code'    => 'AR108',
    'name'    => 'Art',
    'icon'    => '🎨',
    'color'   => '#fce4ec',
    'accent'  => '#880e4f',
    'teacher' => 'Cikgu Faridah binti Yusof',
    'pct'     => 88,
    'desc'    => 'Art explores visual arts including drawing, painting, printmaking, and design. Students develop creative expression, art history knowledge, and portfolio skills.',
    'topics'  => ['Drawing & Sketching Techniques', 'Colour Theory & Painting', 'Printmaking', 'Malaysian Traditional Art', 'Portfolio & Critique'],
    'schedule'=> ['Tuesday 8:45–9:30', 'Thursday 11:00–11:45', 'Friday 12:30–1:15'],
    'materials'=> [
      ['title'=>'Colour Theory Notes',          'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
      ['title'=>'Portfolio Submission Guide',   'type'=>'DOCX', 'icon'=>'fas fa-file-word', 'color'=>'#e3f2fd','tc'=>'#0d47a1'],
      ['title'=>'Traditional Art Slides',       'type'=>'PPTX', 'icon'=>'fas fa-file-powerpoint','color'=>'#fff3e0','tc'=>'#e65100'],
      ['title'=>'Printmaking Worksheet',        'type'=>'PDF',  'icon'=>'fas fa-file-pdf',  'color'=>'#ffebee','tc'=>'#c62828'],
    ],
    'upcoming'=> [
      ['label'=>'Painting Submission',      'date'=>'18 Jun 2026', 'badge'=>'bhw'],
      ['label'=>'Art History Test',         'date'=>'25 Jun 2026', 'badge'=>'bts'],
      ['label'=>'Portfolio Review',         'date'=>'04 Jul 2026', 'badge'=>'bqz'],
    ],
  ],
];

// Validate course code
if (!$code || !isset($CATALOGUE[$code])) {
  redirect('studentHomePage.php?tab=courses');
}

$c = $CATALOGUE[$code];

// ── Pull real uploaded materials for this course, filtered to this student's year ──
$dbMaterials = [];
$studentYear = (int) ($user['year'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM course_materials WHERE course_code = ? AND (target_year IS NULL OR target_year = ?) ORDER BY created_at DESC");
$stmt->bind_param('si', $code, $studentYear);
$stmt->execute();
$mres = $stmt->get_result();
while ($r = $mres->fetch_assoc()) {
    $dbMaterials[] = $r;
}
$stmt->close();

// ── Pull real class schedule from database timetable (Requirement 2) ─────────
if ($studentYear <= 6) $yg = '1-6';
elseif ($studentYear <= 9) $yg = '7-9';
else $yg = '10-11';

if ($yg === '1-6') {
    $periodTimes = [
        'p1' => '8:00–9:00',
        'p2' => '9:00–10:00',
        'p3' => '10:00–10:45 (Break)',
        'p4' => '10:45–11:45',
        'p5' => '11:45–12:45',
    ];
} else {
    $periodTimes = [
        'p1' => '8:00–8:45',
        'p2' => '8:45–9:30',
        'p3' => '9:30–10:00',
        'p4' => '10:00–11:00 (Break)',
        'p5' => '11:00–11:45',
        'p6' => '11:45–12:30',
        'p7' => '12:30–1:15',
    ];
}

$courseKeywordsMap = [
    'BM101' => ['BM', 'BAHASA MALAYSIA', 'BAHASA MELAYU', 'MALAY'],
    'MT102' => ['MATH', 'MATHEMATICS', 'MATEMATIK', 'MT'],
    'SC103' => ['SCI', 'SCIENCE', 'SAINS', 'SC'],
    'EN104' => ['ENG', 'ENGLISH', 'EN'],
    'HI105' => ['HIS', 'HISTORY', 'SEJARAH', 'HI'],
    'GE106' => ['GEO', 'GEOGRAPHY', 'GEOGRAFI', 'GE'],
    'PE107' => ['SPORT', 'PE', 'PJ', 'PENDIDIKAN JASMANI'],
    'AR108' => ['ART', 'SENI', 'PENDIDIKAN SENI', 'AR'],
];
$kwList = $courseKeywordsMap[$code] ?? [strtoupper($c['name']), strtoupper($c['code'])];

$dbSchedule = [];
if ($conn) {
    $ttStmt = $conn->prepare("SELECT * FROM timetable WHERE year = ? AND year_group = ? ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday')");
    $ttStmt->bind_param('is', $studentYear, $yg);
    $ttStmt->execute();
    $ttRes = $ttStmt->get_result();
    while ($ttRow = $ttRes->fetch_assoc()) {
        $day = $ttRow['day'];
        foreach ($periodTimes as $pkey => $timeSlot) {
            $slotVal = trim(strtoupper($ttRow[$pkey] ?? ''));
            if ($slotVal === '' || $slotVal === 'BREAK') continue;
            foreach ($kwList as $kw) {
                if ($slotVal === $kw || strpos($slotVal, $kw) !== false) {
                    $dbSchedule[] = "$day $timeSlot";
                    break;
                }
            }
        }
    }
    $ttStmt->close();
}

// ── Pull real upcoming deadlines from database homework (Requirement 2) ──────
$dbUpcoming = [];
if ($conn && $userId > 0) {
    $subjectIds = [];
    $sRes = $conn->query("SELECT id, name FROM subjects");
    if ($sRes) {
        while ($sRow = $sRes->fetch_assoc()) {
            $sName = strtoupper($sRow['name']);
            foreach ($kwList as $kw) {
                if ($sName === $kw || strpos($sName, $kw) !== false) {
                    $subjectIds[] = (int) $sRow['id'];
                    break;
                }
            }
        }
    }

    $whereConditions = ["h.student_id = " . (int)$userId];
    $matchConditions = [];
    if (!empty($subjectIds)) {
        $matchConditions[] = "h.subject_id IN (" . implode(',', $subjectIds) . ")";
    }
    foreach ($kwList as $kw) {
        $kwEsc = $conn->real_escape_string($kw);
        $matchConditions[] = "UPPER(h.title) LIKE '%$kwEsc%'";
    }
    if (!empty($matchConditions)) {
        $whereConditions[] = "(" . implode(' OR ', $matchConditions) . ")";
    }
    $whereSql = implode(' AND ', $whereConditions);

    $hwRes = $conn->query("SELECT h.* FROM homework h WHERE $whereSql ORDER BY h.due_date ASC");
    if ($hwRes && $hwRes->num_rows > 0) {
        while ($h = $hwRes->fetch_assoc()) {
            $type = $h['type'];
            $badge = ($type === 'Quiz') ? 'bqz' : (($type === 'Test') ? 'bts' : 'bhw');
            $dbUpcoming[] = [
                'id' => (int) $h['id'],
                'label' => $h['title'],
                'date' => date('d M Y', strtotime($h['due_date'])),
                'badge' => $badge,
                'type' => $type,
                'status' => $h['status'],
                'submission_file' => $h['submission_file'] ?? null,
            ];
        }
    }
}

// ── Pull course-specific reminders from teacher (Requirements 8 & 9) ─────────
$courseReminders = [];
if ($conn && $userId > 0) {
    $rStmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND course_code = ? ORDER BY created_at DESC LIMIT 5");
    $rStmt->bind_param('is', $userId, $code);
    $rStmt->execute();
    $rRes = $rStmt->get_result();
    while ($r = $rRes->fetch_assoc()) {
        $courseReminders[] = $r;
    }
    $rStmt->close();
}

pageHead('Course — ' . $c['name']);
?>

<style>
/* ── Course page overrides ───────────────────────────────────────────────── */
.cp-hero {
  background: linear-gradient(135deg, <?= $c['color'] ?> 0%, #fff 100%);
  border: 1.5px solid var(--W);
  border-radius: 16px;
  padding: 1.8rem 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1.4rem;
  flex-wrap: wrap;
}
.cp-bigicon {
  font-size: 3rem;
  width: 72px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: <?= $c['color'] ?>;
  border-radius: 18px;
  flex-shrink: 0;
}
.cp-hero-info { flex: 1; min-width: 180px; }
.cp-hero-info h2 {
  font-size: 1.45rem;
  font-weight: 800;
  color: var(--F);
  margin: 0 0 .18rem;
}
.cp-hero-info .cp-code {
  display: inline-block;
  background: <?= $c['color'] ?>;
  color: <?= $c['accent'] ?>;
  font-size: .72rem;
  font-weight: 700;
  padding: .2rem .65rem;
  border-radius: 99px;
  margin-bottom: .5rem;
  letter-spacing: .04em;
}
.cp-hero-info .cp-teacher {
  font-size: .83rem;
  color: var(--MT);
  display: flex;
  align-items: center;
  gap: .4rem;
}

/* section grid */
.cp-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.2rem;
  margin-bottom: 1.2rem;
}
@media (max-width: 700px) { .cp-grid { grid-template-columns: 1fr; } }

/* card sections */
.cp-section {
  background: #fff;
  border: 1.5px solid var(--W);
  border-radius: 14px;
  overflow: hidden;
}
.cp-section-hdr {
  padding: .75rem 1.2rem;
  border-bottom: 1.5px solid var(--W);
  font-size: .8rem;
  font-weight: 700;
  color: var(--F);
  display: flex;
  align-items: center;
  gap: .5rem;
  background: #fafaf7;
}
.cp-section-body { padding: 1rem 1.2rem; }

/* topics list */
.cp-topic-item {
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .45rem 0;
  border-bottom: 1px solid var(--W);
  font-size: .82rem;
  color: var(--F);
}
.cp-topic-item:last-child { border-bottom: none; }
.cp-topic-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: <?= $c['accent'] ?>;
  flex-shrink: 0;
}

/* schedule */
.cp-sch-item {
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .42rem 0;
  border-bottom: 1px solid var(--W);
  font-size: .8rem;
  color: var(--MT);
}
.cp-sch-item:last-child { border-bottom: none; }
.cp-sch-item i { color: <?= $c['accent'] ?>; width: 16px; text-align: center; }

/* materials */
.cp-mat-item {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .52rem .65rem;
  border-radius: 10px;
  margin-bottom: .45rem;
  background: var(--BG, #f9f6f0);
  cursor: pointer;
  transition: background .15s;
  text-decoration: none;
}
.cp-mat-item:hover { background: var(--W); }
.cp-mat-item:last-child { margin-bottom: 0; }
.cp-mat-ico {
  width: 34px; height: 34px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: .9rem;
  flex-shrink: 0;
}
.cp-mat-name {
  flex: 1;
  font-size: .8rem;
  font-weight: 600;
  color: var(--F);
}
.cp-mat-type {
  font-size: .67rem;
  font-weight: 700;
  color: var(--MT);
  text-transform: uppercase;
  letter-spacing: .04em;
}

/* upcoming */
.cp-up-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .5rem 0;
  border-bottom: 1px solid var(--W);
  gap: .5rem;
  flex-wrap: wrap;
}
.cp-up-item:last-child { border-bottom: none; }
.cp-up-label { font-size: .82rem; font-weight: 600; color: var(--F); }
.cp-up-date  { font-size: .75rem; color: var(--MT); display: flex; align-items: center; gap: .3rem; }

/* desc box */
.cp-desc {
  background: #fff;
  border: 1.5px solid var(--W);
  border-radius: 14px;
  padding: 1.1rem 1.4rem;
  font-size: .84rem;
  color: var(--MT);
  line-height: 1.7;
  margin-bottom: 1.2rem;
}

/* back btn */
.cp-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  font-size: .8rem;
  font-weight: 700;
  color: var(--MT);
  text-decoration: none;
  margin-bottom: 1.1rem;
  padding: .38rem .85rem;
  border: 1.5px solid var(--W);
  border-radius: 8px;
  background: #fff;
  transition: background .15s, color .15s;
}
.cp-back:hover { background: var(--W); color: var(--F); }
</style>

<div id="student" class="cyber-hud-mode">
  <?php
  $notifs = getNotifications($conn, $userId);
  $unreadCount = getUnreadCount($conn, $userId);
  ?>
  <header class="hdr s-hdr">
    <div class="hbrand">
      <img src="maple_wood_logo.png" alt="Maplewood Logo" class="hlogo">
      <h1>Maple<em>wood</em></h1>
    </div>
    <div class="hright">
      <button type="button" class="hud-toggle-btn" id="hudToggleBtn" onclick="mwToggleCyberHud()" title="Toggle Cyberpunk Game HUD Theme">
        <i class="fas fa-crosshairs"></i> <span id="hudToggleTxt">HUD MODE: ON</span>
      </button>
      <div class="ubadge">
        <div class="av"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div>
        <span><?= e($user['name']) ?> &nbsp;·&nbsp; Year <?= e($user['year']) ?></span>
      </div>
      <button type="button" class="tour-btn" onclick="mwStartTour()" title="Start Tour"><i class="fas fa-compass"></i> Tour</button>
      <a href="logout.php" class="obtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <div class="nbell" id="bellWrap">
        <button class="nbtn" onclick="toggleNotifs()" title="Notifications">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
          <?php if ($unreadCount > 0): ?>
            <span class="nbadge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
          <?php endif; ?>
        </button>
        <div class="ndrop" id="notifDrop" style="display:none">
          <div class="ndhdr">
            <span><i class="fas fa-bell"></i> Notifications</span>
          </div>
          <div class="ndlist">
            <?php if (empty($notifs)): ?>
              <div class="ndempty"><i class="fas fa-check-circle"></i> You're all caught up!</div>
            <?php else: ?>
              <?php foreach ($notifs as $n):
                $cCode = $n['course_code'] ?? '';
                $sender = $n['sender_name'] ?? 'Teacher';
                $nDate = date('d M Y, g:ia', strtotime($n['created_at']));
                $isCourse = !empty($cCode);
              ?>
                <div class="nditem <?= $n['is_read'] ? '' : 'ndunread' ?>"
                  <?php if ($isCourse): ?>
                    onclick="mwShowReminder(<?= $n['id'] ?>, <?= htmlspecialchars(json_encode($n['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($n['message']), ENT_QUOTES) ?>, '<?= e($cCode) ?>', '<?= e($sender) ?>', '<?= e($nDate) ?>')"
                    style="cursor:pointer"
                  <?php endif; ?>
                >
                  <div class="ndicn"><i class="fas <?= $isCourse ? 'fa-bell' : 'fa-chart-bar' ?>" style="<?= $isCourse ? 'color:#d97706' : '' ?>"></i></div>
                  <div class="ndcnt">
                    <?php if ($isCourse): ?>
                      <span class="course-rem-badge" style="background:#fef3c7;color:#92400e;margin-bottom:.25rem"><i class="fas fa-book"></i> <?= e($cCode) ?> · Click to view</span>
                    <?php endif; ?>
                    <div class="ndtitle"><?= e($n['title']) ?></div>
                    <div class="ndmsg"><?= e($n['message']) ?></div>
                    <div class="ndtime"><i class="fas fa-clock"></i> <?= date('d M Y, g:i a', strtotime($n['created_at'])) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="dlayout">
    <!-- SIDEBAR -->
    <aside class="sb s-sb">
      <button class="sbToggle" onclick="mwToggleSidebar()" title="Collapse menu">
        <span class="toggle-text">Collapse Menu</span>
        <i class="fas fa-angles-left toggle-icon"></i>
      </button>
      <span class="slbl s-slbl">Menu</span>
      <a class="nl s-nl <?= $tab === 'courses' ? 'on' : '' ?>" href="studentHomePage.php?tab=courses" title="Courses"> <i class="fas fa-book-open"></i>
        <span class="nl-label">Courses</span></a>
      <a class="nl s-nl <?= $tab === 'timetable' ? 'on' : '' ?>" href="studentHomePage.php?tab=timetable" title="Timetable"> <i
          class="fas fa-calendar-alt"></i>
        <span class="nl-label">Timetable</span></a>
      <a class="nl s-nl <?= in_array($tab, ['marks', 'grades']) ? 'on' : '' ?>" href="studentHomePage.php?tab=marks" title="Marks & Grade Prediction"> <i
          class="fas fa-chart-line"></i>
        <span class="nl-label">Marks &amp; Prediction</span></a>
      <a class="nl s-nl <?= $tab === 'homework' ? 'on' : '' ?>" href="studentHomePage.php?tab=homework" title="Homework Status"> <i class="fas fa-tasks"></i>
        <span class="nl-label">Homework Status</span></a>
      <a class="nl s-nl" href="#" onclick="mwOpenCalendar();return false;" title="Calendar Planner"> <i class="fas fa-calendar-check"></i>
        <span class="nl-label">Calendar Planner</span></a>
      <a class="nl s-nl <?= $tab === 'payment' ? 'on' : '' ?>" href="studentHomePage.php?tab=payment" title="Payment"> <i class="fas fa-credit-card"></i>
        <span class="nl-label">Payment</span></a>

      <div class="twid">
        <span class="wtl"><i class="fas fa-check-square"></i> To-Do</span>
        <?php
        $todoItems = [];
        $todoStmt = $conn->prepare("SELECT id, title FROM homework WHERE student_id = ? AND status != 'submitted' ORDER BY due_date ASC LIMIT 6");
        $todoStmt->bind_param('i', $userId);
        $todoStmt->execute();
        $todoRes = $todoStmt->get_result();
        while ($row = $todoRes->fetch_assoc()) $todoItems[] = $row;
        $todoStmt->close();
        ?>
        <div id="todoList">
          <?php if (empty($todoItems)): ?>
            <p style="font-size:.78rem;color:var(--MT);padding:.3rem 0">All caught up! 🎉</p>
          <?php else: ?>
            <?php foreach ($todoItems as $t): ?>
              <label class="titem" id="todo-<?= $t['id'] ?>">
                <input type="checkbox" onchange="mwCompleteTodo(<?= $t['id'] ?>, this)"> <?= e($t['title']) ?>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Mini Calendar -->
      <div class="cwid" style="margin-top:1.2rem">
        <span class="wtl"><i class="fas fa-calendar"></i> <span id="calMonthYear">June 2026</span></span>
        <div class="calgrid">
          <div class="calmo" id="calMonthDisplay">June 2026</div>
          <div class="caldays" id="calendarContainer">
            <!-- Generated by JavaScript -->
          </div>
        </div>
        <button type="button" class="cbtn-planner" onclick="mwOpenCalendar()"><i class="fas fa-expand"></i> Fullscreen Planner</button>
      </div>

            <?php
      // Count real classes per weekday from this student's timetable
      $classCounts = [];
      foreach ($TIMETABLE as $row) {
          $periods = array_filter([
              $row['p1'] ?? '', $row['p2'] ?? '', $row['p3'] ?? '', $row['p4'] ?? '',
              $row['p5'] ?? '', $row['p6'] ?? '', $row['p7'] ?? ''
          ], fn($p) => $p !== '' && strtoupper($p) !== 'BREAK');
          $classCounts[$row['day']] = count($periods);
      }

      // Malaysia public holidays 2026 (national/federal level)
      $publicHolidays2026 = [
          '2026-01-01' => "New Year's Day",
          '2026-02-17' => "Chinese New Year (Day 1)",
          '2026-02-18' => "Chinese New Year (Day 2)",
          '2026-03-21' => "Hari Raya Aidilfitri (Day 1)",
          '2026-03-22' => "Hari Raya Aidilfitri (Day 2)",
          '2026-05-01' => "Labour Day",
          '2026-05-27' => "Hari Raya Haji (Day 1)",
          '2026-05-28' => "Hari Raya Haji (Day 2)",
          '2026-05-31' => "Wesak Day",
          '2026-06-01' => "Agong's Birthday",
          '2026-06-17' => "Awal Muharram",
          '2026-08-25' => "Maulidur Rasul",
          '2026-08-31' => "Merdeka Day",
          '2026-09-16' => "Malaysia Day",
          '2026-11-08' => "Deepavali",
          '2026-11-09' => "Deepavali (Replacement)",
          '2026-12-25' => "Christmas Day",
      ];
      ?>
      <script>
        const mwClassCounts = <?= json_encode($classCounts) ?>;
        const mwHolidays = <?= json_encode($publicHolidays2026) ?>;
        const mwWeekdayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        function generateCalendar() {
          const now = new Date();
          const year = now.getFullYear();
          const month = now.getMonth();
          const today = now.getDate();

          const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
          const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

          document.getElementById('calMonthYear').textContent = shortMonths[month] + ' ' + year;
          document.getElementById('calMonthDisplay').textContent = monthNames[month] + ' ' + year;

          const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
          const calendar = document.getElementById('calendarContainer');
          calendar.innerHTML = '';

          dayNames.forEach(day => {
            const dayEl = document.createElement('div');
            dayEl.className = 'dname';
            dayEl.textContent = day;
            calendar.appendChild(dayEl);
          });

          const firstDay = new Date(year, month, 1).getDay();
          const daysInMonth = new Date(year, month + 1, 0).getDate();

          for (let i = 0; i < firstDay; i++) {
            calendar.appendChild(document.createElement('div'));
          }

          for (let d = 1; d <= daysInMonth; d++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'cday';

            const weekdayName = mwWeekdayNames[new Date(year, month, d).getDay()];
            const iso = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            let tooltipParts = [];

            if (mwHolidays[iso]) {
              dayEl.classList.add('choliday');
              tooltipParts.push('🎉 ' + mwHolidays[iso]);
            } else if (mwClassCounts.hasOwnProperty(weekdayName)) {
              const count = mwClassCounts[weekdayName];
              tooltipParts.push(count > 0 ? (count + ' class' + (count > 1 ? 'es' : '') + ' today') : 'No classes');
            }

            if (tooltipParts.length > 0) {
              dayEl.title = tooltipParts.join(' • ');
            }

            if (d === today) {
              dayEl.classList.add('ctoday');
            }
            dayEl.textContent = d;
            calendar.appendChild(dayEl);
          }
        }

        generateCalendar();
        setInterval(generateCalendar, 60000);
      </script>
      </aside>

    <!-- MAIN CONTENT -->
    <main class="mc s-mc">

      <a href="studentHomePage.php?tab=courses" class="cp-back">
        <i class="fas fa-arrow-left"></i> Back to Courses
      </a>

      <!-- Hero -->
      <div class="cp-hero">
        <div class="cp-bigicon"><?= $c['icon'] ?></div>
        <div class="cp-hero-info">
          <span class="cp-code"><?= e($c['code']) ?></span>
          <h2><?= e($c['name']) ?></h2>
          <div class="cp-teacher">
            <i class="fas fa-chalkboard-teacher"></i>
            <?= e($c['teacher']) ?>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="cp-desc">
        <i class="fas fa-info-circle" style="color:<?= $c['accent'] ?>;margin-right:.45rem"></i>
        <?= e($c['desc']) ?>
      </div>

      <!-- Course Reminders from Teacher (Requirements 8 & 9) -->
      <?php if (!empty($courseReminders)): ?>
        <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:14px;padding:1.1rem 1.4rem;margin-bottom:1.3rem">
          <div style="display:flex;align-items:center;gap:.5rem;font-weight:700;color:#92400e;font-size:.9rem;margin-bottom:.6rem">
            <i class="fas fa-bell"></i> Teacher Reminders for <?= e($c['name']) ?>
          </div>
          <?php foreach ($courseReminders as $rem): ?>
            <div style="padding:.5rem 0;border-bottom:1px solid #fef3c7;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
              <div>
                <strong style="font-size:.85rem;color:var(--F)"><?= e($rem['title']) ?></strong>
                <p style="font-size:.8rem;color:var(--MT);margin-top:.2rem"><?= e($rem['message']) ?></p>
              </div>
              <span style="font-size:.72rem;color:var(--MT);white-space:nowrap"><i class="fas fa-clock"></i> <?= date('d M Y, g:ia', strtotime($rem['created_at'])) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- 2-col grid -->
      <div class="cp-grid">

        <!-- Topics -->
        <div class="cp-section">
          <div class="cp-section-hdr" style="color:<?= $c['accent'] ?>">
            <i class="fas fa-list-ul"></i> Topics Covered
          </div>
          <div class="cp-section-body">
            <?php foreach ($c['topics'] as $topic): ?>
              <div class="cp-topic-item">
                <div class="cp-topic-dot"></div>
                <?= e($topic) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Schedule (Requirement 2: Follow Database) -->
        <div class="cp-section">
          <div class="cp-section-hdr" style="color:<?= $c['accent'] ?>">
            <i class="fas fa-clock"></i> Class Schedule
            <?php if (!empty($dbSchedule)): ?>
              <span style="font-size:.68rem;background:rgba(45,106,79,.1);color:var(--S);padding:.15rem .5rem;border-radius:12px;margin-left:auto;font-weight:700">From Timetable DB</span>
            <?php endif; ?>
          </div>
          <div class="cp-section-body">
            <?php
            $finalSchedule = !empty($dbSchedule) ? $dbSchedule : $c['schedule'];
            if (empty($finalSchedule)):
            ?>
              <p style="color:var(--MT);font-size:.82rem">No schedule assigned in timetable yet.</p>
            <?php else: ?>
              <?php foreach ($finalSchedule as $sch): ?>
                <div class="cp-sch-item">
                  <i class="fas fa-calendar-day"></i>
                  <?= e($sch) ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

               <!-- Course Materials -->
        <div class="cp-section">
          <div class="cp-section-hdr" style="color:<?= $c['accent'] ?>">
            <i class="fas fa-folder-open"></i> Course Materials
          </div>
          <div class="cp-section-body">
            <?php if (empty($dbMaterials)): ?>
              <p style="color:var(--MT);font-size:.82rem">No materials uploaded yet. Check back soon!</p>
            <?php else: ?>
              <?php foreach ($dbMaterials as $mat):
                $ftype = strtolower($mat['file_type']);
                if ($ftype === 'pdf') { $ico = 'fas fa-file-pdf'; }
                elseif (in_array($ftype, ['doc', 'docx'])) { $ico = 'fas fa-file-word'; }
                elseif (in_array($ftype, ['ppt', 'pptx'])) { $ico = 'fas fa-file-powerpoint'; }
                else { $ico = 'fas fa-file-excel'; }
              ?>
                <a href="download_material.php?id=<?= (int) $mat['id'] ?>" class="cp-mat-item">
                  <div class="cp-mat-ico" style="background:<?= $c['color'] ?>;color:<?= $c['accent'] ?>">
                    <i class="<?= $ico ?>"></i>
                  </div>
                  <span class="cp-mat-name"><?= e($mat['title']) ?></span>
                  <span class="cp-mat-type"><?= e($mat['file_type']) ?></span>
                  <i class="fas fa-download" style="color:var(--MT);font-size:.75rem"></i>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <!-- Upcoming Deadlines (Requirement 2: Follow Database) -->
        <div class="cp-section">
          <div class="cp-section-hdr" style="color:<?= $c['accent'] ?>">
            <i class="fas fa-calendar-check"></i> Upcoming Deadlines
            <?php if (!empty($dbUpcoming)): ?>
              <span style="font-size:.68rem;background:rgba(212,168,71,.15);color:#8a6200;padding:.15rem .5rem;border-radius:12px;margin-left:auto;font-weight:700">From Homework DB</span>
            <?php endif; ?>
          </div>
          <div class="cp-section-body">
            <?php
            $finalUpcoming = !empty($dbUpcoming) ? $dbUpcoming : $c['upcoming'];
            if (empty($finalUpcoming)):
            ?>
              <p style="color:var(--MT);font-size:.82rem">No upcoming deadlines recorded for this course 🎉</p>
            <?php else: ?>
              <?php foreach ($finalUpcoming as $up): ?>
                <div class="cp-up-item">
                  <div>
                    <div class="cp-up-label"><?= e($up['label']) ?></div>
                    <div class="cp-up-date"><i class="fas fa-calendar"></i> <?= e($up['date']) ?>
                      <?php if (isset($up['status']) && $up['status'] === 'submitted'): ?>
                        &nbsp;·&nbsp; <span style="color:#1b5e20;font-weight:700"><i class="fas fa-check-circle"></i> Submitted</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div style="display:flex;align-items:center;gap:.5rem">
                    <?php
                      $cls = $up['badge'] ?? 'bhw';
                      $labels = ['bhw'=>'Homework','bqz'=>'Quiz','bts'=>'Test'];
                      $typeText = $labels[$cls] ?? ($up['type'] ?? 'Assignment');
                      echo "<span class=\"{$cls}\">{$typeText}</span>";
                    ?>
                    <?php if (isset($up['id']) && (!isset($up['status']) || $up['status'] !== 'submitted')): ?>
                      <a href="studentHomePage.php?tab=homework&upload=<?= (int)$up['id'] ?>" class="hw-upload-btn" style="padding:.2rem .55rem;font-size:.7rem"><i class="fas fa-upload"></i> Submit</a>
                    <?php elseif (isset($up['submission_file']) && $up['submission_file']): ?>
                      <a href="download_submission.php?id=<?= (int)$up['id'] ?>" class="hw-file-badge" style="padding:.18rem .45rem;font-size:.68rem" title="Download your submission"><i class="fas fa-download"></i> File</a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /.cp-grid -->

    </main>
  </div>
</div>

<script>
  function toggleNotifs() {
    var d = document.getElementById('notifDrop');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
  }
  document.addEventListener('click', function(e) {
    var wrap = document.getElementById('bellWrap');
    if (wrap && !wrap.contains(e.target)) {
      var d = document.getElementById('notifDrop');
      if (d) d.style.display = 'none';
    }
  });
</script>

<?php pageFoot(); ?>
