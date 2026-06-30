<?php
require_once 'config.php';
require_once 'layout.php';
requireAuth('teacher');

$user = $_SESSION['user'];
$tab = $_REQUEST['tab'] ?? 'schedules';

// Handle homework approve/mark-missing POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $idx = (int) ($_POST['idx'] ?? -1);
}elseif ($action === 'save_schedule') {
    flash('toast', 'Schedule saved successfully.');
  } elseif ($action === 'save_timetable') {
    $row_ids = $_POST['row_ids'] ?? [];
    $slots = $_POST['slots'] ?? [];
    $sport_days = $_POST['sport_days'] ?? [];
    $tt_group = $_POST['tt_group'] ?? '10-11';

    // Number of editable columns per group (BREAK/SPORT cells send hidden values too)
    $col_count = ($tt_group === '1-6') ? 5 : 7;

    $slot_idx = 0;
    foreach ($row_ids as $rid) {
      $rid = (int) $rid;
      $is_sport = in_array((string) $rid, $sport_days) ? 1 : 0;
      $p = [];
      for ($x = 0; $x < $col_count; $x++) {
        $p[] = "'" . $conn->real_escape_string($slots[$slot_idx++] ?? '') . "'";
      }

      if ($tt_group === '1-6') {
        $conn->query("UPDATE timetable SET
                    p1={$p[0]}, p2={$p[1]}, p3={$p[2]}, p4={$p[3]}, p5={$p[4]},
                    sport_day=$is_sport
                    WHERE id=$rid");
      } else {
        $conn->query("UPDATE timetable SET
                    p1={$p[0]}, p2={$p[1]}, p3={$p[2]}, p4={$p[3]},
                    p5={$p[4]}, p6={$p[5]}, p7={$p[6]},
                    sport_day=$is_sport
                    WHERE id=$rid");
      }
    }
    flash('toast', 'Timetable saved successfully.');
  } elseif ($action === 'add_subject') {
    $name = trim($_POST['subj_name'] ?? '');
    $yg = trim($_POST['yg'] ?? '');
    if ($name !== '' && $yg !== '') {
      $nameEsc = $conn->real_escape_string($name);
      $ygEsc = $conn->real_escape_string($yg);
      $conn->query("INSERT INTO subjects (name, year_group) VALUES ('$nameEsc','$ygEsc')");
      flash('toast', 'Subject added to Year Group ' . e($yg) . '.');
    } else {
      flash('toast', 'Please enter a valid subject name.');
    }
    } elseif ($action === 'add_homework') {
    $target = $_POST['hw_student'] ?? ''; // 'all' or a specific student id
    $title  = trim($_POST['hw_title'] ?? '');
    $type   = $_POST['hw_type'] ?? 'Homework';
    $due    = $_POST['hw_due'] ?? '';

    if ($title === '' || $due === '') {
        flash('toast', 'Please fill in the assignment title and due date.');
    } else {
        $titleEsc = $conn->real_escape_string($title);
        $typeEsc  = $conn->real_escape_string($type);
        $dueEsc   = $conn->real_escape_string($due);
        $createdBy = (int) $_SESSION['user']['id'];

        if ($target === 'all') {
            $res = $conn->query("SELECT id FROM users WHERE role='student'");
            $count = 0;
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $sid = (int) $r['id'];
                    $conn->query("INSERT INTO homework (student_id, title, type, due_date, status, created_by)
                                  VALUES ($sid, '$titleEsc', '$typeEsc', '$dueEsc', 'missing', $createdBy)");
                    sendNotification($conn, $sid, 'New ' . $type, "A new {$type} \"{$title}\" is due on " . date('d M Y', strtotime($due)) . ".");
                    $count++;
                }
            }
            flash('toast', "Assigned \"$title\" to $count students.");
        } else {
            $sid = (int) $target;
            if ($sid > 0) {
                $conn->query("INSERT INTO homework (student_id, title, type, due_date, status, created_by)
                              VALUES ($sid, '$titleEsc', '$typeEsc', '$dueEsc', 'missing', $createdBy)");
                sendNotification($conn, $sid, 'New ' . $type, "A new {$type} \"{$title}\" is due on " . date('d M Y', strtotime($due)) . ".");
                flash('toast', "Assigned \"$title\" to the selected student.");
            } else {
                flash('toast', 'Please select a student.');
            }
        }
    }
}
    elseif ($action === 'remove_subject') {
    $subj_id = (int) ($_POST['subj_id'] ?? 0);
    if ($subj_id > 0) {
      $conn->query("DELETE FROM subjects WHERE id=$subj_id");
      flash('toast', 'Subject removed.');
    }
  } elseif ($action === 'save_grades') {
    $gyear = (int) ($_POST['gyear'] ?? 0);
    $gterm = $conn->real_escape_string($_POST['gterm'] ?? 'Term 1');
    $student_ids = $_POST['student_ids'] ?? [];
    $subject_ids = $_POST['subject_ids'] ?? [];
    $marks = $_POST['marks'] ?? [];
    foreach ($student_ids as $idx => $sid) {
      $student_id = (int) $sid;
      $subject_id = (int) ($subject_ids[$idx] ?? 0);
      $mark = isset($marks[$idx]) ? floatval($marks[$idx]) : null;
      if ($student_id > 0 && $subject_id > 0 && $mark !== null) {
        $grade = calcGrade($mark, 100);
        $markVal = $conn->real_escape_string((string) $mark);
        $conn->query("INSERT INTO grades (student_id, subject_id, year, term, marks, grade)
                    VALUES ($student_id, $subject_id, $gyear, '$gterm', $markVal, '$grade')
                    ON DUPLICATE KEY UPDATE marks=$markVal, grade='$grade'");
      }
    }
    flash('toast', 'Grades saved and notifications queued.');
  } elseif ($action === 'send_invoice') {
    $target = $_POST['student'] ?? '';
    if ($target === 'All Students') {
      // create invoice for all students
      $res = $conn->query("SELECT id FROM users WHERE role='student'");
      if ($res) {
        while ($r = $res->fetch_assoc()) {
          $uid = (int)$r['id'];
          $conn->query("INSERT INTO payments (user_id, amount, currency, description, status) VALUES ($uid, 450.00, 'RM', 'Monthly tuition fee', 'Invoice Sent')");
        }
      }
      flash('toast', 'Invoices queued for all students.');
    } else {
      // try find student by name
      $name = $conn->real_escape_string($target);
      $r = $conn->query("SELECT id FROM users WHERE name='$name' AND role='student' LIMIT 1");
      if ($r && ($u = $r->fetch_assoc())) {
        $uid = (int)$u['id'];
        $conn->query("INSERT INTO payments (user_id, amount, currency, description, status) VALUES ($uid, 450.00, 'RM', 'Monthly tuition fee', 'Invoice Sent')");
        flash('toast', 'Invoice sent to ' . e($target) . '.');
      } else {
        flash('toast', 'Student not found: ' . e($target));
      }
    }
  }
  $return_url = "teacherHomePage.php?tab={$tab}";
  if ($tab === 'grades') {
    $return_url .= '&gyear=' . ((int) ($_POST['gyear'] ?? $_GET['gyear'] ?? 10));
    $return_url .= '&gterm=' . urlencode($_POST['gterm'] ?? $_GET['gterm'] ?? 'Term 1');
    $return_url .= '&gsub=' . urlencode($_POST['gsub'] ?? $_GET['gsub'] ?? 'grade');
  }
  if (!empty($_POST['tt_year'])) {
    $return_url .= '&year=' . (int) $_POST['tt_year'];
  }
  redirect($return_url);

$toast = flash('toast');
pageHead('Teacher Dashboard');
?>
<div id="teacher">
  <!-- HEADER -->
  <header class="hdr t-hdr">
    <div class="hbrand">
      <img src="maple_wood_logo.png" alt="Maplewood Logo" class="hlogo">
      <h1>Maple<em>wood</em></h1>
      <span class="abadge">Admin</span>
    </div>
    <div class="hright">
      <span style="color:rgba(255,255,255,.75);font-size:.8rem"><i class="fas fa-chalkboard-teacher"></i>
        <?= e($user['name']) ?></span>
      <a href="logout.php" class="obtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>

  <div class="dlayout">
    <!-- SIDEBAR -->
    <aside class="sb t-sb">
      <span class="slbl t-slbl">Management</span>
      <a class="nl t-nl <?= $tab === 'schedules' ? 'on' : '' ?>" href="?tab=schedules"><i class="fas fa-calendar-alt"></i>
        Schedules</a>
      <a class="nl t-nl <?= $tab === 'timetable' ? 'on' : '' ?>" href="?tab=timetable"><i class="fas fa-table"></i> Timetable
        Editor</a>
      <a class="nl t-nl <?= $tab === 'grades' ? 'on' : '' ?>" href="?tab=grades"> <i class="fas fa-chart-bar"></i> Student
        Grades</a>
      <a class="nl t-nl <?= $tab === 'homework' ? 'on' : '' ?>" href="?tab=homework"> <i class="fas fa-tasks"></i> Homework
        Oversight</a>
      <a class="nl t-nl <?= $tab === 'payments' ? 'on' : '' ?>" href="?tab=payments"> <i
          class="fas fa-file-invoice-dollar"></i> Payments</a>
    </aside>

    <!-- MAIN -->
    <main class="mc t-mc">
      <?php if ($toast): ?>
        <div class="toast"><i class="fas fa-check-circle"></i> <?= e($toast) ?></div>
      <?php endif; ?>

      <?php // variables $SPORT,$DAYS,$TIMETABLE,$GRADES,$ALL_STUDENTS,$PAYMENTS are in scope from config.php ?>

      <!-- ── SCHEDULES ── -->
      <?php if ($tab === 'schedules'): ?>
        <div class="phdr">
          <h2>Sport Schedules</h2>
          <p>Manage sport day assignments by year group</p>
        </div>
        <div class="srow">
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-users"></i></div>
            <div class="sv">11</div>
            <div class="sl">Year Groups</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-running"></i></div>
            <div class="sv">3</div>
            <div class="sl">Sport Groups</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-clock"></i></div>
            <div class="sv">2 hr</div>
            <div class="sl">Session</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fce4ec;color:#880e4f"><i class="fas fa-calendar-check"></i></div>
            <div class="sv">Mon–Fri</div>
            <div class="sl">School Week</div>
          </div>
        </div>
        <div class="crd">
          <div class="crdhdr">
            <h3><i class="fas fa-running"></i>&nbsp; Sport Day Assignments</h3>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="save_schedule">
            <div class="scgrid">
              <?php foreach ($SPORT as $s): ?>
                <div class="sccard" style="background:<?= e($s['bg']) ?>">
                  <div class="scyr" style="color:<?= e($s['col']) ?>"><?= e($s['group']) ?></div>
                  <div class="scday" style="color:<?= e($s['col']) ?>"><?= e($s['day']) ?></div>
                  <div class="scdlbl">Assigned Sport Day</div>
                  <div class="scbdg"><i class="fas fa-clock"></i> 2 Hours</div><br>
                  <select class="scsel" name="sport_day[]">
                    <?php foreach ($DAYS as $d): ?>
                      <option <?= $d === $s['day'] ? 'selected' : '' ?>><?= e($d) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endforeach; ?>
            </div>
            <div style="padding:0 1.2rem 1.2rem">
              <button type="submit" class="bsm bpr"><i class="fas fa-save"></i> Save Changes</button>
            </div>
          </form>
        </div>

        <!-- ── TIMETABLE EDITOR ── -->
      <?php elseif ($tab === 'timetable'): ?>
        <?php
        $tt_year = (int) ($_GET['year'] ?? 10);
        if ($tt_year <= 6)
          $tt_group = '1-6';
        elseif ($tt_year <= 9)
          $tt_group = '7-9';
        else
          $tt_group = '10-11';

        $tt_rows = [];
        $res = $conn->query(
          "SELECT * FROM timetable
       WHERE year = $tt_year AND year_group = '$tt_group'
       ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday')"
        );
        if ($res)
          while ($r = $res->fetch_assoc())
            $tt_rows[] = $r;

        // Column headers per group
        if ($tt_group === '1-6') {
          $headers = ['8:00–9:00', '9:00–10:00', '10:00–10:45 (Break)', '10:45–11:45', '11:45–12:45'];
          $cols = ['p1', 'p2', 'p3', 'p4', 'p5'];
        } else {
          $headers = ['8:00–8:45', '8:45–9:30', '9:30–10:00 (30min)', '10:00–11:00 (Break)', '11:00–11:45', '11:45–12:30', '12:30–1:15', '1:15–1:45 (30min)'];
          $cols = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7'];
        }
        ?>
        <div class="phdr">
          <h2>Timetable Editor</h2>
          <p>Edit subject slots — changes reflect in student view instantly</p>
        </div>

        <div style="display:flex;gap:.75rem;margin-bottom:1.2rem;align-items:center;flex-wrap:wrap">
          <form method="GET">
            <input type="hidden" name="tab" value="timetable">
            <select name="year" onchange="this.form.submit()"
              style="padding:.38rem .75rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem;background:#fff">
              <?php foreach (range(1, 11) as $y): ?>
                <option value="<?= $y ?>" <?= $y === $tt_year ? 'selected' : '' ?>>Year <?= $y ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <span style="font-size:.75rem;color:var(--MT)">
            Group: <strong><?= $tt_group ?></strong> —
            <?php if ($tt_group === '1-6')
              echo '5 periods · 1 hr each · 8:00–12:45';
            else
              echo '7 periods · 45 min each · 8:00–1:45'; ?>
          </span>
        </div>

        <?php if (empty($tt_rows)): ?>
          <div class="crd" style="padding:2rem;text-align:center;color:var(--MT)">
            <i class="fas fa-calendar-times" style="font-size:2rem;margin-bottom:.75rem;display:block"></i>
            No timetable found for Year <?= $tt_year ?>. Add rows to the database first.
          </div>
        <?php else: ?>
          <div class="crd">
            <form method="POST">
              <input type="hidden" name="action" value="save_timetable">
              <input type="hidden" name="tt_year" value="<?= $tt_year ?>">
              <input type="hidden" name="tt_group" value="<?= $tt_group ?>">
              <div class="tw">
                <table>
                  <thead>
                    <tr>
                      <th style="text-align:left;padding-left:1rem;min-width:110px">Day</th>
                      <?php foreach ($headers as $h): ?>
                        <th style="font-size:.68rem"><?= $h ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tt_rows as $row): ?>
                      <input type="hidden" name="row_ids[]" value="<?= $row['id'] ?>">
                      <tr>
                        <td
                          style="font-weight:600;font-size:.78rem;color:var(--MT);background:#f9f6f0;border-right:1.5px solid var(--W);padding:.5rem 1rem;white-space:nowrap">
                          <?= e($row['day']) ?>
                          <label
                            style="display:flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:500;margin-top:.3rem;cursor:pointer;color:var(--P)">
                            <input type="checkbox" name="sport_days[]" value="<?= $row['id'] ?>"
                              <?= $row['sport_day'] ? 'checked' : '' ?>>
                            Sport day
                          </label>
                        </td>
                        <?php foreach ($cols as $idx => $col): ?>
                          <?php $val = $row[$col] ?? ''; ?>
                          <?php if ($val === 'BREAK'): ?>
                            <td style="background:#f9f6f0;text-align:center;font-size:.72rem;color:var(--MT);padding:4px">
                              Break<input type="hidden" name="slots[]" value="BREAK">
                            </td>
                          <?php elseif ($val === 'SPORT'): ?>
                            <td style="background:#fef9e7;text-align:center;font-size:.72rem;color:#92400e;padding:4px">
                              ⚽ Sport<input type="hidden" name="slots[]" value="SPORT">
                            </td>
                          <?php else: ?>
                            <td style="padding:3px">
                              <input class="einput" type="text" name="slots[]" value="<?= e($val) ?>" style="min-width:70px">
                            </td>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div
                style="padding:1rem 1.25rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;border-top:1.5px solid var(--W)">
                <button type="submit" class="bsm bpr"><i class="fas fa-save"></i> Save Timetable</button>
                <span style="font-size:.75rem;color:var(--MT)">
                  <i class="fas fa-info-circle"></i>
                  Type <strong>BREAK</strong> or <strong>SPORT</strong> (uppercase) to set special cells.
                  Tick "Sport day" to auto-mark the row.
                </span>
              </div>
            </form>
          </div>
        <?php endif; ?>

        <!-- ── GRADES TAB (Teacher) ── -->
      <?php elseif ($tab === 'grades'): ?>
        <?php
        $grade_year = (int) ($_GET['gyear'] ?? 10);
        $grade_term = $_GET['gterm'] ?? 'Term 1';
        $grade_subTab = $_GET['gsub'] ?? 'grade'; // 'grade' or 'subjects'
        $grade_subject_id = (int) ($_REQUEST['gsubj'] ?? 0);
        $terms = ['Term 1', 'Term 2', 'Term 3'];

        if ($grade_year <= 6)
          $grade_yg = '1-6';
        elseif ($grade_year <= 9)
          $grade_yg = '7-9';
        else
          $grade_yg = '10-11';

        // Fetch subjects for this year group
        $grade_yg_esc = $conn->real_escape_string($grade_yg);
        $subj_res = $conn->query(
          "SELECT * FROM subjects WHERE year_group='$grade_yg_esc' OR year_group = REPLACE('$grade_yg_esc','-',',') ORDER BY name"
        );
        $subjects = [];
        if ($subj_res)
          while ($r = $subj_res->fetch_assoc())
            $subjects[] = $r;

        if (!empty($subjects)) {
          $subjectIds = array_map('intval', array_column($subjects, 'id'));
          if (!in_array($grade_subject_id, $subjectIds, true)) {
            $grade_subject_id = (int) $subjects[0]['id'];
          }
        }

        $selected_subject = null;
        foreach ($subjects as $s) {
          if ((int) $s['id'] === $grade_subject_id) {
            $selected_subject = $s;
            break;
          }
        }

        // Fetch students for this year
        $stud_res = $conn->query("SELECT * FROM users WHERE role='student' AND year=$grade_year ORDER BY name");
        $students = [];
        if ($stud_res)
          while ($r = $stud_res->fetch_assoc())
            $students[] = $r;

        // Fetch existing grades
        $existing = [];
        if (!empty($students) && !empty($subjects)) {
          $gt = $conn->real_escape_string($grade_term);
          $subjectFilter = $grade_subject_id ? " AND g.subject_id = $grade_subject_id" : '';
          $gr = $conn->query(
            "SELECT g.*, s.name AS subject_name
           FROM grades g
           JOIN subjects s ON g.subject_id = s.id
           WHERE g.year = $grade_year AND g.term = '$gt'" . $subjectFilter
          );
          if ($gr)
            while ($r = $gr->fetch_assoc())
              $existing[$r['student_id']][$r['subject_id']] = $r;
        }
        ?>

        <div class="phdr">
          <h2>Student Grades</h2>
          <p>Manage and release grades to students</p>
        </div>

        <!-- Controls row -->
        <div style="display:flex;gap:.6rem;margin-bottom:1.2rem;flex-wrap:wrap;align-items:center">
          <!-- Year selector -->
          <form method="GET" style="display:flex;gap:.5rem;align-items:center">
            <input type="hidden" name="tab" value="grades">
            <input type="hidden" name="gsub" value="<?= e($grade_subTab ?? 'grade') ?>">
            <select name="gyear" onchange="this.form.submit()"
              style="padding:.38rem .75rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem;background:#fff">
              <?php foreach (range(1, 11) as $y): ?>
                <option value="<?= $y ?>" <?= $y === $grade_year ? 'selected' : '' ?>>Year <?= $y ?></option>
              <?php endforeach; ?>
            </select>
            <!-- Term selector -->
            <select name="gterm" onchange="this.form.submit()"
              style="padding:.38rem .75rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem;background:#fff">
              <?php foreach ($terms as $t): ?>
                <option <?= $t === $grade_term ? 'selected' : '' ?>><?= e($t) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($grade_subTab === 'grade' && !empty($subjects)): ?>
              <select name="gsubj" onchange="this.form.submit()"
                style="padding:.38rem .75rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem;background:#fff">
                <?php foreach ($subjects as $s): ?>
                  <option value="<?= $s['id'] ?>" <?= ((int) $s['id'] === $grade_subject_id) ? 'selected' : '' ?>>
                    <?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </form>

          <!-- Sub-tabs -->
          <a href="?tab=grades&gyear=<?= $grade_year ?>&gterm=<?= urlencode($grade_term) ?>&gsub=grade&gsubj=<?= $grade_subject_id ?>"
            style="padding:.38rem .9rem;border-radius:8px;font-size:.8rem;font-weight:600;border:1.5px solid var(--W);
    background:<?= $grade_subTab !== 'subjects' ? 'var(--P)' : '#fff' ?>;color:<?= $grade_subTab !== 'subjects' ? '#fff' : 'var(--F)' ?>">
            <i class="fas fa-pen"></i> Grade Students
          </a>
          <a href="?tab=grades&gyear=<?= $grade_year ?>&gterm=<?= urlencode($grade_term) ?>&gsub=subjects"
            style="padding:.38rem .9rem;border-radius:8px;font-size:.8rem;font-weight:600;border:1.5px solid var(--W);
    background:<?= $grade_subTab === 'subjects' ? 'var(--P)' : '#fff' ?>;color:<?= $grade_subTab === 'subjects' ? '#fff' : 'var(--F)' ?>">
            <i class="fas fa-list"></i> Manage Subjects
          </a>
        </div>

        <?php if ($grade_subTab === 'subjects'): ?>
          <!-- ── MANAGE SUBJECTS ── -->
          <div class="crd">
            <div class="crdhdr">
              <h3><i class="fas fa-list"></i>&nbsp; Subjects for Year Group <?= e($grade_yg) ?></h3>
            </div>
            <div style="padding:1.1rem 1.25rem">
              <!-- Add subject form -->
              <form method="POST" style="display:flex;gap:.6rem;margin-bottom:1.2rem;flex-wrap:wrap">
                <input type="hidden" name="action" value="add_subject">
                <input type="hidden" name="tab" value="grades">
                <input type="hidden" name="gsub" value="subjects">
                <input type="hidden" name="gyear" value="<?= $grade_year ?>">
                <input type="hidden" name="gterm" value="<?= e($grade_term) ?>">
                <input type="hidden" name="yg" value="<?= e($grade_yg) ?>">
                <input type="text" name="subj_name" placeholder="e.g. Physics, Art, Music..." required style="flex:1;min-width:180px;padding:.42rem .85rem;border-radius:8px;border:1.5px solid var(--W);
        font-family:'Plus Jakarta Sans',sans-serif;font-size:.85rem">
                <button type="submit" class="bsm bpr"><i class="fas fa-plus"></i> Add Subject</button>
              </form>

              <!-- Subject list -->
              <?php if (empty($subjects)): ?>
                <p style="color:var(--MT);font-size:.83rem">No subjects yet. Add one above.</p>
              <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.5rem">
                  <?php foreach ($subjects as $s): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;
          padding:.6rem 1rem;background:#f9f6f0;border-radius:9px;border:1.5px solid var(--W)">
                      <span style="font-weight:600;font-size:.85rem"><?= e($s['name']) ?></span>
                      <form method="POST" onsubmit="return confirm('Remove <?= e($s['name']) ?>?')">
                        <input type="hidden" name="action" value="remove_subject">
                        <input type="hidden" name="subj_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="gyear" value="<?= $grade_year ?>">
                        <input type="hidden" name="gterm" value="<?= e($grade_term) ?>">
                        <button type="submit" class="bsm bms" style="padding:.28rem .7rem">
                          <i class="fas fa-trash"></i> Remove
                        </button>
                      </form>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

        <?php else: ?>
          <!-- ── GRADE STUDENTS ── -->
          <?php if (empty($students)): ?>
            <div class="crd" style="padding:2rem;text-align:center;color:var(--MT)">
              <i class="fas fa-users" style="font-size:2rem;margin-bottom:.75rem;display:block"></i>
              No students found for Year <?= $grade_year ?>.
            </div>
          <?php elseif (empty($subjects) || !$selected_subject): ?>
            <div class="crd" style="padding:2rem;text-align:center;color:var(--MT)">
              <i class="fas fa-list" style="font-size:2rem;margin-bottom:.75rem;display:block"></i>
              No subjects set up for Year Group <?= e($grade_yg) ?>. Go to <strong>Manage Subjects</strong> first.
            </div>
          <?php else: ?>
            <form method="POST">
              <input type="hidden" name="action" value="save_grades">
              <input type="hidden" name="tab" value="grades">
              <input type="hidden" name="gsub" value="grade">
              <input type="hidden" name="gsubj" value="<?= $grade_subject_id ?>">
              <input type="hidden" name="gyear" value="<?= $grade_year ?>">
              <input type="hidden" name="gterm" value="<?= e($grade_term) ?>">
              <div class="crd">
                <div class="crdhdr">
                  <h3><i class="fas fa-pen"></i>&nbsp; Year <?= $grade_year ?> — <?= e($grade_term) ?> —
                    <?= e($selected_subject['name']) ?></h3>
                  <button type="submit" class="bsm bpr"><i class="fas fa-save"></i> Save & Notify Students</button>
                </div>
                <div class="tw">
                  <table>
                    <thead>
                      <tr>
                        <th style="text-align:left;padding-left:1rem;min-width:140px">Student</th>
                        <th style="min-width:160px"><?= e($selected_subject['name']) ?><br>
                          <span style="font-weight:400;font-size:.65rem;color:var(--MT)">Marks / 100</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $st):
                        $existing_grade = $existing[$st['id']][$grade_subject_id] ?? null;
                        $existing_marks = $existing_grade ? $existing_grade['marks'] : '';
                        $existing_letter = $existing_grade ? $existing_grade['grade'] : '';
                        ?>
                        <tr>
                          <td style="font-weight:600;background:#f9f6f0;border-right:1.5px solid var(--W);padding:.5rem 1rem">
                            <div><?= e($st['name']) ?></div>
                            <div style="font-size:.7rem;color:var(--MT)">Year <?= $st['year'] ?></div>
                          </td>
                          <td style="padding:4px;text-align:center">
                            <input type="hidden" name="student_ids[]" value="<?= $st['id'] ?>">
                            <input type="hidden" name="subject_ids[]" value="<?= $grade_subject_id ?>">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:3px">
                              <input type="number" name="marks[]" value="<?= $existing_marks ?>" min="0" max="100" step="0.5"
                                placeholder="—" class="einput" style="width:70px;text-align:center" oninput="calcGrade(this)">
                              <span class="grade-badge" style="font-size:.72rem;font-weight:700;
                  <?= $existing_letter ? 'display:inline-block' : 'display:none' ?>">
                                <?= e($existing_letter) ?>
                              </span>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <div
                  style="padding:.85rem 1.25rem;border-top:1.5px solid var(--W);background:#fafaf7;font-size:.75rem;color:var(--MT)">
                  <i class="fas fa-info-circle"></i>
                  Grades auto-calculate as you type. Click <strong>Save & Notify Students</strong> to release results and send
                  notifications.
                </div>
              </div>
            </form>

            <script>
              // Grade scale
              const scale = [
                [90, 'A+'], [80, 'A'], [75, 'A-'], [70, 'B+'], [65, 'B'],
                [60, 'B-'], [55, 'C+'], [50, 'C'], [45, 'C-'], [40, 'D'], [0, 'E']
              ];
              const colors = {
                'A+': '#1b5e20', 'A': '#1b5e20', 'A-': '#1b5e20',
                'B+': '#0d47a1', 'B': '#0d47a1', 'B-': '#0d47a1',
                'C+': '#bf360c', 'C': '#bf360c', 'C-': '#bf360c',
                'D': '#880e4f', 'E': '#b71c1c'
              };
              const bgs = {
                'A+': '#e8f5e9', 'A': '#e8f5e9', 'A-': '#e8f5e9',
                'B+': '#e3f2fd', 'B': '#e3f2fd', 'B-': '#e3f2fd',
                'C+': '#fff3e0', 'C': '#fff3e0', 'C-': '#fff3e0',
                'D': '#fce4ec', 'E': '#ffebee'
              };

              function calcGrade(input) {
                var val = parseFloat(input.value);
                var badge = input.parentElement.querySelector('.grade-badge');
                if (isNaN(val) || input.value === '') {
                  badge.style.display = 'none';
                  badge.textContent = '';
                  return;
                }
                var pct = (val / 100) * 100;
                var grade = 'E';
                for (var i = 0; i < scale.length; i++) {
                  if (pct >= scale[i][0]) { grade = scale[i][1]; break; }
                }
                badge.textContent = grade;
                badge.style.display = 'inline-block';
                badge.style.color = colors[grade];
                badge.style.background = bgs[grade];
                badge.style.padding = '2px 8px';
                badge.style.borderRadius = '6px';
              }
            </script>
          <?php endif; ?>
        <?php endif; ?>

        <!-- ── HOMEWORK OVERSIGHT ── -->
      <?php elseif ($tab === 'homework'): ?>
   <?php
$ok = count(array_filter($HOMEWORK_ALL, fn($h) => $h['status'] === 'submitted'));
$ms = count(array_filter($HOMEWORK_ALL, fn($h) => $h['status'] === 'missing'));
$rt = ($ok + $ms) > 0 ? round($ok / ($ok + $ms) * 100) . '%' : '0%';
?>
        <div class="phdr">
          <h2>Homework Oversight</h2>
          <p>Review and manage all student submissions</p>
          <div class="crd">
  <div class="crdhdr">
    <h3><i class="fas fa-plus-circle"></i>&nbsp; Assign New Homework</h3>
  </div>
  <form method="POST" style="padding:1.1rem 1.4rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="action" value="add_homework">

    <div>
      <label style="display:block;font-size:.7rem;font-weight:700;color:var(--MT);margin-bottom:.3rem">ASSIGN TO</label>
      <select name="hw_student" style="padding:.45rem .7rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem">
        <option value="all">All Students</option>
        <?php foreach ($ALL_STUDENTS as $s): ?>
          <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (Year <?= $s['year'] ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="display:block;font-size:.7rem;font-weight:700;color:var(--MT);margin-bottom:.3rem">TITLE</label>
      <input type="text" name="hw_title" placeholder="e.g. Chapter 6 Worksheet" required
        style="padding:.45rem .7rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem;min-width:200px">
    </div>

    <div>
      <label style="display:block;font-size:.7rem;font-weight:700;color:var(--MT);margin-bottom:.3rem">TYPE</label>
      <select name="hw_type" style="padding:.45rem .7rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem">
        <option>Homework</option>
        <option>Quiz</option>
        <option>Test</option>
      </select>
    </div>

    <div>
      <label style="display:block;font-size:.7rem;font-weight:700;color:var(--MT);margin-bottom:.3rem">DUE DATE</label>
      <input type="date" name="hw_due" required
        style="padding:.43rem .7rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem">
    </div>

    <button type="submit" class="bsm bpr"><i class="fas fa-plus"></i> Add Homework</button>
  </form>
</div>
        </div>
        <div class="srow">
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div>
            <div class="sv"><?= $ok ?></div>
            <div class="sl">Submitted</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-times-circle"></i></div>
            <div class="sv"><?= $ms ?></div>
            <div class="sl">Missing</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-hourglass-half"></i></div>
            <div class="sv">0</div>
            <div class="sl">Pending</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-percentage"></i>
            </div>
            <div class="sv"><?= $rt ?></div>
            <div class="sl">Rate</div>
          </div>
        </div>
        <div class="crd">
          <div class="crdhdr">
            <h3><i class="fas fa-tasks"></i>&nbsp; Submission Status</h3>
          </div>
          <div class="tw">
            <table>
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Year</th>
                  <th>Assignment</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
<tbody>
  <?php foreach ($HOMEWORK_ALL as $h): ?>
    <tr>
      <td style="font-weight:600"><?= e($h['student_name']) ?></td>
      <td>Year <?= $h['student_year'] ?></td>
      <td><?= e($h['title']) ?></td>
      <td style="color:var(--MT)"><?= date('d M Y', strtotime($h['due_date'])) ?></td>
      <td><?php if ($h['status'] === 'submitted'): ?>
          <span class="bok"><i class="fas fa-check-circle"></i> Submitted</span>
        <?php else: ?>
          <span class="bno"><i class="fas fa-times-circle"></i> Missing</span>
        <?php endif; ?>
      </td>
      <td style="white-space:nowrap">
        <form method="POST" style="display:inline">
          <input type="hidden" name="action" value="approve">
          <input type="hidden" name="hw_id" value="<?= $h['id'] ?>">
          <button type="submit" class="bsm bap"><i class="fas fa-check"></i> Approve</button>
        </form>
        &nbsp;
        <form method="POST" style="display:inline">
          <input type="hidden" name="action" value="missing">
          <input type="hidden" name="hw_id" value="<?= $h['id'] ?>">
          <button type="submit" class="bsm bms"><i class="fas fa-times"></i> Missing</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>
            </table>
          </div>
        </div>

        <!-- ── PAYMENTS ── -->
      <?php elseif ($tab === 'payments'): ?>
        <div class="phdr">
          <h2>Payment Tracking</h2>
          <p>Monitor student fee status</p>
        </div>
        <div class="srow">
          <?php
          $paymentRows = [];
          $studentsRes = $conn->query("SELECT id, name, year FROM users WHERE role='student' ORDER BY name");
          if ($studentsRes) {
            while ($row = $studentsRes->fetch_assoc()) {
              $studentId = (int) $row['id'];
              $latestPayment = null;
              $paymentRes = $conn->query("SELECT * FROM payments WHERE user_id=$studentId ORDER BY created_at DESC, id DESC LIMIT 1");
              if ($paymentRes && $paymentRes->num_rows > 0) {
                $latestPayment = $paymentRes->fetch_assoc();
              }

              $status = $latestPayment['status'] ?? 'Invoice Sent';
              $amountValue = $latestPayment ? (float) $latestPayment['amount'] : 450.00;
              $amountDisplay = ($latestPayment['currency'] ?? 'RM') . ' ' . number_format($amountValue, 2);
              $dateDisplay = $latestPayment ? date('j M Y', strtotime($latestPayment['created_at'])) : '—';

              $paymentRows[] = [
                'id' => $studentId,
                'student' => $row['name'],
                'year' => (int) $row['year'],
                'amount' => $amountDisplay,
                'amount_value' => $amountValue,
                'date' => $dateDisplay,
                'status' => $status,
              ];
            }
          }
          if (empty($paymentRows)) {
            $paymentRows = $PAYMENTS;
          }

          $paid = count(array_filter($paymentRows, fn($p) => strtolower($p['status']) === 'paid'));
          $inv = count($paymentRows) - $paid;
          $outstanding = 0;
          foreach ($paymentRows as $p) {
            if (strtolower($p['status']) !== 'paid') {
              $outstanding += (float) ($p['amount_value'] ?? 0);
            }
          }
          ?>
          <div class="sc">
            <div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div>
            <div class="sv"><?= $paid ?></div>
            <div class="sl">Paid</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-file-invoice"></i></div>
            <div class="sv"><?= $inv ?></div>
            <div class="sl">Invoice Sent</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="sv">0</div>
            <div class="sl">Overdue</div>
          </div>
          <div class="sc">
            <div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-coins"></i></div>
            <div class="sv">RM <?= number_format($outstanding, 2) ?></div>
            <div class="sl">Outstanding</div>
          </div>
        </div>
        <div class="crd">
          <div class="crdhdr">
            <h3><i class="fas fa-file-invoice-dollar"></i>&nbsp; Student Payment List</h3>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="send_invoice">
              <input type="hidden" name="student" value="All Students">
              <button type="submit" class="bsm bpr"><i class="fas fa-paper-plane"></i> Send All Invoices</button>
            </form>
          </div>
          <div class="tw">
            <table>
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Year</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($paymentRows as $p): ?>
                  <tr>
                    <td style="font-weight:600"><?= e($p['student']) ?></td>
                    <td>Year <?= (int) $p['year'] ?></td>
                    <td style="font-weight:600"><?= e($p['amount']) ?></td>
                    <td style="color:var(--MT)"><?= e($p['date']) ?></td>
                    <td><?= strtolower($p['status']) === 'paid'
                      ? '<span class="bpd"><i class="fas fa-check"></i> Paid</span>'
                      : '<span class="binv"><i class="fas fa-envelope"></i> ' . e($p['status']) . '</span>' ?></td>
                    <td>
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="send_invoice">
                        <input type="hidden" name="student" value="<?= e($p['student']) ?>">
                        <button type="submit" class="bsd"><i class="fas fa-paper-plane"></i> Send</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>
<?php pageFoot(); ?>