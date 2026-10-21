<?php
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 186400) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
$_SESSION['last_activity'] = time();

require_once 'config.php';
require_once 'layout.php';
requireAuth('student');

// Prevent browser caching so payment status refreshes correctly when returning from success page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$user = $_SESSION['user'];
$userId = $_SESSION['user']['id'] ?? null;
$tab = $_GET['tab'] ?? 'courses';

// Handle flash messages
$toast = flash('toast');

// Mark all read when dropdown opened
if (isset($_GET['markread'])) {
  markAllRead($conn, $userId);
  redirect("studentHomePage.php?tab=$tab");
}

pageHead('Student Dashboard');
?>
<div id="student" class="cyber-hud-mode">
  <!-- HEADER -->
  <?php
  $notifs = getNotifications($conn, $userId);
  $unreadCount = getUnreadCount($conn, $userId);
  ?>
  <header class="hdr s-hdr">
    <div class="hbrand" id="hbrand">
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
      <button type="button" class="tour-btn" onclick="mwStartTour()" title="Start Guided Tour"><i class="fas fa-compass"></i> Tour</button>
      <a href="logout.php" class="obtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <div class="nbell" id="bellWrap">
        <!-- FIX: pass event to toggleNotifs to stop bubbling -->
        <button class="nbtn" onclick="toggleNotifs(event)" title="Notifications">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
          <?php if ($unreadCount > 0): ?>
            <span class="nbadge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
          <?php endif; ?>
        </button>

        <!-- Dropdown panel -->
        <div class="ndrop" id="notifDrop" style="display:none">
          <div class="ndhdr">
            <span><i class="fas fa-bell"></i> Notifications &amp; Reminders</span>
            <?php if ($unreadCount > 0): ?>
              <a href="?tab=<?= $tab ?>&markread=1" class="ndmark">Mark all read</a>
            <?php endif; ?>
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
    <!-- SIDEBAR -->
    <aside class="sb s-sb" id="sbMenu">
      <button class="sbToggle" onclick="mwToggleSidebar()" title="Collapse menu">
        <span class="toggle-text">Collapse Menu</span>
        <i class="fas fa-angles-left toggle-icon"></i>
      </button>
      <span class="slbl s-slbl">Menu</span>
      <a class="nl s-nl <?= $tab === 'courses' ? 'on' : '' ?>" href="?tab=courses" title="Courses"> <i class="fas fa-book-open"></i>
        <span class="nl-label">Courses</span></a>
      <a class="nl s-nl <?= $tab === 'timetable' ? 'on' : '' ?>" href="?tab=timetable" title="Timetable"> <i
          class="fas fa-calendar-alt"></i>
        <span class="nl-label">Timetable</span></a>
      <a class="nl s-nl <?= in_array($tab, ['marks', 'grades']) ? 'on' : '' ?>" href="?tab=marks" title="Marks & Grade Prediction"> <i
          class="fas fa-chart-line"></i>
        <span class="nl-label">Marks &amp; Prediction</span></a>
      <a class="nl s-nl <?= $tab === 'homework' ? 'on' : '' ?>" href="?tab=homework" title="Homework Status"> <i class="fas fa-tasks"></i>
        <span class="nl-label">Homework Status</span></a>
      <a class="nl s-nl <?= $tab === 'calendar' ? 'on' : '' ?>" href="?tab=calendar" title="Calendar Planner"> <i class="fas fa-calendar-check"></i>
        <span class="nl-label">Calendar Planner</span></a>
      <a class="nl s-nl <?= $tab === 'payment' ? 'on' : '' ?>" href="?tab=payment" title="Payment"> <i class="fas fa-credit-card"></i>
        <span class="nl-label">Payment</span></a>

     <!-- To-Do Widget (real homework from DB) -->
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
      <div class="cwid" id="calWrap" style="margin-top:1.2rem">
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

      <?php if ($toast): ?>
        <div class="toast"><i class="fas fa-check-circle"></i> <?= e($toast) ?></div>
      <?php endif; ?>

      <?php // variables $COURSES,$TIMETABLE,$HOMEWORK,$PAYMENTS are in scope from config.php ?>

      <!-- ── COURSES TAB ── -->
      <?php if ($tab === 'courses'): ?>
        <div class="phdr">
          <h2>Your Courses</h2>
          <p>Year <?= e($user['year']) ?> — Semester 1, 2026</p>
        </div>
          <?php
          // Cumulative percentage across all terms/subjects
          $cum_res = $conn->query(
            "SELECT SUM(marks) AS tm, SUM(max_marks) AS tx, COUNT(*) AS n
            FROM grades WHERE student_id = " . (int) $userId
          );
          $cum = $cum_res ? $cum_res->fetch_assoc() : null;
          $has_grades = $cum && (int) $cum['n'] > 0 && (float) $cum['tx'] > 0;
          $cum_pct = $has_grades ? round(((float) $cum['tm'] / (float) $cum['tx']) * 100, 1) : null;
          ?>
        <div class="srow">
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-book"></i></div>
            <div class="sv">8</div>
            <div class="sl">Enrolled</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-tasks"></i></div>
            <div class="sv">3</div>
            <div class="sl">Pending</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div>
            <div class="sv">12</div>
            <div class="sl">Submitted</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-star"></i></div>
            <div class="sv"><?= $has_grades ? $cum_pct . '%' : '—' ?></div>
            <div class="sl">Cumulative Marks</div>
          </div>
        </div>
        <div class="cgrid">
          <?php foreach ($COURSES as $c): ?>
            <a class="cc" href="coursePage.php?code=<?= urlencode($c['code']) ?>"
              style="text-decoration:none;display:block;color:inherit">
              <div class="cico"><?= $c['icon'] ?></div>
              <div class="cn"><?= e($c['code']) ?></div>
              <div class="cmeta"><?= e($c['name']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>

      <?php elseif ($tab === 'grades' || $tab === 'marks'): ?>
        <?php
        $yr = (int) $user['year'];
        if ($yr <= 6)
          $yg = '1-6';
        elseif ($yr <= 9)
          $yg = '7-9';
        else
          $yg = '10-11';

        $term = $_GET['term'] ?? 'Term 1';
        $terms = ['Term 1', 'Term 2', 'Term 3'];

        $sid = (int) $userId;
        $grades_res = $conn->query(
          "SELECT g.*, s.name AS subject_name
       FROM grades g
       JOIN subjects s ON g.subject_id = s.id
       WHERE g.student_id = $sid AND g.term = '{$conn->real_escape_string($term)}'
       ORDER BY s.name"
        );
        $my_grades = [];
        if ($grades_res)
          while ($r = $grades_res->fetch_assoc())
            $my_grades[] = $r;

        $total_marks = 0;
        $total_max = 0;
        $count = count($my_grades);
        foreach ($my_grades as $g) {
          $total_marks += $g['marks'];
          $total_max += $g['max_marks'];
        }
        $overall_pct = $total_max > 0 ? round(($total_marks / $total_max) * 100, 1) : 0;
        $overall_grade = $count > 0 ? calcGrade($total_marks, $total_max) : '—';
        $as = array_filter($my_grades, fn($g) => str_starts_with($g['grade'], 'A'));
        ?>
        <div class="phdr" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div>
            <h2>My Marks &amp; Academic Prediction</h2>
            <p>Year <?= e($user['year']) ?> — Subject Breakdown &amp; ML.NET Machine Learning Forecasting</p>
          </div>
          <a href="#mlPredictionSection" class="bpr bsm" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;padding:.5rem 1rem;background:linear-gradient(135deg,var(--P),var(--S));color:#fff;border-radius:10px;font-weight:700">
            <i class="fas fa-brain" style="color:var(--O)"></i> <span>AI Grade Predictor</span>
          </a>
        </div>

        <div style="display:flex;gap:.5rem;margin-bottom:1.2rem;flex-wrap:wrap;align-items:center">
          <span style="font-size:.78rem;font-weight:700;color:var(--MT);text-transform:uppercase;letter-spacing:.05em;margin-right:.25rem">Academic Term:</span>
          <?php foreach ($terms as $t): ?>
            <a href="?tab=<?= urlencode($tab) ?>&term=<?= urlencode($t) ?>" style="padding:.4rem 1.1rem;border-radius:8px;font-size:.82rem;font-weight:600;border:1.5px solid var(--W);text-decoration:none;
    background:<?= $t === $term ? 'var(--P)' : '#fff' ?>;
    color:<?= $t === $term ? '#fff' : 'var(--F)' ?>;box-shadow:<?= $t === $term ? '0 2px 8px rgba(15,35,24,.18)' : 'none' ?>">
              <?= e($t) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="srow" style="margin-bottom:1.2rem">
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-book-open"></i></div>
            <div class="sv"><?= $count ?></div>
            <div class="sl">Subjects Graded</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#e3f2fd;color:#0d47a1"><i class="fas fa-percentage"></i></div>
            <div class="sv"><?= $overall_pct ?>%</div>
            <div class="sl">Overall Term Score</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-award"></i></div>
            <div class="sv"><?= $overall_grade ?></div>
            <div class="sl">Overall Grade</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fce4ec;color:#880e4f"><i class="fas fa-trophy"></i></div>
            <div class="sv"><?= count($as) ?></div>
            <div class="sl">A-Grade Honors</div>
          </div>
        </div>

        <!-- SUBJECT MARKS TABLE -->
        <?php if (empty($my_grades)): ?>
          <div class="crd" style="padding:2.5rem;text-align:center;color:var(--MT);margin-bottom:1.5rem">
            <i class="fas fa-hourglass-half" style="font-size:2rem;margin-bottom:.75rem;display:block"></i>
            <strong>No marks released for <?= e($term) ?> yet.</strong>
            <p style="font-size:.85rem;margin-top:.35rem">Your teachers have not finalized grades for this term yet. You can still test the AI Grade Predictor below!</p>
          </div>
        <?php else: ?>
          <div class="crd" style="margin-bottom:1.6rem">
            <div class="crdhdr" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
              <h3><i class="fas fa-table-list"></i>&nbsp; <?= e($term) ?> Subject Marks &amp; Performance</h3>
              <span style="font-size:.76rem;color:var(--MT);background:#f4f1ea;padding:3px 10px;border-radius:20px;font-weight:600">
                <i class="fas fa-check-circle" style="color:#2d6a4f"></i> <?= $count ?> Subjects Recorded
              </span>
            </div>
            <div class="tw">
              <table>
                <thead>
                  <tr>
                    <th>Subject</th>
                    <th>Marks Scored</th>
                    <th>Percentage &amp; Progress</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th style="text-align:center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($my_grades as $g):
                    $pct = round(($g['marks'] / $g['max_marks']) * 100, 1);
                    $color = gradeColor($g['grade']);
                    ?>
                    <tr>
                      <td style="font-weight:600">
                        <i class="fas fa-bookmark" style="color:var(--S);margin-right:6px;font-size:.8rem"></i>
                        <?= e($g['subject_name']) ?>
                      </td>
                      <td style="font-family:'Playfair Display',serif;font-size:1.02rem;font-weight:700">
                        <?= $g['marks'] ?>
                        <span style="font-size:.74rem;color:var(--MT);font-family:'Plus Jakarta Sans',sans-serif"> / <?= $g['max_marks'] ?></span>
                      </td>
                      <td>
                        <div style="display:flex;align-items:center;gap:.6rem">
                          <div style="flex:1;height:8px;background:var(--W);border-radius:99px;overflow:hidden;min-width:85px">
                            <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct >= 80 ? '#2d6a4f' : ($pct >= 65 ? '#52b788' : ($pct >= 50 ? '#f4a261' : '#e63946')) ?>;border-radius:99px;transition:width .4s ease">
                            </div>
                          </div>
                          <span style="font-size:.8rem;font-weight:700;color:var(--F);min-width:42px"><?= $pct ?>%</span>
                        </div>
                      </td>
                      <td>
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;font-weight:800;font-size:1rem;background:<?= $color[0] ?>;color:<?= $color[1] ?>;box-shadow:0 2px 6px rgba(0,0,0,.06)">
                          <?= e($g['grade']) ?>
                        </span>
                      </td>
                      <td style="font-size:.8rem;font-weight:600">
                        <?php
                        if ($pct >= 80)
                          echo '<span style="color:#1b5e20;display:inline-flex;align-items:center;gap:4px"><i class="fas fa-arrow-up"></i> Excellent</span>';
                        elseif ($pct >= 65)
                          echo '<span style="color:#0d47a1;display:inline-flex;align-items:center;gap:4px"><i class="fas fa-check"></i> Good</span>';
                        elseif ($pct >= 50)
                          echo '<span style="color:#bf360c;display:inline-flex;align-items:center;gap:4px"><i class="fas fa-minus"></i> Average</span>';
                        else
                          echo '<span style="color:#b71c1c;display:inline-flex;align-items:center;gap:4px"><i class="fas fa-arrow-down"></i> Needs Focus</span>';
                        ?>
                      </td>
                      <td style="text-align:center">
                        <button type="button" onclick="mwSelectSubjectForPrediction('<?= e(addslashes($g['subject_name'])) ?>', <?= $pct ?>)"
                          style="background:rgba(45,106,79,.08);color:var(--S);border:1px solid rgba(45,106,79,.25);border-radius:8px;padding:.32rem .75rem;font-size:.74rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .2s"
                          onmouseover="this.style.background='var(--S)';this.style.color='#fff'"
                          onmouseout="this.style.background='rgba(45,106,79,.08)';this.style.color='var(--S)'"
                          title="Forecast next score for <?= e($g['subject_name']) ?>">
                          <i class="fas fa-wand-magic-sparkles"></i> Predict
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div style="padding:1rem 1.25rem;border-top:1.5px solid var(--W);background:#fafaf7;display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap">
              <span style="font-size:.82rem;font-weight:700;color:var(--F)">Overall <?= e($term) ?> Cumulative:</span>
              <div style="flex:1;height:10px;background:var(--W);border-radius:99px;overflow:hidden;min-width:120px">
                <div style="height:100%;width:<?= $overall_pct ?>%;background:linear-gradient(90deg,var(--P),#52b788);border-radius:99px;transition:width .5s ease"></div>
              </div>
              <span style="font-size:.9rem;font-weight:800;color:var(--P)"><?= $overall_pct ?>% &nbsp;·&nbsp; Grade <?= $overall_grade ?></span>
            </div>
          </div>
        <?php endif; ?>

        <!-- ── GRADE PREDICTION SYSTEM (ML.NET & C# ASP.NET CORE API) ── -->
        <div class="crd" id="mlPredictionSection" style="border:1.5px solid rgba(45,106,79,.25);box-shadow:0 8px 30px rgba(0,0,0,.08);overflow:hidden">
          <div class="crdhdr" style="background:linear-gradient(135deg,var(--F) 0%, #173826 100%);color:#fff;padding:1.2rem 1.4rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div>
              <div style="display:flex;align-items:center;gap:8px">
                <span style="background:var(--O);color:var(--F);font-size:.72rem;font-weight:800;padding:2px 8px;border-radius:4px;letter-spacing:.05em">ML.NET</span>
                <h3 style="font-size:1.15rem;font-weight:700;color:#fff;margin:0">Grade Prediction System</h3>
              </div>
              <p style="font-size:.78rem;color:var(--L);margin-top:3px">Powered by C# ASP.NET Core Web API with Microsoft ML.NET Regression Model</p>
            </div>
            <div id="mlApiStatusBadge" style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);padding:4px 12px;border-radius:30px;font-size:.74rem;font-weight:600">
              <i class="fas fa-circle-notch fa-spin"></i> Checking C# Service...
            </div>
          </div>

          <div style="padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1.75rem" class="ml-grid-layout">
            <!-- INPUT CONTROLS (LEFT) -->
            <div>
              <div style="font-size:.76rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--S);margin-bottom:.9rem;display:flex;align-items:center;gap:6px">
                <i class="fas fa-sliders"></i> Prediction Parameters
              </div>

              <!-- Scope / Subject Selector -->
              <div class="fg" style="margin-bottom:1.15rem">
                <label for="predSubjectSelect" style="font-size:.74rem;font-weight:700;color:var(--T);display:block;margin-bottom:4px">Target Subject / Evaluation Scope</label>
                <select id="predSubjectSelect" onchange="mwOnSubjectChange()" style="width:100%;padding:.7rem .9rem;border:1.5px solid var(--W);border-radius:10px;font-family:inherit;font-size:.88rem;background:#fff;outline:none">
                  <option value="Overall" data-prev="<?= $overall_pct > 0 ? $overall_pct : 80 ?>">Overall Term Academic Performance</option>
                  <?php foreach ($my_grades as $g):
                    $spct = round(($g['marks'] / $g['max_marks']) * 100, 1);
                  ?>
                    <option value="<?= e($g['subject_name']) ?>" data-prev="<?= $spct ?>">
                      <?= e($g['subject_name']) ?> (Current: <?= $spct ?>% · <?= e($g['grade']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Study Hours -->
              <div style="background:#fafaf8;border:1px solid var(--W);border-radius:12px;padding:1rem;margin-bottom:1rem">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem">
                  <label for="predStudyHoursNum" style="font-size:.74rem;font-weight:700;color:var(--T);text-transform:uppercase;letter-spacing:.04em">
                    <i class="fas fa-clock" style="color:var(--S)"></i> Weekly Study Hours
                  </label>
                  <div style="display:flex;align-items:center;gap:4px">
                    <input type="number" id="predStudyHoursNum" min="0" max="60" step="0.5" value="12" style="width:65px;padding:.3rem .45rem;border:1.5px solid var(--W);border-radius:8px;font-weight:700;font-size:.88rem;text-align:center" oninput="mwSyncStudyHours(this.value, 'num')">
                    <span style="font-size:.74rem;color:var(--MT);font-weight:600">hrs/wk</span>
                  </div>
                </div>
                <input type="range" id="predStudyHoursRange" min="0" max="40" step="0.5" value="12" style="width:100%;accent-color:var(--S);cursor:pointer" oninput="mwSyncStudyHours(this.value, 'range')">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--MT);margin-top:2px">
                  <span>0 hrs (Minimal)</span>
                  <span>10-15 hrs (Recommended)</span>
                  <span>40 hrs (Intensive)</span>
                </div>
              </div>

              <!-- Attendance Percentage -->
              <div style="background:#fafaf8;border:1px solid var(--W);border-radius:12px;padding:1rem;margin-bottom:1rem">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem">
                  <label for="predAttendanceNum" style="font-size:.74rem;font-weight:700;color:var(--T);text-transform:uppercase;letter-spacing:.04em">
                    <i class="fas fa-user-check" style="color:var(--S)"></i> Attendance Rate
                  </label>
                  <div style="display:flex;align-items:center;gap:4px">
                    <input type="number" id="predAttendanceNum" min="0" max="100" step="1" value="92" style="width:65px;padding:.3rem .45rem;border:1.5px solid var(--W);border-radius:8px;font-weight:700;font-size:.88rem;text-align:center" oninput="mwSyncAttendance(this.value, 'num')">
                    <span style="font-size:.74rem;color:var(--MT);font-weight:600">%</span>
                  </div>
                </div>
                <input type="range" id="predAttendanceRange" min="50" max="100" step="1" value="92" style="width:100%;accent-color:var(--S);cursor:pointer" oninput="mwSyncAttendance(this.value, 'range')">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--MT);margin-top:2px">
                  <span>50%</span>
                  <span>85% (Passing standard)</span>
                  <span>100%</span>
                </div>
              </div>

              <!-- Previous Grade Score -->
              <div style="background:#fafaf8;border:1px solid var(--W);border-radius:12px;padding:1rem;margin-bottom:1.15rem">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem">
                  <label for="predPreviousGradeNum" style="font-size:.74rem;font-weight:700;color:var(--T);text-transform:uppercase;letter-spacing:.04em">
                    <i class="fas fa-history" style="color:var(--S)"></i> Previous / Baseline Score
                  </label>
                  <div style="display:flex;align-items:center;gap:4px">
                    <input type="number" id="predPreviousGradeNum" min="0" max="100" step="0.5" value="<?= $overall_pct > 0 ? $overall_pct : 80 ?>" style="width:65px;padding:.3rem .45rem;border:1.5px solid var(--W);border-radius:8px;font-weight:700;font-size:.88rem;text-align:center" oninput="mwSyncPreviousGrade(this.value, 'num')">
                    <span style="font-size:.74rem;color:var(--MT);font-weight:600">%</span>
                  </div>
                </div>
                <input type="range" id="predPreviousGradeRange" min="0" max="100" step="0.5" value="<?= $overall_pct > 0 ? $overall_pct : 80 ?>" style="width:100%;accent-color:var(--S);cursor:pointer" oninput="mwSyncPreviousGrade(this.value, 'range')">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
                  <span style="font-size:.7rem;color:var(--MT)">Current average: <?= $overall_pct ?>%</span>
                  <button type="button" onclick="mwQuickFillStats(<?= $overall_pct > 0 ? $overall_pct : 80 ?>)" style="background:transparent;border:none;color:var(--S);font-size:.72rem;font-weight:700;cursor:pointer;padding:0;text-decoration:underline">
                    Use My Average
                  </button>
                </div>
              </div>

              <!-- Run Predict Action Button -->
              <button type="button" id="btnRunPrediction" onclick="mwRunPrediction()" class="pbtn" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:.9rem 1rem;font-size:.92rem;background:linear-gradient(135deg,var(--F) 0%,var(--S) 100%);box-shadow:0 4px 15px rgba(15,35,24,.2)">
                <i class="fas fa-wand-magic-sparkles" style="color:var(--O)"></i>
                <span id="btnRunPredictionTxt">Predict Grade with ML.NET</span>
              </button>
            </div>

            <!-- PREDICTION RESULTS (RIGHT) -->
            <div style="display:flex;flex-direction:column;gap:1.1rem">
              <div style="font-size:.76rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--S);display:flex;align-items:center;gap:6px">
                <i class="fas fa-chart-pie"></i> ML Inference Output
              </div>

              <!-- Primary Forecast Card -->
              <div style="background:linear-gradient(135deg,#fcfbf7 0%,#f4f1ea 100%);border:1.5px solid var(--W);border-radius:16px;padding:1.4rem;text-align:center;position:relative;box-shadow:inset 0 1px 3px rgba(255,255,255,.8)">
                <span id="predScopeBadge" style="display:inline-block;padding:3px 12px;background:rgba(45,106,79,.12);color:var(--F);font-size:.72rem;font-weight:700;border-radius:20px;margin-bottom:.75rem">
                  Overall Term Forecast
                </span>

                <div style="display:flex;align-items:center;justify-content:center;gap:1.25rem;margin-bottom:.5rem">
                  <!-- Large Score Display -->
                  <div>
                    <div id="predScoreVal" style="font-family:'Playfair Display',serif;font-size:3.2rem;font-weight:800;color:var(--F);line-height:1">
                      --.-%
                    </div>
                    <div style="font-size:.74rem;color:var(--MT);margin-top:4px;font-weight:600">Predicted Academic Score</div>
                  </div>

                  <!-- Grade Letter Badge -->
                  <div style="display:flex;flex-direction:column;align-items:center">
                    <span id="predGradeBadge" style="width:62px;height:62px;border-radius:14px;background:#e8f5e9;color:#1b5e20;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;box-shadow:0 4px 12px rgba(0,0,0,.08)">
                      --
                    </span>
                    <span style="font-size:.7rem;color:var(--MT);margin-top:4px;font-weight:600">Letter Grade</span>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div style="height:10px;background:var(--W);border-radius:99px;overflow:hidden;margin:1rem auto .6rem;max-width:320px">
                  <div id="predProgressFill" style="height:100%;width:0%;background:linear-gradient(90deg,var(--S),#52b788);border-radius:99px;transition:width .6s cubic-bezier(.34,1.56,.64,1)"></div>
                </div>

                <!-- Tier & Trend indicators -->
                <div style="display:flex;align-items:center;justify-content:center;gap:.75rem;flex-wrap:wrap;margin-top:.6rem">
                  <span id="predTierBadge" style="padding:3px 10px;border-radius:6px;background:#e3f2fd;color:#0d47a1;font-size:.74rem;font-weight:700">
                    Awaiting Inference
                  </span>
                  <span id="predDeltaBadge" style="padding:3px 10px;border-radius:6px;background:#f3f4f6;color:#374151;font-size:.74rem;font-weight:700">
                    --
                  </span>
                </div>
              </div>

              <!-- Recommendation & AI Insights Box -->
              <div style="background:#fff;border:1.5px solid var(--W);border-radius:14px;padding:1.15rem;box-shadow:0 2px 10px rgba(0,0,0,.04);flex:1">
                <div style="font-size:.76rem;font-weight:700;color:var(--T);margin-bottom:.6rem;display:flex;align-items:center;gap:6px">
                  <i class="fas fa-lightbulb" style="color:var(--O)"></i> ML Recommendations &amp; Insights
                </div>
                <div id="predInsightsList" style="display:flex;flex-direction:column;gap:.5rem;font-size:.8rem;color:var(--MT);line-height:1.5">
                  <div style="display:flex;align-items:flex-start;gap:6px">
                    <i class="fas fa-circle-check" style="color:var(--S);margin-top:3px;font-size:.75rem"></i>
                    <span>Adjust weekly study hours and attendance to see how your predicted score shifts.</span>
                  </div>
                </div>
              </div>

              <!-- Model Architecture Info Footer -->
              <div style="background:rgba(212,168,71,.08);border:1px solid rgba(212,168,71,.2);border-radius:10px;padding:.65rem .85rem;display:flex;align-items:center;justify-content:space-between;font-size:.72rem;color:var(--MT)">
                <span><i class="fas fa-microchip" style="color:#8a6200;margin-right:4px"></i> Engine: <strong id="predEngineLabel">C# ASP.NET Core API</strong></span>
                <span id="predServiceStatusNote" style="color:var(--S);font-weight:600"><i class="fas fa-plug"></i> http://localhost:5000/api/insights/predict</span>
              </div>
            </div>
          </div>
        </div>

        <script>
        // ── GRADE PREDICTOR INTERACTIVE SCRIPT ──
        function mwSyncStudyHours(val, source) {
          var f = parseFloat(val) || 0;
          if (source === 'range') document.getElementById('predStudyHoursNum').value = f;
          else document.getElementById('predStudyHoursRange').value = f;
        }

        function mwSyncAttendance(val, source) {
          var f = parseFloat(val) || 0;
          if (source === 'range') document.getElementById('predAttendanceNum').value = f;
          else document.getElementById('predAttendanceRange').value = f;
        }

        function mwSyncPreviousGrade(val, source) {
          var f = parseFloat(val) || 0;
          if (source === 'range') document.getElementById('predPreviousGradeNum').value = f;
          else document.getElementById('predPreviousGradeRange').value = f;
        }

        function mwOnSubjectChange() {
          var sel = document.getElementById('predSubjectSelect');
          var opt = sel.options[sel.selectedIndex];
          var prev = opt.getAttribute('data-prev');
          if (prev) {
            var val = parseFloat(prev);
            document.getElementById('predPreviousGradeNum').value = val;
            document.getElementById('predPreviousGradeRange').value = val;
          }
          var scopeTxt = sel.value === 'Overall' ? 'Overall Term Forecast' : (sel.value + ' Subject Forecast');
          document.getElementById('predScopeBadge').textContent = scopeTxt;
          mwRunPrediction();
        }

        function mwSelectSubjectForPrediction(subjectName, currentScore) {
          var sel = document.getElementById('predSubjectSelect');
          var found = false;
          for (var i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value.toLowerCase() === subjectName.toLowerCase()) {
              sel.selectedIndex = i;
              found = true;
              break;
            }
          }
          if (!found) {
            sel.value = 'Overall';
          }
          document.getElementById('predPreviousGradeNum').value = currentScore;
          document.getElementById('predPreviousGradeRange').value = currentScore;
          document.getElementById('predScopeBadge').textContent = subjectName + ' Subject Forecast';

          var target = document.getElementById('mlPredictionSection');
          if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            target.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
            target.style.borderColor = 'var(--O)';
            target.style.boxShadow = '0 0 22px rgba(212,168,71,.4)';
            setTimeout(function() {
              target.style.borderColor = 'rgba(45,106,79,.25)';
              target.style.boxShadow = '0 8px 30px rgba(0,0,0,.08)';
            }, 1200);
          }
          mwRunPrediction();
        }

        function mwQuickFillStats(val) {
          document.getElementById('predPreviousGradeNum').value = val;
          document.getElementById('predPreviousGradeRange').value = val;
          mwRunPrediction();
        }

        function mwCheckApiHealth() {
          fetch('api_predict_grade.php?action=check_health')
            .then(function(res) { return res.json(); })
            .then(function(data) {
              var badge = document.getElementById('mlApiStatusBadge');
              if (data && data.online) {
                badge.innerHTML = '<i class="fas fa-circle-check" style="color:#52b788"></i> <span>C# ML.NET Connected (localhost:5000)</span>';
                badge.style.background = 'rgba(82,183,136,.18)';
                badge.style.color = '#fff';
                badge.style.borderColor = '#52b788';
                document.getElementById('predEngineLabel').textContent = 'Live C# ASP.NET Core API';
              } else {
                badge.innerHTML = '<i class="fas fa-microchip" style="color:#ffbe3b"></i> <span>Smart Estimator (C# Service Offline)</span>';
                badge.style.background = 'rgba(212,168,71,.2)';
                badge.style.color = '#fff';
                badge.style.borderColor = 'var(--O)';
                document.getElementById('predEngineLabel').textContent = 'Local Smart ML Model';
              }
            })
            .catch(function() {
              var badge = document.getElementById('mlApiStatusBadge');
              badge.innerHTML = '<i class="fas fa-microchip" style="color:#ffbe3b"></i> <span>Smart Estimator Ready</span>';
            });
        }

        function mwRunPrediction() {
          var btn = document.getElementById('btnRunPrediction');
          var btnTxt = document.getElementById('btnRunPredictionTxt');
          var studyHours = parseFloat(document.getElementById('predStudyHoursNum').value) || 0;
          var attendance = parseFloat(document.getElementById('predAttendanceNum').value) || 0;
          var prevGrade = parseFloat(document.getElementById('predPreviousGradeNum').value) || 0;
          var sel = document.getElementById('predSubjectSelect');
          var subjectName = sel ? sel.value : 'Overall';

          btn.disabled = true;
          btnTxt.textContent = 'Computing with ML.NET...';

          fetch('api_predict_grade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              study_hours: studyHours,
              attendance_percentage: attendance,
              previous_grade: prevGrade,
              subject: subjectName
            })
          })
          .then(function(res) { return res.json(); })
          .then(function(data) {
            btn.disabled = false;
            btnTxt.textContent = 'Predict Grade with ML.NET';

            if (data && data.success) {
              var p = data.prediction;
              document.getElementById('predScoreVal').textContent = p.predictedScore.toFixed(1) + '%';
              var gb = document.getElementById('predGradeBadge');
              gb.textContent = p.letterGrade;
              gb.style.background = p.badgeBg;
              gb.style.color = p.badgeText;

              document.getElementById('predProgressFill').style.width = Math.min(100, Math.max(0, p.predictedScore)) + '%';
              document.getElementById('predTierBadge').textContent = p.tier;

              var db = document.getElementById('predDeltaBadge');
              if (p.delta > 0) {
                db.innerHTML = '<i class="fas fa-arrow-trend-up"></i> +' + p.delta.toFixed(1) + '% Expected Gain';
                db.style.background = '#e8f5e9';
                db.style.color = '#1b5e20';
              } else if (p.delta < 0) {
                db.innerHTML = '<i class="fas fa-arrow-trend-down"></i> ' + p.delta.toFixed(1) + '% Projected Dip';
                db.style.background = '#ffebee';
                db.style.color = '#b71c1c';
              } else {
                db.innerHTML = '<i class="fas fa-equals"></i> Steady Performance';
                db.style.background = '#f3f4f6';
                db.style.color = '#4b5563';
              }

              var list = document.getElementById('predInsightsList');
              list.innerHTML = '';
              if (data.insights && data.insights.length) {
                data.insights.forEach(function(msg) {
                  var row = document.createElement('div');
                  row.style.display = 'flex';
                  row.style.alignItems = 'flex-start';
                  row.style.gap = '8px';
                  row.innerHTML = '<i class="fas fa-lightbulb" style="color:var(--O);margin-top:3px;font-size:.78rem"></i><span>' + msg + '</span>';
                  list.appendChild(row);
                });
              }

              if (data.statusNote) {
                document.getElementById('predServiceStatusNote').textContent = data.statusNote;
              }
            }
          })
          .catch(function(err) {
            btn.disabled = false;
            btnTxt.textContent = 'Predict Grade with ML.NET';
            console.error('Prediction failed', err);
          });
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
          mwCheckApiHealth();
          mwRunPrediction();
        });
        </script>
        <style>
        @media (max-width: 860px) {
          .ml-grid-layout { grid-template-columns: 1fr !important; }
        }
        #student.cyber-hud-mode #mlPredictionSection {
          background: #080f0c !important;
          border-color: #ff103f !important;
          box-shadow: 0 0 20px rgba(255, 16, 63, 0.25) !important;
        }
        #student.cyber-hud-mode #mlPredictionSection .crdhdr {
          background: linear-gradient(135deg, #45000a 0%, #1a0004 100%) !important;
          border-bottom-color: #ff103f !important;
        }
        #student.cyber-hud-mode #mlPredictionSection input,
        #student.cyber-hud-mode #mlPredictionSection select {
          background: #0d1a14 !important;
          color: #fff !important;
          border-color: #264a38 !important;
        }
        </style>
        <!-- ── TIMETABLE TAB ── -->
      <?php elseif ($tab === 'timetable'): ?>
        <?php
        $yr = (int) $user['year'];
        if ($yr <= 6)
          $yg = '1-6';
        elseif ($yr <= 9)
          $yg = '7-9';
        else
          $yg = '10-11';
        ?>
        <div class="phdr" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div>
            <h2>My Timetable</h2>
            <p>Year <?= e($user['year']) ?> — Weekly Schedule</p>
          </div>

        </div>


          <div class="tw">
            <table>
      <thead>
    <tr>
        <th style="text-align:left;padding-left:1rem;min-width:90px">Day</th>

        <?php
       if ($yg === '1-6') {

    $periodKeys = ['p1','p2','p3','p4','p5'];

    $headers = [
        '8:00-9:00',
        '9:00-10:00',
        '10:00-10:45 (Break)',
        '10:45-11:45',
        '11:45-12:45'
    ];

} elseif ($yg === '7-9') {

    $periodKeys = ['p1','p2','p3','p4','p5','p6','p7'];

    $headers = [
        '8:00-8:45',
        '8:45-9:30',
        '9:30-10:00 (30min)',
        '10:00-11:00 (Break)',
        '11:00-11:45',
        '11:45-12:30',
        '12:30-1:15'
    ];

} else {   // Year 10-11

    $periodKeys = ['p1','p2','p3','p4','p5','p6','p7'];

    $headers = [
        '8:00-8:45',
        '8:45-9:30',
        '9:30-10:00 (30min)',
        '10:00-11:00 (Break)',
        '11:00-11:45',
        '11:45-12:30',
        '12:30-1:15'
    ];

}
        foreach ($headers as $header):
        ?>
            <th><?= e($header) ?></th>
        <?php endforeach; ?>

    </tr>
