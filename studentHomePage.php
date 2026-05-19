<?php
require_once '../config.php';
require_once '../layout.php';
requireAuth('student');

$user = $_SESSION['user'];
$tab  = $_GET['tab'] ?? 'courses';

// Handle flash messages
$toast = flash('toast');

pageHead('Student Dashboard');
?>
a<div id="student">
  <!-- HEADER -->
  <header class="hdr s-hdr">
    <div class="hbrand">
      <span style="font-size:1.4rem">🍁</span>
      <h1>Maple<em>wood</em></h1>
    </div>
    <div class="hright">
      <div class="ubadge">
        <div class="av"><?= e(strtoupper(substr($user['name'],0,1))) ?></div>
        <span><?= e($user['name']) ?> &nbsp;·&nbsp; Year <?= e($user['year']) ?></span>
      </div>
      <a href="../logout.php" class="obtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>

  <div class="dlayout">
    <!-- SIDEBAR -->
    <aside class="sb s-sb">
      <span class="slbl s-slbl">Menu</span>
      <a class="nl s-nl <?= $tab==='courses'?'on':'' ?>"   href="?tab=courses">   <i class="fas fa-book-open"></i>   Courses</a>
      <a class="nl s-nl <?= $tab==='timetable'?'on':'' ?>" href="?tab=timetable"> <i class="fas fa-calendar-alt"></i> Timetable</a>
      <a class="nl s-nl <?= $tab==='homework'?'on':'' ?>"  href="?tab=homework">  <i class="fas fa-tasks"></i>        Homework Status</a>
      <a class="nl s-nl <?= $tab==='payment'?'on':'' ?>"   href="?tab=payment">   <i class="fas fa-credit-card"></i>  Payment</a>

      <!-- To-Do Widget -->
      <div class="twid">
        <span class="wtl"><i class="fas fa-check-square"></i> To-Do</span>
        <?php foreach (['Review Math notes','Submit BM essay','Study for History','Prepare sports kit','Complete Geo report'] as $i=>$t): ?>
        <label class="titem"><input type="checkbox" <?= $i===0?'checked':'' ?>> <?= e($t) ?></label>
        <?php endforeach; ?>
      </div>

      <!-- Mini Calendar -->
      <div class="cwid" style="margin-top:1.2rem">
        <span class="wtl"><i class="fas fa-calendar"></i> May 2024</span>
        <div class="calgrid">
          <div class="calmo">May 2024</div>
          <div class="caldays">
            <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?>
            <div class="dname"><?= $d ?></div>
            <?php endforeach; ?>
            <?php for ($i=0;$i<3;$i++) echo '<div></div>'; ?>
            <?php for ($d=1;$d<=31;$d++): ?>
            <div class="cday <?= $d===9?'ctoday':'' ?>"><?= $d ?></div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="mc s-mc">

      <?php if ($toast): ?>
      <div class="toast"><i class="fas fa-check-circle"></i> <?= e($toast) ?></div>
      <?php endif; ?>

      <?php // variables $COURSES,$TIMETABLE,$HOMEWORK,$PAYMENTS are in scope from config.php ?>

      <!-- ── COURSES TAB ── -->
      <?php if ($tab === 'courses'): ?>
      <div class="phdr"><h2>Your Courses</h2><p>Year <?= e($user['year']) ?> — Semester 1, 2024</p></div>
      <div class="srow">
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-book"></i></div><div class="sv">8</div><div class="sl">Enrolled</div></div>
        <div class="sc"><div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-tasks"></i></div><div class="sv">3</div><div class="sl">Pending</div></div>
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div><div class="sv">12</div><div class="sl">Submitted</div></div>
        <div class="sc"><div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-star"></i></div><div class="sv">B+</div><div class="sl">Grade</div></div>
      </div>
      <div class="cgrid">
        <?php foreach ($COURSES as $c): ?>
        <div class="cc">
          <div class="cico"><?= $c['icon'] ?></div>
          <div class="cn"><?= e($c['code']) ?></div>
          <div class="cmeta"><?= e($c['name']) ?></div>
          <div class="ppct"><span>Progress</span><span><?= $c['pct'] ?>%</span></div>
          <div class="pbar"><div class="pfill" style="width:<?= $c['pct'] ?>%"></div></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- ── TIMETABLE TAB ── -->
      <?php elseif ($tab === 'timetable'): ?>
      <div class="phdr"><h2>My Timetable</h2><p>Year <?= e($user['year']) ?> — Weekly Schedule</p></div>
      <div class="crd"><div class="tw">
        <table>
          <thead><tr>
            <th>Time</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th>
          </tr></thead>
          <tbody>
            <?php foreach ($TIMETABLE as $row): ?>
            <tr>
              <td class="tcol"><?= e($row['t']) ?></td>
              <?php foreach ($row['s'] as $s): ?>
              <?php if ($s==='BREAK'): ?>
                <td class="cbrk"><i class="fas fa-coffee"></i> Break</td>
              <?php elseif ($s==='SPORT'): ?>
                <td class="cspt">🏃 Sport (2hr)</td>
              <?php else: ?>
                <td><span class="csub"><?= e($s) ?></span></td>
              <?php endif; ?>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div></div>
      <div class="crd" style="padding:1.1rem 1.25rem">
        <div style="display:flex;gap:1.5rem;font-size:.8rem;flex-wrap:wrap">
          <span><span style="display:inline-block;width:10px;height:10px;background:rgba(45,106,79,.08);border-radius:2px;margin-right:5px"></span>Regular Class</span>
          <span><span style="display:inline-block;width:10px;height:10px;background:rgba(212,168,71,.12);border-radius:2px;margin-right:5px"></span>Sport (2hr)</span>
          <span style="color:var(--MT);font-style:italic"><i class="fas fa-coffee" style="margin-right:5px"></i>Break</span>
        </div>
      </div>

      <!-- ── HOMEWORK TAB ── -->
      <?php elseif ($tab === 'homework'): ?>
      <div class="phdr"><h2>Homework Status</h2><p>Track your assignments, quizzes and tests</p></div>
      <div class="srow">
        <?php $ok=array_filter($HOMEWORK,fn($h)=>$h['status']==='submitted'); $ms=array_filter($HOMEWORK,fn($h)=>$h['status']==='missing'); ?>
        <div class="sc"><div class="sico" style="background:#e8f5e9;color:#1b5e20"><i class="fas fa-check-circle"></i></div><div class="sv"><?= count($ok) ?></div><div class="sl">Submitted</div></div>
        <div class="sc"><div class="sico" style="background:#ffebee;color:#b71c1c"><i class="fas fa-times-circle"></i></div><div class="sv"><?= count($ms) ?></div><div class="sl">Missing</div></div>
        <div class="sc"><div class="sico" style="background:#fff3e0;color:#bf360c"><i class="fas fa-clock"></i></div><div class="sv">0</div><div class="sl">Late</div></div>
        <div class="sc"><div class="sico" style="background:rgba(212,168,71,.15);color:#8a6200"><i class="fas fa-percentage"></i></div>
          <div class="sv"><?= count($HOMEWORK)?round(count($ok)/count($HOMEWORK)*100).'%':'0%' ?></div><div class="sl">Completion</div></div>
      </div>
      <div class="crd"><div class="tw">
        <table>
          <thead><tr><th>Item Name</th><th>Type</th><th>Due Date</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($HOMEWORK as $h): ?>
            <tr>
              <td style="font-weight:600"><?= e($h['item']) ?></td>
              <td><?php
                $cls = $h['type']==='Homework' ? 'bhw' : ($h['type']==='Quiz' ? 'bqz' : 'bts');
                echo "<span class=\"{$cls}\">{$h['type']}</span>";
              ?></td>
              <td style="color:var(--MT)"><?= e($h['due']) ?></td>
              <td><?php if ($h['status']==='submitted'):?>
                <span class="bok"><i class="fas fa-check-circle"></i> Submitted</span>
                <?php else: ?>
                <span class="bno"><i class="fas fa-times-circle"></i> Missing</span>
              <?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div></div>

      <!-- ── PAYMENT TAB ── -->
      <?php elseif ($tab === 'payment'): ?>
      <div class="phdr"><h2>Payment</h2><p>Your fee statements and invoice history</p></div>
      <div class="phero">
        <div>
          <div style="font-size:.75rem;opacity:.65;margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em">Current Balance Due</div>
          <div style="font-size:2.3rem;font-weight:700;font-family:'Playfair Display',serif">RM 450.00</div>
          <div style="font-size:.8rem;opacity:.6;margin-top:.28rem">May 2024 — Monthly Tuition Fee</div>
        </div>
        <button onclick="alert('Payment gateway not available in demo.')"
          style="background:var(--O);color:var(--F);border:none;padding:.8rem 1.4rem;border-radius:12px;
          font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;cursor:pointer;font-size:.88rem">
          Pay Now &nbsp;<i class="fas fa-arrow-right"></i>
        </button>
      </div>
      <?php foreach ($PAYMENTS as $p): ?>
      <div class="pcard">
        <div>
          <div style="font-size:.74rem;color:var(--MT);margin-bottom:.22rem"><?= e($p['date']) ?> — Monthly Tuition</div>
          <div style="font-size:1.4rem;font-weight:700;font-family:'Playfair Display',serif;color:var(--F)"><?= e($p['amount']) ?></div>
        </div>
        <?= $p['status']==='Paid'
          ? '<span class="bpd"><i class="fas fa-check"></i> Paid</span>'
          : '<span class="binv"><i class="fas fa-file-invoice"></i> Invoice Sent</span>' ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

    </main>
  </div>
</div>
<?php pageFoot(); ?>
