<?php
require_once 'config.php';
require_once 'layout.php';
requireAuth('student');

$user     = $_SESSION['user'];
$userId   = $_SESSION['user']['id'] ?? null;
$code     = $_GET['code'] ?? '';

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
  'GE106' => [
    'code'    => 'GE106',
    'name'    => 'Geography',
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

<div id="student">
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
      <div class="ubadge">
        <div class="av"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div>
        <span><?= e($user['name']) ?> &nbsp;·&nbsp; Year <?= e($user['year']) ?></span>
      </div>
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
              <?php foreach ($notifs as $n): ?>
                <div class="nditem <?= $n['is_read'] ? '' : 'ndunread' ?>">
                  <div class="ndicn"><i class="fas fa-chart-bar"></i></div>
                  <div class="ndcnt">
                    <div class="ndtitle"><?= e($n['title']) ?></div>
                    <div class="ndmsg"><?= e($n['message']) ?></div>
                    <div class="ndtime"><i class="fas fa-clock"></i>
                      <?= date('d M Y, g:i a', strtotime($n['created_at'])) ?></div>
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
    <!-- SIDEBAR (same as dashboard) -->
    <aside class="sb s-sb">
      <span class="slbl s-slbl">Menu</span>
      <a class="nl s-nl on" href="studentHomePage.php?tab=courses"> <i class="fas fa-book-open"></i> Courses</a>
      <a class="nl s-nl" href="studentHomePage.php?tab=timetable"> <i class="fas fa-calendar-alt"></i> Timetable</a>
      <a class="nl s-nl" href="studentHomePage.php?tab=homework">  <i class="fas fa-tasks"></i> Homework Status</a>
      <a class="nl s-nl" href="studentHomePage.php?tab=payment">   <i class="fas fa-credit-card"></i> Payment</a>

      <div class="twid">
        <span class="wtl"><i class="fas fa-check-square"></i> To-Do</span>
        <?php foreach (['Review Math notes', 'Submit BM essay', 'Study for History', 'Prepare sports kit', 'Complete Geo report'] as $i => $t): ?>
          <label class="titem"><input type="checkbox" <?= $i === 0 ? 'checked' : '' ?>> <?= e($t) ?></label>
        <?php endforeach; ?>
      </div>

      <div class="cwid" style="margin-top:1.2rem">
        <span class="wtl"><i class="fas fa-calendar"></i> <span id="calMonthYear"></span></span>
        <div class="calgrid">
          <div class="calmo" id="calMonthDisplay"></div>
          <div class="caldays" id="calendarContainer"></div>
        </div>
      </div>
      <script>
        (function generateCalendar() {
          const now = new Date(), year = now.getFullYear(), month = now.getMonth(), today = now.getDate();
          const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
          const shortMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
          document.getElementById('calMonthYear').textContent = shortMonths[month] + ' ' + year;
          document.getElementById('calMonthDisplay').textContent = monthNames[month] + ' ' + year;
          const dayNames = ['Su','Mo','Tu','We','Th','Fr','Sa'];
          const cal = document.getElementById('calendarContainer');
          cal.innerHTML = '';
          dayNames.forEach(d => { const el = document.createElement('div'); el.className='dname'; el.textContent=d; cal.appendChild(el); });
          const firstDay = new Date(year, month, 1).getDay();
          const daysInMonth = new Date(year, month + 1, 0).getDate();
          for (let i = 0; i < firstDay; i++) cal.appendChild(document.createElement('div'));
          for (let d = 1; d <= daysInMonth; d++) {
            const el = document.createElement('div'); el.className='cday';
            if (d === today) el.classList.add('ctoday');
            el.textContent = d; cal.appendChild(el);
          }
        })();
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

        <!-- Schedule -->
        <div class="cp-section">
          <div class="cp-section-hdr" style="color:<?= $c['accent'] ?>">
            <i class="fas fa-clock"></i> Class Schedule
          </div>
          <div class="cp-section-body">
            <?php foreach ($c['schedule'] as $sch): ?>
              <div class="cp-sch-item">
                <i class="fas fa-calendar-day"></i>
                <?= e($sch) ?>
              </div>
            <?php endforeach; ?>
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
        <!-- Upcoming -->
        <div class="cp-section">
          <div class="cp-section-hdr" style="color:<?= $c['accent'] ?>">
            <i class="fas fa-calendar-check"></i> Upcoming Deadlines
          </div>
          <div class="cp-section-body">
            <?php foreach ($c['upcoming'] as $up): ?>
              <div class="cp-up-item">
                <div>
                  <div class="cp-up-label"><?= e($up['label']) ?></div>
                  <div class="cp-up-date"><i class="fas fa-calendar"></i> <?= e($up['date']) ?></div>
                </div>
                <?php
                  $cls = $up['badge'];
                  $labels = ['bhw'=>'Homework','bqz'=>'Quiz','bts'=>'Test'];
                  echo "<span class=\"{$cls}\">{$labels[$cls]}</span>";
                ?>
              </div>
            <?php endforeach; ?>
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