</thead>
              <tbody>
                <?php foreach ($TIMETABLE as $row):
                  $periods = [];
                  foreach ($periodKeys as $key) {
                    $periods[] = $row[$key] ?? '';
                  }
                  $is_sport = (bool) $row['sport_day'];
                  ?>
                  <tr class="tt-row">
                    <td
                      class="tt-day-col"
                      style="font-weight:600;font-size:.78rem;color:var(--MT);background:#f9f6f0;border-right:1.5px solid var(--W);padding:.5rem 1rem;white-space:nowrap">
                      <?= e($row['day']) ?>
                    </td>
                    <?php foreach ($periods as $idx => $p): ?>
                      <td style="padding:4px">
                        <div
                          class="tt-slot <?= strtoupper($p) === 'SPORT' ? 'tt-sport' : (strtoupper($p) === 'BREAK' ? 'tt-break' : 'tt-normal') ?>"
                          style="background:<?= strtoupper($p) === 'SPORT' ? '#fef3c7' : (strtoupper($p) === 'BREAK' ? '#f3f4f6' : 'rgba(45,106,79,.08)') ?>;color:<?= strtoupper($p) === 'SPORT' ? '#92400e' : (strtoupper($p) === 'BREAK' ? '#6b7280' : 'var(--P)') ?>;border-radius:8px;padding:5px 4px;font-size:.75rem;font-weight:<?= strtoupper($p) === 'BREAK' ? '400' : '600' ?>;display:flex;align-items:center;justify-content:center;height:36px;text-align:center;line-height:1.2">
                          <?= e($p !== '' ? $p : '—') ?>
                        </div>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ── HOMEWORK TAB (Requirement 5: Upload Function) ── -->
      <?php elseif ($tab === 'homework'): ?>
        <?php
        $dbHomework = [];
        if ($conn && $userId > 0) {
            $hwQ = $conn->query("SELECT h.*, s.name AS subject_name FROM homework h LEFT JOIN subjects s ON h.subject_id = s.id WHERE h.student_id = $userId ORDER BY h.due_date ASC");
            if ($hwQ) {
                while ($r = $hwQ->fetch_assoc()) $dbHomework[] = $r;
            }
        }
        $subHw = array_filter($dbHomework, fn($h) => $h['status'] === 'submitted');
        $misHw = array_filter($dbHomework, fn($h) => $h['status'] === 'missing');
        $nowDate = date('Y-m-d');
        $lateHw = array_filter($misHw, fn($h) => $h['due_date'] < $nowDate);
        $completionPct = count($dbHomework) ? round(count($subHw) / count($dbHomework) * 100) : 0;
        ?>
        <div class="phdr" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
          <div>
            <h2>Homework &amp; Assignments</h2>
            <p>Upload your homework submissions and track test deadlines</p>
          </div>
          <?php if (!empty($dbHomework)): ?>
            <button type="button" class="bpr bsm" onclick="openHwUploadModal(<?= (int)$dbHomework[0]['id'] ?>, '<?= e(addslashes($dbHomework[0]['title'])) ?>')">
              <i class="fas fa-cloud-upload-alt"></i> Submit Work
            </button>
          <?php endif; ?>
        </div>

        <div class="srow">
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div>
            <div class="sv"><?= count($subHw) ?></div>
            <div class="sl">Submitted</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-times-circle"></i></div>
            <div class="sv"><?= count($misHw) ?></div>
            <div class="sl">Missing</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-clock"></i></div>
            <div class="sv"><?= count($lateHw) ?></div>
            <div class="sl">Overdue</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-percentage"></i></div>
            <div class="sv"><?= $completionPct ?>%</div>
            <div class="sl">Completion</div>
          </div>
        </div>

        <div class="crd">
          <div class="tw">
            <table>
              <thead>
                <tr>
                  <th>Assignment Name</th>
                  <th>Subject</th>
                  <th>Type</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Submission / File</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($dbHomework)): ?>
                  <tr>
                    <td colspan="7" style="padding:2.5rem;text-align:center;color:var(--MT)">
                      <i class="fas fa-check-double" style="font-size:2rem;margin-bottom:.5rem;display:block"></i>
                      No homework tasks assigned yet. You're all caught up! 🎉
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($dbHomework as $h):
                    $isSubmitted = ($h['status'] === 'submitted');
                    $isOverdue = (!$isSubmitted && $h['due_date'] < $nowDate);
                    $cls = ($h['type'] === 'Quiz') ? 'bqz' : (($h['type'] === 'Test') ? 'bts' : 'bhw');
                  ?>
                    <tr>
                      <td style="font-weight:600">
                        <?= e($h['title']) ?>
                        <?php if ($isOverdue): ?>
                          <span style="font-size:.68rem;background:#fee2e2;color:#991b1b;padding:.12rem .4rem;border-radius:4px;margin-left:.4rem;font-weight:700">Overdue</span>
                        <?php endif; ?>
                      </td>
                      <td style="color:var(--MT);font-size:.82rem"><?= e($h['subject_name'] ?: 'General') ?></td>
                      <td><span class="<?= $cls ?>"><?= e($h['type']) ?></span></td>
                      <td style="color:var(--MT)"><?= date('d M Y', strtotime($h['due_date'])) ?></td>
                      <td>
                        <?php if ($isSubmitted): ?>
                          <span class="bok"><i class="fas fa-check-circle"></i> Submitted</span>
                        <?php else: ?>
                          <span class="bno"><i class="fas fa-times-circle"></i> Missing</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($h['submission_file'])): ?>
                          <a href="download_submission.php?id=<?= (int)$h['id'] ?>" class="hw-file-badge" title="Download your submission">
                            <i class="fas fa-file-download"></i> View File
                          </a>
                          <?php if (!empty($h['submitted_at'])): ?>
                            <div style="font-size:.68rem;color:var(--MT);margin-top:2px"><?= date('d M, g:ia', strtotime($h['submitted_at'])) ?></div>
                          <?php endif; ?>
                        <?php else: ?>
                          <span style="font-size:.76rem;color:var(--MT)">No file uploaded</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!$isSubmitted): ?>
                          <button type="button" class="hw-upload-btn" onclick="openHwUploadModal(<?= (int)$h['id'] ?>, '<?= e(addslashes($h['title'])) ?>')">
                            <i class="fas fa-upload"></i> Upload Work
                          </button>
                        <?php else: ?>
                          <button type="button" class="bsd bsm" onclick="openHwUploadModal(<?= (int)$h['id'] ?>, '<?= e(addslashes($h['title'])) ?>')">
                            <i class="fas fa-redo"></i> Resubmit
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Upload Homework Modal -->
        <div id="hwUploadModal" class="mw-modal">
          <div class="mw-modal-card">
            <div class="mw-modal-hdr" style="background:var(--F)">
              <h3 style="font-size:1.05rem;display:flex;align-items:center;gap:.5rem"><i class="fas fa-cloud-upload-alt" style="color:var(--O)"></i> Submit Assignment</h3>
              <button onclick="closeHwUploadModal()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:1.1rem"><i class="fas fa-times"></i></button>
            </div>
            <form action="submit_homework.php" method="POST" enctype="multipart/form-data">
              <div class="mw-modal-body">
                <input type="hidden" name="hw_id" id="uploadHwId" value="">
                <div class="fg">
                  <label>Assignment</label>
                  <input type="text" id="uploadHwTitle" readonly style="background:#f4f1ea;font-weight:700">
                </div>
                <div class="fg">
                  <label>Select Homework File (PDF, Word, PPT, Excel, Images, ZIP up to 20MB)</label>
                  <input type="file" name="homework_file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.png,.jpg,.jpeg">
                </div>
                <div class="fg">
                  <label>Remarks / Notes for Teacher (Optional)</label>
                  <textarea name="submission_notes" rows="3" style="width:100%;border-radius:10px;border:1.5px solid var(--W);padding:.6rem .8rem;font-family:inherit;font-size:.85rem;outline:none" placeholder="Add any comments or questions for your teacher..."></textarea>
                </div>
              </div>
              <div class="mw-modal-foot">
                <button type="button" class="bsd bsm" onclick="closeHwUploadModal()">Cancel</button>
                <button type="submit" class="bpr bsm"><i class="fas fa-check"></i> Upload &amp; Submit</button>
              </div>
            </form>
          </div>
        </div>

        <!-- ── CALENDAR TAB (Requirements 6 & 7) ── -->
      <?php elseif ($tab === 'calendar'): ?>
        <div class="phdr" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
          <div>
            <h2>Academic &amp; Holiday Planner</h2>
            <p>School calendar with color-coded national public holidays, school events, and deadlines</p>
          </div>
          <button type="button" class="bpr bsm" onclick="mwOpenCalendar()">
            <i class="fas fa-expand"></i> Launch Fullscreen Planner
          </button>
        </div>

        <div class="crd" style="padding:1.4rem">
          <div class="cal-pl-legend" style="border-radius:10px;margin-bottom:1.2rem;border:1px solid var(--W)">
            <span style="font-weight:700;color:var(--F)"><i class="fas fa-palette"></i> Color Codes:</span>
            <div class="legend-item"><span class="legend-chip legend-hol"></span> <span>Public Holiday (National)</span></div>
            <div class="legend-item"><span class="legend-chip legend-evt"></span> <span>School Special Event</span></div>
            <div class="legend-item"><span class="legend-chip legend-exam"></span> <span>Examination &amp; Test</span></div>
            <div class="legend-item"><span class="legend-chip legend-sport"></span> <span>Sports Day / Session</span></div>
            <div class="legend-item"><span class="legend-chip legend-hw"></span> <span>Assignment Deadline</span></div>
          </div>
          <div style="border:1.5px solid var(--W);border-radius:14px;overflow:hidden">
            <div class="cal-grid-fs" id="calInlineGrid"></div>
          </div>
        </div>
        <script>
          window.addEventListener('load', function() {
            mwLoadCalendarEvents();
            // Also duplicate grid to inline if on calendar tab
            setTimeout(function() {
              var fsGrid = document.getElementById('calPlGrid');
              var inGrid = document.getElementById('calInlineGrid');
              if (fsGrid && inGrid && fsGrid.children.length) {
                inGrid.innerHTML = fsGrid.innerHTML;
              }
            }, 800);
          });
        </script>

        <!-- ── PAYMENT TAB ── -->
      <?php elseif ($tab === 'payment'): ?>
        <div class="phdr">
          <h2>Payment</h2>
          <p>Your fee statements and invoice history</p>
        </div>
        <?php
        $uid = $_SESSION['user']['id'] ?? null;
        $myPayments = [];
        if ($uid && $conn) {
          try {
            $r = $conn->query("SELECT p.*, u.name AS student_name FROM payments p LEFT JOIN users u ON p.user_id=u.id WHERE p.user_id=$uid ORDER BY p.created_at DESC");
            if ($r)
              while ($row = $r->fetch_assoc())
                $myPayments[] = $row;
          } catch (mysqli_sql_exception $e) {
            // payments table doesn't exist yet — fall through to $PAYMENTS fallback below
          }
        }
        // Fallback: use global $PAYMENTS if DB not available or table missing
        if (empty($myPayments)) {
          foreach ($PAYMENTS as $p) {
            $myPayments[] = $p;
          }
        }

        // Calculate current balance (sum of unpaid invoices)
        $balance = 0.0;
        foreach ($myPayments as $mp) {
          $amt = 0.0;
          if (isset($mp['amount'])) {
            $a = preg_replace('/[^0-9.]/', '', (string) $mp['amount']);
            $amt = $a !== '' ? (float) $a : 0.0;
          }
          if ((isset($mp['status']) && strtolower($mp['status']) !== 'paid') || (isset($mp['status']) && $mp['status'] === 'Invoice Sent'))
            $balance += $amt;
        }

        // If an invoice id is requested, fetch it for inline viewing in the Payment tab
        $viewInvoiceId = isset($_GET['invoice']) ? (int) $_GET['invoice'] : 0;
        $viewInvoice = null;
        if ($viewInvoiceId && $conn) {
          $stmtInv = $conn->prepare('SELECT p.*, u.name AS student_name FROM payments p LEFT JOIN users u ON p.user_id=u.id WHERE p.id=? AND p.user_id=? LIMIT 1');
          $stmtInv->bind_param('ii', $viewInvoiceId, $uid);
          $stmtInv->execute();
          $resInv = $stmtInv->get_result();
          $viewInvoice = $resInv->fetch_assoc();
          $stmtInv->close();
        }
        ?>
        <div class="phero">
          <div>
            <div style="font-size:.75rem;opacity:.65;margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em">
              Current Balance Due</div>
            <div style="font-size:2.3rem;font-weight:700;font-family:'Playfair Display',serif">RM
              <?= number_format($balance, 2) ?>
            </div>
            <div style="font-size:.8rem;opacity:.6;margin-top:.28rem">Outstanding invoices</div>
          </div>
          <?php
          $firstUnpaid = null;
          foreach ($myPayments as $mp) {
            if (isset($mp['status']) && strtolower($mp['status']) !== 'paid') {
              $firstUnpaid = $mp;
              break;
            }
          }
          ?>
          <?php if ($firstUnpaid && isset($firstUnpaid['id'])): ?>
            <a href="pay.php?id=<?= (int) $firstUnpaid['id'] ?>" class="bpr bsm"
              style="display:inline-block;padding:.8rem 1.4rem;border-radius:12px">Pay Now &nbsp;<i
                class="fas fa-arrow-right"></i></a>
          <?php else: ?>
            <button onclick="alert('No outstanding invoices.')" class="bpr bsm"
              style="display:inline-block;padding:.8rem 1.4rem;border-radius:12px">All Paid &nbsp;<i
                class="fas fa-check"></i></button>
          <?php endif; ?>
        </div>
          <?php if ($viewInvoice): ?>
            <div class="crd" id="invoice-<?= (int) $viewInvoice['id'] ?>" style="margin-top:1rem">
              <div class="crdhdr">
                <h3>Invoice #<?= e($viewInvoice['id']) ?></h3>
              </div>
              <div style="padding:1rem">
                <p><strong>Student:</strong> <?= e($viewInvoice['student_name'] ?? $user['name']) ?></p>
                <p><strong>Amount:</strong> <?= e($viewInvoice['currency'] . ' ' . number_format($viewInvoice['amount'], 2)) ?></p>
                <p><strong>Status:</strong> <?= e($viewInvoice['status']) ?></p>
                <p><strong>Created:</strong> <?= e($viewInvoice['created_at']) ?></p>
                <?php if (!empty($viewInvoice['paid_at'])): ?>
                  <p><strong>Paid At:</strong> <?= e($viewInvoice['paid_at']) ?></p>
                <?php endif; ?>
                <div style="margin-top:.8rem;display:flex;gap:.6rem;flex-wrap:wrap">
                  <a href="?tab=payment" class="bsd bsm">Close</a>
                  <?php if (isset($viewInvoice['status']) && strtolower($viewInvoice['status']) !== 'paid'): ?>
                    <a href="pay.php?id=<?= (int) $viewInvoice['id'] ?>" class="bpr bsm">Pay Now</a>
                  <?php endif; ?>
                  <button onclick="printInvoice(<?= (int) $viewInvoice['id'] ?>)" class="bsd bsm">Print</button>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php foreach ($myPayments as $p): ?>
          <?php
            $hasId = isset($p['id']) && (int) $p['id'] > 0;
            $cardHref = '';
            if ($hasId) {
              // If invoice already paid, open inline viewer; otherwise link to the pay flow
              if (isset($p['status']) && strtolower($p['status']) === 'paid') {
                $cardHref = 'studentHomePage.php?tab=payment&invoice=' . (int) $p['id'];
              } else {
                $cardHref = 'pay.php?id=' . (int) $p['id'];
              }
            }
          ?>
          <?php if ($hasId): ?>
            <a class="pcard" href="<?= e($cardHref) ?>">
          <?php else: ?>
            <div class="pcard">
          <?php endif; ?>
              <div>
                <div class="pcard-date" style="font-size:.74rem;margin-bottom:.22rem"><?= e($p['created_at'] ?? $p['date']) ?>
                  — Invoice</div>
                <div class="pcard-amount" style="font-size:1.4rem;font-weight:700;font-family:'Playfair Display',serif">
                  <?= e(isset($p['amount_formatted']) ? $p['amount_formatted'] : (isset($p['amount']) ? (is_numeric($p['amount']) ? ('RM ' . number_format((float) $p['amount'], 2)) : $p['amount']) : 'RM 0.00')) ?>
                </div>
              </div>
              <?php if (isset($p['status']) && strtolower($p['status']) === 'paid'): ?>
                <span class="bpd"><i class="fas fa-check"></i> Paid</span>
              <?php else: ?>
                <span class="binv"><i class="fas fa-file-invoice"></i> Invoice Sent</span>
                <?php if (!$hasId && isset($p['id'])): ?>
                  <a href="pay.php?id=<?= (int) $p['id'] ?>" style="margin-left:.6rem" class="bsd bsm">Pay</a>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($hasId): ?>
            </a>
          <?php else: ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  </main>
