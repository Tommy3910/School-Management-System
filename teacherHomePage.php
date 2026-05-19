<?php
require_once 'config.php';
require_once 'layout.php';
requireAuth('teacher');

$user = $_SESSION['user'];
$tab  = $_GET['tab'] ?? 'schedules';

// Handle homework approve/mark-missing POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idx    = (int)($_POST['idx'] ?? -1);
    if ($action === 'approve' && isset($_SESSION['hw_statuses'][$idx])) {
        $_SESSION['hw_statuses'][$idx] = 'submitted';
        flash('toast', 'Submission approved.');
    } elseif ($action === 'missing' && isset($_SESSION['hw_statuses'][$idx])) {
        $_SESSION['hw_statuses'][$idx] = 'missing';
        flash('toast', 'Marked as missing.');
    } elseif ($action === 'save_schedule') {
        flash('toast', 'Schedule saved successfully.');
    } elseif ($action === 'save_timetable') {
        $row_ids    = $_POST['row_ids']    ?? [];
        $slots      = $_POST['slots']      ?? [];
        $sport_days = $_POST['sport_days'] ?? [];
        $tt_group   = $_POST['tt_group']   ?? '10-11';

        // Number of editable columns per group (BREAK/SPORT cells send hidden values too)
        $col_count = ($tt_group === '1-6') ? 5 : 7;

        $slot_idx = 0;
        foreach ($row_ids as $rid) {
            $rid      = (int)$rid;
            $is_sport = in_array((string)$rid, $sport_days) ? 1 : 0;
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
    } elseif ($action === 'send_invoice') {
        flash('toast', 'Invoice sent to ' . ($_POST['student'] ?? 'student') . '.');
    }
    $return_url = "teacherHomePage.php?tab={$tab}";
    if (!empty($_POST['tt_year'])) {
        $return_url .= '&year=' . (int)$_POST['tt_year'];
    }
    redirect($return_url);
}

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
      <span style="color:rgba(255,255,255,.75);font-size:.8rem"><i class="fas fa-chalkboard-teacher"></i> <?= e($user['name']) ?></span>
      <a href="logout.php" class="obtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>

  <div class="dlayout">
    <!-- SIDEBAR -->
    <aside class="sb t-sb">
      <span class="slbl t-slbl">Management</span>
      <a class="nl t-nl <?= $tab==='schedules'?'on':'' ?>" href="?tab=schedules"><i class="fas fa-calendar-alt"></i> Schedules</a>
      <a class="nl t-nl <?= $tab==='timetable'?'on':'' ?>" href="?tab=timetable"><i class="fas fa-table"></i> Timetable Editor</a>
      <a class="nl t-nl <?= $tab==='grades'?'on':'' ?>"    href="?tab=grades">   <i class="fas fa-chart-bar"></i> Student Grades</a>
      <a class="nl t-nl <?= $tab==='homework'?'on':'' ?>"  href="?tab=homework"> <i class="fas fa-tasks"></i> Homework Oversight</a>
      <a class="nl t-nl <?= $tab==='payments'?'on':'' ?>"  href="?tab=payments"> <i class="fas fa-file-invoice-dollar"></i> Payments</a>
    </aside>

    <!-- MAIN -->
    <main class="mc t-mc">
      <?php if ($toast): ?>
      <div class="toast"><i class="fas fa-check-circle"></i> <?= e($toast) ?></div>
      <?php endif; ?>

      <?php // variables $SPORT,$DAYS,$TIMETABLE,$GRADES,$ALL_STUDENTS,$PAYMENTS are in scope from config.php ?>

      <!-- ── SCHEDULES ── -->
      <?php if ($tab === 'schedules'): ?>
      <div class="phdr"><h2>Sport Schedules</h2><p>Manage sport day assignments by year group</p></div>
      <div class="srow">
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-users"></i></div><div class="sv">11</div><div class="sl">Year Groups</div></div>
        <div class="sc"><div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-running"></i></div><div class="sv">3</div><div class="sl">Sport Groups</div></div>
        <div class="sc"><div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-clock"></i></div><div class="sv">2 hr</div><div class="sl">Session</div></div>
        <div class="sc"><div class="sico" style="background:#fce4ec;color:#880e4f"><i class="fas fa-calendar-check"></i></div><div class="sv">Mon–Fri</div><div class="sl">School Week</div></div>
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
                <option <?= $d===$s['day']?'selected':'' ?>><?= e($d) ?></option>
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
  $tt_year = (int)($_GET['year'] ?? 10);
  if ($tt_year <= 6)      $tt_group = '1-6';
  elseif ($tt_year <= 9)  $tt_group = '7-9';
  else                    $tt_group = '10-11';

  $tt_rows = [];
  $res = $conn->query(
      "SELECT * FROM timetable
       WHERE year = $tt_year AND year_group = '$tt_group'
       ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday')"
  );
  if ($res) while ($r = $res->fetch_assoc()) $tt_rows[] = $r;

  // Column headers per group
  if ($tt_group === '1-6') {
      $headers = ['8:00–9:00','9:00–10:00','10:00–10:45 (Break)','10:45–11:45','11:45–12:45'];
      $cols    = ['p1','p2','p3','p4','p5'];
  } else {
      $headers = ['8:00–8:45','8:45–9:30','9:30–10:00 (30min)','10:00–11:00 (Break)','11:00–11:45','11:45–12:30','12:30–1:15','1:15–1:45 (30min)'];
      $cols    = ['p1','p2','p3','p4','p5','p6','p7'];
  }
