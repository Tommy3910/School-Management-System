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
<div id="student">
  <!-- HEADER -->
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
            <span><i class="fas fa-bell"></i> Notifications</span>
            <?php if ($unreadCount > 0): ?>
              <a href="?tab=<?= $tab ?>&markread=1" class="ndmark">Mark all read</a>
            <?php endif; ?>
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
    <!-- SIDEBAR -->
    <aside class="sb s-sb">
      <button class="sbToggle" onclick="mwToggleSidebar()" title="Collapse menu">
        <i class="fas fa-angles-left"></i>
      </button>
      <span class="slbl s-slbl">Menu</span>
      <a class="nl s-nl <?= $tab === 'courses' ? 'on' : '' ?>" href="?tab=courses" title="Courses"> <i class="fas fa-book-open"></i>
        <span class="nl-label">Courses</span></a>
      <a class="nl s-nl <?= $tab === 'timetable' ? 'on' : '' ?>" href="?tab=timetable" title="Timetable"> <i
          class="fas fa-calendar-alt"></i>
        <span class="nl-label">Timetable</span></a>
      <a class="nl s-nl <?= $tab === 'homework' ? 'on' : '' ?>" href="?tab=homework" title="Homework Status"> <i class="fas fa-tasks"></i>
        <span class="nl-label">Homework Status</span></a>
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
      <div class="cwid" style="margin-top:1.2rem">
        <span class="wtl"><i class="fas fa-calendar"></i> <span id="calMonthYear">June 2026</span></span>
        <div class="calgrid">
          <div class="calmo" id="calMonthDisplay">June 2026</div>
          <div class="caldays" id="calendarContainer">
            <!-- Generated by JavaScript -->
          </div>
        </div>
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
            <div class="sv">B+</div>
            <div class="sl">Grade</div>
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

      <?php elseif ($tab === 'grades'): ?>
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
        ?>
        <div class="phdr">
          <h2>My Grades</h2>
          <p>Year <?= e($user['year']) ?> — Academic Results</p>
        </div>

        <div style="display:flex;gap:.5rem;margin-bottom:1.2rem;flex-wrap:wrap">
          <?php foreach ($terms as $t): ?>
            <a href="?tab=grades&term=<?= urlencode($t) ?>" style="padding:.38rem 1rem;border-radius:8px;font-size:.8rem;font-weight:600;border:1.5px solid var(--W);
    background:<?= $t === $term ? 'var(--P)' : '#fff' ?>;
    color:<?= $t === $term ? '#fff' : 'var(--F)' ?>">
              <?= e($t) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="srow" style="margin-bottom:1.2rem">
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-book"></i></div>
            <div class="sv"><?= $count ?></div>
            <div class="sl">Subjects Graded</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#e3f2fd;color:#0d47a1"><i class="fas fa-percentage"></i></div>
            <div class="sv"><?= $overall_pct ?>%</div>
            <div class="sl">Overall Score</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-star"></i></div>
            <div class="sv"><?= $overall_grade ?></div>
            <div class="sl">Overall Grade</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fce4ec;color:#880e4f"><i class="fas fa-trophy"></i></div>
            <div class="sv">
              <?php
              $as = array_filter($my_grades, fn($g) => str_starts_with($g['grade'], 'A'));
              echo count($as);
              ?>
            </div>
            <div class="sl">A Grades</div>
          </div>
        </div>

        <?php if (empty($my_grades)): ?>
          <div class="crd" style="padding:2.5rem;text-align:center;color:var(--MT)">
            <i class="fas fa-hourglass-half" style="font-size:2rem;margin-bottom:.75rem;display:block"></i>
            No grades released for <?= e($term) ?> yet. Check back soon!
          </div>
        <?php else: ?>
          <div class="crd">
            <div class="crdhdr">
              <h3><i class="fas fa-chart-bar"></i>&nbsp; <?= e($term) ?> Results</h3>
            </div>
            <div class="tw">
              <table>
                <thead>
                  <tr>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Performance</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($my_grades as $g):
                    $pct = round(($g['marks'] / $g['max_marks']) * 100, 1);
                    $color = gradeColor($g['grade']);
                    ?>
                    <tr>
                      <td style="font-weight:600"><?= e($g['subject_name']) ?></td>
                      <td style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700">
                        <?= $g['marks'] ?><span
                          style="font-size:.72rem;color:var(--MT);font-family:'Plus Jakarta Sans',sans-serif"> /
                          <?= $g['max_marks'] ?></span>
                      </td>
                      <td>
                        <div style="display:flex;align-items:center;gap:.6rem">
                          <div
                            style="flex:1;height:7px;background:var(--W);border-radius:99px;overflow:hidden;min-width:80px">
                            <div
                              style="height:100%;width:<?= $pct ?>%;background:<?= $pct >= 70 ? '#52b788' : ($pct >= 50 ? '#f4a261' : '#e63946') ?>;border-radius:99px">
                            </div>
                          </div>
                          <span style="font-size:.78rem;font-weight:600;color:var(--F);min-width:38px"><?= $pct ?>%</span>
                        </div>
                      </td>
                      <td>
                        <span style="display:inline-flex;align-items:center;justify-content:center;
              width:42px;height:42px;border-radius:10px;font-weight:800;font-size:1rem;
              background:<?= $color[0] ?>;color:<?= $color[1] ?>">
                          <?= e($g['grade']) ?>
                        </span>
                      </td>
                      <td style="font-size:.78rem;color:var(--MT)">
                        <?php
                        if ($pct >= 80)
                          echo '<span style="color:#1b5e20"><i class="fas fa-arrow-up"></i> Excellent</span>';
                        elseif ($pct >= 65)
                          echo '<span style="color:#0d47a1"><i class="fas fa-check"></i> Good</span>';
                        elseif ($pct >= 50)
                          echo '<span style="color:#bf360c"><i class="fas fa-minus"></i> Average</span>';
                        else
                          echo '<span style="color:#b71c1c"><i class="fas fa-arrow-down"></i> Needs Improvement</span>';
                        ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div
              style="padding:1rem 1.25rem;border-top:1.5px solid var(--W);background:#fafaf7;display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
              <span style="font-size:.8rem;font-weight:700;color:var(--F)">Overall <?= e($term) ?>:</span>
              <div style="flex:1;height:10px;background:var(--W);border-radius:99px;overflow:hidden;min-width:120px">
                <div
                  style="height:100%;width:<?= $overall_pct ?>%;background:linear-gradient(90deg,var(--P),#52b788);border-radius:99px;transition:width .5s ease">
                </div>
              </div>
              <span style="font-size:.88rem;font-weight:700;color:var(--P)"><?= $overall_pct ?>% —
                <?= $overall_grade ?></span>
            </div>
          </div>
        <?php endif; ?>
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
        <div class="phdr">
          <h2>My Timetable</h2>
          <p>Year <?= e($user['year']) ?> — Weekly Schedule</p>
        </div>

        <div class="crd">
          <div
            style="padding:.75rem 1.25rem;border-bottom:1.5px solid var(--W);font-size:.78rem;font-weight:700;color:var(--MT);text-transform:uppercase;letter-spacing:.06em">
            Year <?= e($user['year']) ?> &nbsp;·&nbsp; 
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
                  <tr>
                    <td
                      style="font-weight:600;font-size:.78rem;color:var(--MT);background:#f9f6f0;border-right:1.5px solid var(--W);padding:.5rem 1rem;white-space:nowrap">
                      <?= e($row['day']) ?>
                    </td>
                    <?php foreach ($periods as $idx => $p): ?>
                      <td style="padding:4px">
                        <div
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

        <!-- ── HOMEWORK TAB ── -->
      <?php elseif ($tab === 'homework'): ?>
        <div class="phdr">
          <h2>Homework Status</h2>
          <p>Track your assignments, quizzes and tests</p>
        </div>
        <div class="srow">
          <?php $ok = array_filter($HOMEWORK, fn($h) => $h['status'] === 'submitted');
          $ms = array_filter($HOMEWORK, fn($h) => $h['status'] === 'missing'); ?>
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div>
            <div class="sv"><?= count($ok) ?></div>
            <div class="sl">Submitted</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-times-circle"></i></div>
            <div class="sv"><?= count($ms) ?></div>
            <div class="sl">Missing</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-clock"></i></div>
            <div class="sv">0</div>
            <div class="sl">Late</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-percentage"></i>
            </div>
            <div class="sv"><?= count($HOMEWORK) ? round(count($ok) / count($HOMEWORK) * 100) . '%' : '0%' ?></div>
            <div class="sl">Completion</div>
          </div>
        </div>
        <div class="crd">
          <div class="tw">
            <table>
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Type</th>
                  <th>Due Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($HOMEWORK as $h): ?>
                  <tr>
                    <td style="font-weight:600"><?= e($h['item']) ?></td>
                    <td><?php
                    $cls = $h['type'] === 'Homework' ? 'bhw' : ($h['type'] === 'Quiz' ? 'bqz' : 'bts');
                    echo "<span class=\"{$cls}\">{$h['type']}</span>";
                    ?></td>
                    <td style="color:var(--MT)"><?= e($h['due']) ?></td>
                    <td><?php if ($h['status'] === 'submitted'): ?>
                        <span class="bok"><i class="fas fa-check-circle"></i> Submitted</span>
                      <?php else: ?>
                        <span class="bno"><i class="fas fa-times-circle"></i> Missing</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

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
                <div style="font-size:.74rem;color:var(--MT);margin-bottom:.22rem"><?= e($p['created_at'] ?? $p['date']) ?>
                  — Invoice</div>
                <div style="font-size:1.4rem;font-weight:700;font-family:'Playfair Display',serif;color:var(--F)">
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
</script>
<?php pageFoot(); ?>