</div>
</div>

<script>
  // FIX: accept event parameter and stop propagation so the
  // document click listener doesn't immediately close the dropdown
  function toggleNotifs(e) {
    e.stopPropagation();
    var d = document.getElementById('notifDrop');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
  }
  
  function mwCompleteTodo(hwId, checkbox) {
    checkbox.disabled = true;
    fetch('toggle_todo.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'hw_id=' + hwId
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        var row = document.getElementById('todo-' + hwId);
        row.style.transition = 'opacity .3s';
        row.style.opacity = '0';
        setTimeout(function(){ row.remove(); }, 300);
      } else {
        checkbox.checked = false;
        checkbox.disabled = false;
        alert('Could not update. Please try again.');
      }
    })
    .catch(function(){
      checkbox.checked = false;
      checkbox.disabled = false;
      alert('Connection error.');
    });
  }
  document.addEventListener('click', function (e) {
    var wrap = document.getElementById('bellWrap');
    if (wrap && !wrap.contains(e.target)) {
      var d = document.getElementById('notifDrop');
      if (d) d.style.display = 'none';
    }
  });

  window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
      window.location.reload();
    }
  });

  (function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('refresh') === '1') {
      params.delete('refresh');
      var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
      window.history.replaceState(null, '', newUrl);
    }
  })();

  function printInvoice(id) {
    var el = document.getElementById('invoice-' + id);
    if (!el) return alert('Invoice not found');
    var w = window.open('', '_blank');
    var styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(function(n){ return n.outerHTML }).join('\n');
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>Invoice #' + id + '</title>' + styles + '</head><body>' + el.innerHTML + '</body></html>');
    w.document.close();
    w.focus();
    setTimeout(function(){ w.print(); }, 300);
  }

  function openHwUploadModal(id, title) {
    var idInput = document.getElementById('uploadHwId');
    var titleInput = document.getElementById('uploadHwTitle');
    var modal = document.getElementById('hwUploadModal');
    if (idInput) idInput.value = id;
    if (titleInput) titleInput.value = title || 'Assignment #' + id;
    if (modal) modal.classList.add('open');
  }

  function closeHwUploadModal() {
    var modal = document.getElementById('hwUploadModal');
    if (modal) modal.classList.remove('open');
  }

  (function() {
    var params = new URLSearchParams(window.location.search);
    var uploadId = params.get('upload');
    if (uploadId) {
      setTimeout(function() {
        openHwUploadModal(uploadId, 'Assignment #' + uploadId);
      }, 300);
    }
  })();
</script>
<?php pageFoot(); ?>