?>
<div class="phdr"><h2>Timetable Editor</h2><p>Edit subject slots — changes reflect in student view instantly</p></div>

<div style="display:flex;gap:.75rem;margin-bottom:1.2rem;align-items:center;flex-wrap:wrap">
  <form method="GET">
    <input type="hidden" name="tab" value="timetable">
    <select name="year" onchange="this.form.submit()"
      style="padding:.38rem .75rem;border-radius:8px;border:1.5px solid var(--W);font-family:'Plus Jakarta Sans',sans-serif;font-size:.83rem;background:#fff">
      <?php foreach (range(1,11) as $y): ?>
      <option value="<?= $y ?>" <?= $y===$tt_year?'selected':'' ?>>Year <?= $y ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <span style="font-size:.75rem;color:var(--MT)">
    Group: <strong><?= $tt_group ?></strong> —
    <?php if ($tt_group==='1-6') echo '5 periods · 1 hr each · 8:00–12:45';
    else echo '7 periods · 45 min each · 8:00–1:45'; ?>
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
            <td style="font-weight:600;font-size:.78rem;color:var(--MT);background:#f9f6f0;border-right:1.5px solid var(--W);padding:.5rem 1rem;white-space:nowrap">
              <?= e($row['day']) ?>
              <label style="display:flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:500;margin-top:.3rem;cursor:pointer;color:var(--P)">
                <input type="checkbox" name="sport_days[]" value="<?= $row['id'] ?>" <?= $row['sport_day']?'checked':'' ?>>
                Sport day
              </label>
            </td>
            <?php foreach ($cols as $idx => $col): ?>
              <?php if ($tt_group !== '1-6' && $idx === 3): ?>
                <td style="background:#f3f4f6;text-align:center;font-size:.72rem;color:var(--MT);padding:4px">
                  Break<input type="hidden" name="slots[]" value="BREAK">
                </td>
              <?php endif; ?>
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
                  <input class="einput" type="text" name="slots[]" value="<?= e($val) ?>"
                    style="min-width:70px">
                </td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="padding:1rem 1.25rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;border-top:1.5px solid var(--W)">
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

      <!-- ── GRADES ── -->
      <?php elseif ($tab === 'grades'): ?>
      <div class="phdr"><h2>Student Grades</h2><p>Academic performance overview</p></div>
      <div class="srow">
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-trophy"></i></div><div class="sv">2</div><div class="sl">Grade A</div></div>
        <div class="sc"><div class="sico" style="background:rgba(45,106,79,.1);color:var(--S)"><i class="fas fa-thumbs-up"></i></div><div class="sv">3</div><div class="sl">Grade B</div></div>
        <div class="sc"><div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-exclamation"></i></div><div class="sv">1</div><div class="sl">Needs Help</div></div>
        <div class="sc"><div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-percentage"></i></div><div class="sv">76%</div><div class="sl">Class Avg</div></div>
      </div>
      <div class="crd">
        <div class="crdhdr"><h3><i class="fas fa-user-graduate"></i>&nbsp; All Students</h3>
          <a href="?tab=grades" class="bsm bpr"><i class="fas fa-download"></i> Export</a>
        </div>
        <div class="tw">
          <table>
            <thead><tr><th>Student</th><th>Year</th><th>BM</th><th>Math</th><th>Science</th><th>English</th><th>History</th><th>Overall</th></tr></thead>
            <tbody>
              <?php foreach ($GRADES as $g): ?>
              <tr>
                <td style="font-weight:600"><?= e($g[0]) ?></td>
                <td>Year <?= $g[1] ?></td>
                <td><?= $g[2] ?></td><td><?= $g[3] ?></td><td><?= $g[4] ?></td>
                <td><?= $g[5] ?></td><td><?= $g[6] ?></td>
                <td><strong style="color:var(--F)"><?= e($g[7]) ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── HOMEWORK OVERSIGHT ── -->
      <?php elseif ($tab === 'homework'): ?>
      <?php
        $hwSt = $_SESSION['hw_statuses'];
        $ok   = count(array_filter($hwSt, fn($s)=>$s==='submitted'));
        $ms   = count(array_filter($hwSt, fn($s)=>$s==='missing'));
        $rt   = ($ok+$ms) > 0 ? round($ok/($ok+$ms)*100).'%' : '0%';
      ?>
      <div class="phdr"><h2>Homework Oversight</h2><p>Review and manage all student submissions</p></div>
      <div class="srow">
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div><div class="sv"><?= $ok ?></div><div class="sl">Submitted</div></div>
        <div class="sc"><div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-times-circle"></i></div><div class="sv"><?= $ms ?></div><div class="sl">Missing</div></div>
        <div class="sc"><div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-hourglass-half"></i></div><div class="sv">0</div><div class="sl">Pending</div></div>
        <div class="sc"><div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-percentage"></i></div><div class="sv"><?= $rt ?></div><div class="sl">Rate</div></div>
      </div>
      <div class="crd">
        <div class="crdhdr"><h3><i class="fas fa-tasks"></i>&nbsp; Submission Status</h3></div>
        <div class="tw">
          <table>
            <thead><tr><th>Student</th><th>Year</th><th>Assignment</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($ALL_STUDENTS as $i => $s): ?>
              <tr>
                <td style="font-weight:600"><?= e($s['name']) ?></td>
                <td>Year <?= $s['year'] ?></td>
                <td><?= e($s['hw']) ?></td>
                <td style="color:var(--MT)"><?= e($s['due']) ?></td>
                <td><?php if ($hwSt[$i]==='submitted'): ?>
                  <span class="bok"><i class="fas fa-check-circle"></i> Submitted</span>
                <?php else: ?>
                  <span class="bno"><i class="fas fa-times-circle"></i> Missing</span>
                <?php endif; ?></td>
                <td style="white-space:nowrap">
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="idx" value="<?= $i ?>">
                    <button type="submit" class="bsm bap"><i class="fas fa-check"></i> Approve</button>
                  </form>
                  &nbsp;
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="missing">
                    <input type="hidden" name="idx" value="<?= $i ?>">
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
      <div class="phdr"><h2>Payment Tracking</h2><p>Monitor student fee status</p></div>
      <div class="srow">
        <?php $paid=count(array_filter($PAYMENTS,fn($p)=>$p['status']==='Paid')); $inv=count($PAYMENTS)-$paid; ?>
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div><div class="sv"><?= $paid ?></div><div class="sl">Paid</div></div>
        <div class="sc"><div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-file-invoice"></i></div><div class="sv"><?= $inv ?></div><div class="sl">Invoice Sent</div></div>
        <div class="sc"><div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-exclamation-triangle"></i></div><div class="sv">0</div><div class="sl">Overdue</div></div>
        <div class="sc"><div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-coins"></i></div><div class="sv">RM <?= $inv*450 ?></div><div class="sl">Outstanding</div></div>
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
            <thead><tr><th>Student</th><th>Year</th><th>Amount</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($PAYMENTS as $p): ?>
              <tr>
                <td style="font-weight:600"><?= e($p['student']) ?></td>
                <td>Year <?= $p['year'] ?></td>
                <td style="font-weight:600"><?= e($p['amount']) ?></td>
                <td style="color:var(--MT)"><?= e($p['date']) ?></td>
                <td><?= $p['status']==='Paid'
                  ? '<span class="bpd"><i class="fas fa-check"></i> Paid</span>'
                  : '<span class="binv"><i class="fas fa-envelope"></i> Invoice Sent</span>' ?></td>
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
