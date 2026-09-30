<?php
function pageHead($title = 'Maplewood') {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Maplewood — {$title}</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
HTML;
    echo file_get_contents(__DIR__ . '/style.css');
    echo "</style>\n</head>\n<body>\n";
}

function renderChatWidget() {
    global $conn;

    $userName = $_SESSION['user']['name'] ?? '';
    $firstName = $userName !== '' ? explode(' ', $userName)[0] : '';
    $greetName = $firstName !== ''
        ? "Hi {$firstName}! How can I help you today?"
        : "Hi! How can I help you today?";

    $userId = (int) ($_SESSION['user']['id'] ?? 0);

    // ── Load this student's real chat history from the database ──
    $historyHtml = '';
    if ($userId > 0 && $conn) {
        $stmt = $conn->prepare("SELECT role, message FROM chatbot_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $cls = $row['role'] === 'user' ? 'user' : 'bot';
            $historyHtml .= '<div class="mwMsg ' . $cls . '">' . e($row['message']) . '</div>';
        }
        $stmt->close();
    }

    // If there's no saved history yet, just show the greeting (not saved to DB
    // until the student actually sends their first real message)
    if ($historyHtml === '') {
        $historyHtml = '<div class="mwMsg bot">' . e($greetName) . '</div>';
    }

    echo <<<HTML
<style>
#mwChatBtn{position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;
  background:var(--F,#0f2318);color:#fff;border:none;box-shadow:0 6px 20px rgba(0,0,0,.25);
  cursor:pointer;font-size:1.4rem;z-index:9999;display:flex;align-items:center;justify-content:center;
  transition:transform .2s}
#mwChatBtn:hover{transform:scale(1.06)}
#mwChatWin{position:fixed;bottom:92px;right:24px;width:380px;max-width:calc(100vw - 32px);
  height:460px;max-height:calc(100vh - 140px);background:#fff;border-radius:16px;
  box-shadow:0 12px 40px rgba(0,0,0,.25);display:none;flex-direction:column;overflow:hidden;z-index:9999;
  font-family:'Plus Jakarta Sans',sans-serif}
#mwChatWin.open{display:flex}
#mwChatHdr{background:var(--F,#0f2318);color:#fff;padding:.85rem 1.1rem;display:flex;
  align-items:center;justify-content:space-between;font-weight:700;font-size:.9rem}
#mwChatHdrBtns{display:flex;align-items:center;gap:.6rem}
#mwChatHdr button{background:none;border:none;color:#fff;cursor:pointer;font-size:.95rem;opacity:.75}
#mwChatHdr button:hover{opacity:1}
#mwChatBody{flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.6rem;
  background:#f7f3ec}
.mwMsg{max-width:82%;padding:.55rem .8rem;border-radius:12px;font-size:.82rem;line-height:1.45;
  white-space:pre-wrap}
.mwMsg.bot{background:#fff;color:#111a14;align-self:flex-start;border:1px solid #e8e0cc}
.mwMsg.user{background:var(--F,#0f2318);color:#fff;align-self:flex-end}
.mwMsg.typing{color:#5a6b60;font-style:italic}
#mwChatForm{display:flex;border-top:1px solid #e8e0cc;padding:.6rem;gap:.5rem}
#mwChatInput{flex:1;border:1.5px solid #e8e0cc;border-radius:10px;padding:.5rem .7rem;
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.82rem;outline:none}
#mwChatInput:focus{border-color:var(--S,#2d6a4f)}
#mwChatForm button{background:var(--F,#0f2318);color:#fff;border:none;border-radius:10px;
  padding:0 1rem;cursor:pointer;font-size:.85rem}
</style>

<button id="mwChatBtn" onclick="mwToggleChat()" title="Ask Maplewood Assistant">
  <i class="fas fa-comment-dots"></i>
</button>

<div id="mwChatWin">
  <div id="mwChatHdr">
    <span><i class="fas fa-leaf"></i> Maplewood Assistant</span>
    <div id="mwChatHdrBtns">
      <button onclick="mwClearChat()" title="Clear chat history"><i class="fas fa-trash"></i></button>
      <button onclick="mwToggleChat()" title="Close"><i class="fas fa-times"></i></button>
    </div>
  </div>
  <div id="mwChatBody">{$historyHtml}</div>
  <form id="mwChatForm" onsubmit="mwSendMsg(event)">
    <input id="mwChatInput" type="text" placeholder="Ask a question..." autocomplete="off">
    <button type="submit"><i class="fas fa-paper-plane"></i></button>
  </form>
</div>

<script>
function mwToggleChat(){
  var win = document.getElementById('mwChatWin');
  win.classList.toggle('open');
  if (win.classList.contains('open')) {
    var body = document.getElementById('mwChatBody');
    body.scrollTop = body.scrollHeight;
  }
}

function mwFormatMarkdown(text) {
  // Escape HTML first so nothing unexpected can inject markup
  var div = document.createElement('div');
  div.textContent = text;
  var escaped = div.innerHTML;

  // Bold: **text** -> <strong>text</strong>
  escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

  // Bullet lines starting with "* " -> proper <ul><li>
  var lines = escaped.split('\\n');
  var html = '';
  var inList = false;
  lines.forEach(function(line) {
    var trimmed = line.trim();
    if (trimmed.startsWith('* ') || trimmed.startsWith('- ')) {
      if (!inList) { html += '<ul style="margin:.3rem 0 .3rem 1.1rem;padding:0">'; inList = true; }
      html += '<li style="margin-bottom:.25rem">' + trimmed.substring(2) + '</li>';
    } else {
      if (inList) { html += '</ul>'; inList = false; }
      if (trimmed !== '') html += '<div>' + trimmed + '</div>';
    }
  });
  if (inList) html += '</ul>';

  return html;
}


document.querySelectorAll('#mwChatBody .mwMsg.bot').forEach(function(div) {
  div.innerHTML = mwFormatMarkdown(div.textContent);
});

async function mwSendMsg(e){
  e.preventDefault();
  var input = document.getElementById('mwChatInput');
  var body = document.getElementById('mwChatBody');
  var msg = input.value.trim();
  if(!msg) return;

  body.insertAdjacentHTML('beforeend', '<div class="mwMsg user"></div>');
  body.lastElementChild.textContent = msg;
  input.value = '';
  body.scrollTop = body.scrollHeight;

  body.insertAdjacentHTML('beforeend', '<div class="mwMsg bot typing" id="mwTyping">Thinking...</div>');
  body.scrollTop = body.scrollHeight;

  try {
    var res = await fetch('api_chat.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({message: msg})
    });
    var data = await res.json();
    document.getElementById('mwTyping').remove();
    var reply = data.reply || data.error || 'Something went wrong.';
    body.insertAdjacentHTML('beforeend', '<div class="mwMsg bot"></div>');
    body.lastElementChild.innerHTML = mwFormatMarkdown(reply);
  } catch(err) {
    document.getElementById('mwTyping').remove();
    body.insertAdjacentHTML('beforeend', '<div class="mwMsg bot">Connection error. Please try again.</div>');
  }
  body.scrollTop = body.scrollHeight;
}

async function mwClearChat(){
  if (!confirm('Clear your entire chat history? This cannot be undone.')) return;
  try {
    await fetch('clear_chat_history.php', { method: 'POST' });
    document.getElementById('mwChatBody').innerHTML = '';
    location.reload();
  } catch(err) {
    alert('Could not clear chat history. Please try again.');
  }
}
</script>
HTML;
}

function pageFoot() {
    renderChatWidget();
    
    // Render shared modals (Calendar Planner, Event Details, Reminder Details, Dashboard Tour)
    echo <<<HTML
<!-- ── FULLSCREEN CALENDAR PLANNER OVERLAY ── -->
<div id="calPlannerOverlay" class="cal-planner-overlay">
  <div class="cal-pl-hdr">
    <div class="cal-pl-title"><i class="fas fa-calendar-alt" style="color:var(--O)"></i> Maple<em>wood</em> Academic &amp; Holiday Planner</div>
    <div class="cal-pl-actions">
      <button class="cal-btn" onclick="mwCalNav(-1)"><i class="fas fa-chevron-left"></i> Prev</button>
      <span id="calPlMonthYear" style="font-weight:700;font-size:1.1rem;min-width:140px;text-align:center">June 2026</span>
      <button class="cal-btn" onclick="mwCalNav(1)">Next <i class="fas fa-chevron-right"></i></button>
      <button class="cal-btn" onclick="mwCalToday()"><i class="fas fa-dot-circle"></i> Today</button>
      <button class="cal-btn cal-btn-close" onclick="mwCloseCalendar()"><i class="fas fa-times"></i> Close</button>
    </div>
  </div>
  <div class="cal-pl-legend">
    <span style="font-weight:700;color:var(--F)"><i class="fas fa-palette"></i> Event Legend:</span>
    <div class="legend-item"><span class="legend-chip legend-hol"></span> <span>Public Holiday (National)</span></div>
    <div class="legend-item"><span class="legend-chip legend-evt"></span> <span>School Special Event</span></div>
    <div class="legend-item"><span class="legend-chip legend-exam"></span> <span>Examination &amp; Test</span></div>
    <div class="legend-item"><span class="legend-chip legend-sport"></span> <span>Sports Day / Session</span></div>
    <div class="legend-item"><span class="legend-chip legend-hw"></span> <span>Assignment Deadline</span></div>
  </div>
  <div style="flex:1;overflow-y:auto;display:flex;flex-direction:column">
    <div class="cal-grid-fs" id="calPlGrid"></div>
  </div>
</div>

<!-- ── EVENT DETAIL MODAL ── -->
<div id="calEventModal" class="mw-modal">
  <div class="mw-modal-card">
    <div class="mw-modal-hdr" id="calEvtHdr" style="background:var(--F)">
      <h3 id="calEvtTitle" style="font-size:1.05rem;display:flex;align-items:center;gap:.5rem">Event</h3>
      <button onclick="mwCloseEvtModal()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:1.1rem"><i class="fas fa-times"></i></button>
    </div>
    <div class="mw-modal-body">
      <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap">
        <span id="calEvtBadge" class="course-rem-badge" style="background:#e8f5e9;color:#1b5e20">Badge</span>
        <span id="calEvtTime" style="font-size:.8rem;color:var(--MT)"><i class="fas fa-clock"></i> Time</span>
      </div>
      <p id="calEvtDesc" style="font-size:.88rem;color:var(--T);line-height:1.6;margin-bottom:1rem"></p>
      <div id="calEvtAction" style="display:none;margin-top:.75rem"></div>
    </div>
    <div class="mw-modal-foot">
      <button class="bsd bsm" onclick="mwCloseEvtModal()">Close</button>
    </div>
  </div>
</div>

<!-- ── REMINDER DETAIL MODAL (Requirements 8 & 9) ── -->
<div id="reminderDetailModal" class="mw-modal">
  <div class="mw-modal-card">
    <div class="mw-modal-hdr" style="background:linear-gradient(135deg,var(--F),var(--S))">
      <h3 style="font-size:1.05rem;display:flex;align-items:center;gap:.5rem"><i class="fas fa-bell" style="color:var(--O)"></i> Course Reminder</h3>
      <button onclick="mwCloseReminderModal()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:1.1rem"><i class="fas fa-times"></i></button>
    </div>
    <div class="mw-modal-body">
      <div id="remCourseBanner" class="course-rem-banner">
        <div id="remCourseIcon" style="font-size:2.2rem">📖</div>
        <div>
          <div id="remCourseCode" style="font-size:.75rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.05em">COURSE CODE</div>
          <div id="remCourseName" style="font-size:1.1rem;font-weight:700;color:var(--F)">Course Name</div>
        </div>
      </div>
      <div style="margin-bottom:.8rem">
        <span style="font-size:.78rem;color:var(--MT)">From: <strong id="remSender" style="color:var(--F)">Teacher</strong> &nbsp;·&nbsp; <span id="remDate">Date</span></span>
      </div>
      <h4 id="remTitle" style="font-size:1.02rem;color:var(--F);margin-bottom:.5rem;font-weight:700">Reminder Title</h4>
      <p id="remMessage" style="font-size:.88rem;color:var(--T);line-height:1.65;background:#faf8f5;padding:.9rem;border-radius:10px;border:1px solid var(--W);white-space:pre-wrap"></p>
    </div>
    <div class="mw-modal-foot">
      <button class="bsd bsm" onclick="mwCloseReminderModal()">Close</button>
      <a id="remCourseLink" href="#" class="bpr bsm" style="display:inline-flex;align-items:center;gap:.35rem">Go to Course Page <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</div>

<!-- ── DASHBOARD TOUR OVERLAY & CARD ── -->
<div id="mwTourBackdrop" onclick="mwEndTour()"></div>
<div id="mwTourCard">
  <span id="mwTourStepBadge" class="tour-step-badge">Step 1 of 6</span>
  <h3 id="mwTourTitle" class="tour-title"><i class="fas fa-compass"></i> Welcome</h3>
  <p id="mwTourDesc" class="tour-desc">Tour description</p>
  <div class="tour-nav">
    <div class="tour-dots" id="mwTourDots"></div>
    <div class="tour-btns">
      <button class="tbtn tbtn-skip" onclick="mwEndTour()">Skip</button>
      <button class="tbtn tbtn-prev" id="mwTourPrevBtn" onclick="mwPrevTourStep()"><i class="fas fa-arrow-left"></i></button>
      <button class="tbtn tbtn-next" id="mwTourNextBtn" onclick="mwNextTourStep()">Next <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
</div>

<script>
function togglePw(id,btn){
  var i=document.getElementById(id);
  i.type=i.type==='password'?'text':'password';
  btn.innerHTML=i.type==='text'?'<i class="fas fa-eye-slash"></i>':'<i class="fas fa-eye"></i>';
}
function otpMove(el,nextId){
  if(el.value){el.classList.add('filled');if(nextId)document.getElementById(nextId).focus();}
  else el.classList.remove('filled');
}
function otpBack(e,prevId,el){
  if(e.key==='Backspace'&&!el.value&&prevId){
    var p=document.getElementById(prevId);p.value='';p.classList.remove('filled');p.focus();
  }
}
function mwToggleSidebar(){
  var sb = document.querySelector('.sb');
  if (!sb) return;
  sb.classList.toggle('collapsed');
  localStorage.setItem('mwSidebarCollapsed', sb.classList.contains('collapsed') ? '1' : '0');
}
(function(){
  var sb = document.querySelector('.sb');
  if (sb && localStorage.getItem('mwSidebarCollapsed') === '1') {
    sb.classList.add('collapsed');
  }
})();
function checkStrength(pw,barPrefix,labelId){
  var bars=[1,2,3,4].map(function(n){return document.getElementById(barPrefix+n);});
  var lbl=document.getElementById(labelId);
  var score=0;
  if(pw.length>=8)score++;
  if(/[A-Z]/.test(pw))score++;
  if(/[0-9]/.test(pw))score++;
  if(/[^A-Za-z0-9]/.test(pw))score++;
  var colors=['#ef5350','#ff9800','#42a5f5','#66bb6a'];
  var labels=['Weak','Fair','Good','Strong'];
  bars.forEach(function(b,i){if(b)b.style.background=i<score?colors[score-1]:'#e8e0cc';});
  if(lbl)lbl.textContent=pw.length===0?'Enter a password':(labels[score-1]||'Weak');
  if(lbl)lbl.style.color=pw.length===0?'#5a6b60':colors[score-1];
}

// ── FULLSCREEN CALENDAR PLANNER LOGIC ──
var mwCalCurrentYear = new Date().getFullYear();
var mwCalCurrentMonth = new Date().getMonth();
var mwAllEvents = [];
var mwMonthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

async function mwLoadCalendarEvents() {
  try {
    var res = await fetch('api_calendar_events.php?year=' + mwCalCurrentYear + '&month=' + (mwCalCurrentMonth + 1));
    var data = await res.json();
    mwAllEvents = data.events || [];
    mwRenderFsCalendar();
  } catch(err) {
    console.error('Failed to load events', err);
    mwRenderFsCalendar();
  }
}

function mwOpenCalendar() {
  var overlay = document.getElementById('calPlannerOverlay');
  if (!overlay) return;
  overlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  mwLoadCalendarEvents();
}

function mwCloseCalendar() {
  var overlay = document.getElementById('calPlannerOverlay');
  if (!overlay) return;
  overlay.classList.remove('active');
  document.body.style.overflow = '';
}

function mwCalNav(delta) {
  mwCalCurrentMonth += delta;
  if (mwCalCurrentMonth < 0) { mwCalCurrentMonth = 11; mwCalCurrentYear--; }
  else if (mwCalCurrentMonth > 11) { mwCalCurrentMonth = 0; mwCalCurrentYear++; }
  mwLoadCalendarEvents();
}

function mwCalToday() {
  var now = new Date();
  mwCalCurrentYear = now.getFullYear();
  mwCalCurrentMonth = now.getMonth();
  mwLoadCalendarEvents();
}

function mwRenderFsCalendar() {
  var titleEl = document.getElementById('calPlMonthYear');
  if (titleEl) titleEl.textContent = mwMonthNames[mwCalCurrentMonth] + ' ' + mwCalCurrentYear;

  var grid = document.getElementById('calPlGrid');
  if (!grid) return;
  grid.innerHTML = '';

  var dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  dayHeaders.forEach(function(dh) {
    var hEl = document.createElement('div');
    hEl.className = 'cal-head-day';
    hEl.textContent = dh;
    grid.appendChild(hEl);
  });

  var firstDay = new Date(mwCalCurrentYear, mwCalCurrentMonth, 1).getDay();
  var daysInMonth = new Date(mwCalCurrentYear, mwCalCurrentMonth + 1, 0).getDate();
  var daysInPrevMonth = new Date(mwCalCurrentYear, mwCalCurrentMonth, 0).getDate();

  var todayDate = new Date();
  var isCurrentMonth = (todayDate.getFullYear() === mwCalCurrentYear && todayDate.getMonth() === mwCalCurrentMonth);
  var todayNum = todayDate.getDate();

  // Prev month padding
  for (var i = firstDay - 1; i >= 0; i--) {
    var cell = document.createElement('div');
    cell.className = 'cal-day-cell other-month';
    var num = daysInPrevMonth - i;
    cell.innerHTML = '<div class="cal-day-num">' + num + '</div>';
    grid.appendChild(cell);
  }

  // Days in current month
  for (var d = 1; d <= daysInMonth; d++) {
    var cell = document.createElement('div');
    cell.className = 'cal-day-cell';
    var isToday = (isCurrentMonth && d === todayNum);
    if (isToday) cell.classList.add('today');

    var dStr = String(d).padStart(2, '0');
    var mStr = String(mwCalCurrentMonth + 1).padStart(2, '0');
    var isoDate = mwCalCurrentYear + '-' + mStr + '-' + dStr;

    var numHtml = '<div class="cal-day-num"><span>' + d + '</span>' + (isToday ? '<span class="cal-today-badge">Today</span>' : '') + '</div>';
    cell.innerHTML = numHtml;

    // Filter events for this day
    var dayEvents = mwAllEvents.filter(function(e) { return e.date === isoDate; });
    dayEvents.forEach(function(ev) {
      var chip = document.createElement('div');
      chip.className = 'cal-chip ' + (ev.type || 'special_event');
      chip.innerHTML = '<i class="' + (ev.icon || 'fas fa-calendar') + '"></i> <span>' + ev.title + '</span>';
      chip.onclick = (function(item) {
        return function(e) {
          e.stopPropagation();
          mwShowEventModal(item);
        };
      })(ev);
      cell.appendChild(chip);
    });

    grid.appendChild(cell);
  }

  // Next month padding to fill grid
  var totalCells = firstDay + daysInMonth;
  var nextDays = (7 - (totalCells % 7)) % 7;
  for (var n = 1; n <= nextDays; n++) {
    var cell = document.createElement('div');
    cell.className = 'cal-day-cell other-month';
    cell.innerHTML = '<div class="cal-day-num">' + n + '</div>';
    grid.appendChild(cell);
  }
}

function mwShowEventModal(ev) {
  document.getElementById('calEvtTitle').innerHTML = '<i class="' + (ev.icon || 'fas fa-calendar') + '"></i> ' + ev.title;
  var badge = document.getElementById('calEvtBadge');
  badge.textContent = ev.badge || 'Event';
  badge.style.background = ev.bg || '#f5f3ff';
  badge.style.color = ev.color || '#5b21b6';
  badge.style.borderColor = ev.border || '#ddd6fe';

  document.getElementById('calEvtTime').innerHTML = '<i class="fas fa-clock"></i> ' + (ev.time || 'All Day') + ' &nbsp;·&nbsp; ' + ev.date;
  document.getElementById('calEvtDesc').textContent = ev.description || 'No additional details.';

  var act = document.getElementById('calEvtAction');
  if (ev.type === 'homework') {
    act.style.display = 'block';
    act.innerHTML = '<a href="studentHomePage.php?tab=homework" class="bpr bsm"><i class="fas fa-tasks"></i> Go to Homework Page</a>';
  } else {
    act.style.display = 'none';
  }

  document.getElementById('calEventModal').classList.add('open');
}

function mwCloseEvtModal() {
  document.getElementById('calEventModal').classList.remove('open');
}

// ── REMINDER MODAL LOGIC (Requirements 8 & 9) ──
var mwCourseNames = {
  'BM101': 'Bahasa Malaysia',
  'MT102': 'Mathematics',
  'SC103': 'Science',
  'EN104': 'English',
  'HI105': 'History',
  'GE106': 'Geography',
  'PE107': 'Physical Education',
  'AR108': 'Art'
};
var mwCourseIcons = {
  'BM101': '📖', 'MT102': '📐', 'SC103': '🔬', 'EN104': '✏️',
  'HI105': '🏛️', 'GE106': '🌍', 'PE107': '⚽', 'AR108': '🎨'
};

function mwShowReminder(notifId, title, message, courseCode, sender, date) {
  document.getElementById('remTitle').textContent = title || 'Course Reminder';
  document.getElementById('remMessage').textContent = message || '';
  document.getElementById('remSender').textContent = sender || 'Teacher';
  document.getElementById('remDate').textContent = date || '';

  var banner = document.getElementById('remCourseBanner');
  var link = document.getElementById('remCourseLink');
  var codeEl = document.getElementById('remCourseCode');
  var nameEl = document.getElementById('remCourseName');
  var iconEl = document.getElementById('remCourseIcon');

  if (courseCode) {
    banner.style.display = 'flex';
    codeEl.textContent = courseCode;
    nameEl.textContent = mwCourseNames[courseCode] || courseCode;
    iconEl.textContent = mwCourseIcons[courseCode] || '📚';
    link.style.display = 'inline-flex';
    link.href = 'coursePage.php?code=' + encodeURIComponent(courseCode);
  } else {
    banner.style.display = 'none';
    link.style.display = 'none';
  }

  document.getElementById('reminderDetailModal').classList.add('open');

  if (notifId) {
    fetch('api_mark_notification_read.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'id=' + notifId
    });
  }
}

function mwCloseReminderModal() {
  document.getElementById('reminderDetailModal').classList.remove('open');
}

// ── DASHBOARD TOUR LOGIC (Requirement 4) ──
var mwTourSteps = [];
var mwCurrentTourIdx = 0;

function mwStartTour(customSteps) {
  if (customSteps && customSteps.length) {
    mwTourSteps = customSteps;
  } else {
    // Default student/teacher steps
    var isTeacher = document.getElementById('teacher') !== null;
    if (isTeacher) {
      mwTourSteps = [
        { el: '.hbrand', title: 'Welcome Teacher', desc: 'Welcome to Maplewood Teacher & Admin Portal. Here you can manage classes, assign work, and guide students.' },
        { el: '.sb', title: 'Management Menu', desc: 'Easily navigate through Sports Schedules, Timetable Editor, Student Grades, Homework Oversight, Payments, and Materials.' },
        { el: '.mc', title: 'Main Workspace', desc: 'View and update records, enter student grades, assign tasks, and distribute course files in real time.' },
        { el: '.nbell', title: 'Notifications & Alerts', desc: 'Stay updated with automated alerts and reminders across the learning center.' },
        { el: '#mwChatBtn', title: 'AI Assistant', desc: 'Access your smart AI Assistant anytime for quick school knowledge lookups and questions.' }
      ];
    } else {
      mwTourSteps = [
        { el: '.hbrand', title: 'Welcome to Maplewood', desc: 'Your personalized academic portal. Access courses, schedules, assignments, and grades all in one place.' },
        { el: '.sb', title: 'Navigation Sidebar', desc: 'Access your Courses, Weekly Timetable, Homework Status, Online Payments, and the Full Calendar Planner.' },
        { el: '.srow', title: 'Academic Progress Stats', desc: 'Track your enrolled course count, pending assignments, completed homework, and cumulative academic score.' },
        { el: '.twid', title: 'Interactive To-Do List', desc: 'Check off assignments as you complete them with instant live updates!' },
        { el: '.cwid', title: 'Calendar & Fullscreen Planner', desc: 'View daily class counts, Malaysian public holidays, and open the full-screen monthly planner.' },
        { el: '.nbell', title: 'Notifications & Course Reminders', desc: 'Check new homework alerts, grade releases, and teacher course reminders tagged by subject!' },
        { el: '#mwChatBtn', title: 'Gemini AI Study Planner', desc: 'Ask your personal AI Study Assistant to plan your study schedule and answer any questions.' }
      ];
    }
  }

  // Filter only visible elements
  mwTourSteps = mwTourSteps.filter(function(s) {
    return document.querySelector(s.el) !== null;
  });

  if (!mwTourSteps.length) return;
  mwCurrentTourIdx = 0;
  document.getElementById('mwTourBackdrop').classList.add('active');
  document.getElementById('mwTourCard').classList.add('active');
  mwShowTourStep(0);
}

function mwShowTourStep(idx) {
  var prevEl = document.querySelector('.mw-tour-highlight');
  if (prevEl) prevEl.classList.remove('mw-tour-highlight');

  var step = mwTourSteps[idx];
  var target = document.querySelector(step.el);
  if (!target) return;

  target.classList.add('mw-tour-highlight');
  target.scrollIntoView({ behavior: 'smooth', block: 'center' });

  document.getElementById('mwTourStepBadge').textContent = 'Step ' + (idx + 1) + ' of ' + mwTourSteps.length;
  document.getElementById('mwTourTitle').innerHTML = '<i class="fas fa-compass" style="color:var(--O)"></i> ' + step.title;
  document.getElementById('mwTourDesc').textContent = step.desc;

  // Dots
  var dotsContainer = document.getElementById('mwTourDots');
  dotsContainer.innerHTML = '';
  for (var i = 0; i < mwTourSteps.length; i++) {
    var d = document.createElement('div');
    d.className = 'tour-dot' + (i === idx ? ' active' : '');
    dotsContainer.appendChild(d);
  }

  document.getElementById('mwTourPrevBtn').style.display = idx > 0 ? 'inline-block' : 'none';
  var nextBtn = document.getElementById('mwTourNextBtn');
  if (idx === mwTourSteps.length - 1) {
    nextBtn.innerHTML = 'Finish <i class="fas fa-check"></i>';
  } else {
    nextBtn.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
  }

  // Position tour card near target
  var rect = target.getBoundingClientRect();
  var card = document.getElementById('mwTourCard');
  var cardWidth = card.offsetWidth || 340;
  var cardHeight = card.offsetHeight || 180;

  var top = rect.bottom + 14;
  var left = rect.left;

  if (top + cardHeight > window.innerHeight) {
    top = Math.max(16, rect.top - cardHeight - 14);
  }
  if (left + cardWidth > window.innerWidth) {
    left = Math.max(16, window.innerWidth - cardWidth - 20);
  }
  if (left < 16) left = 16;

  card.style.top = top + 'px';
  card.style.left = left + 'px';
}

function mwNextTourStep() {
  if (mwCurrentTourIdx < mwTourSteps.length - 1) {
    mwCurrentTourIdx++;
    mwShowTourStep(mwCurrentTourIdx);
  } else {
    mwEndTour();
  }
}

function mwPrevTourStep() {
  if (mwCurrentTourIdx > 0) {
    mwCurrentTourIdx--;
    mwShowTourStep(mwCurrentTourIdx);
  }
}

function mwEndTour() {
  var prevEl = document.querySelector('.mw-tour-highlight');
  if (prevEl) prevEl.classList.remove('mw-tour-highlight');
  document.getElementById('mwTourBackdrop').classList.remove('active');
  document.getElementById('mwTourCard').classList.remove('active');
  localStorage.setItem('mw_tour_completed', '1');
}

// Auto prompt tour once for first time users
window.addEventListener('load', function() {
  if (!localStorage.getItem('mw_tour_completed') && (document.getElementById('student') || document.getElementById('teacher'))) {
    setTimeout(function() {
      mwStartTour();
    }, 1000);
  }
});
</script>
</body></html>
HTML;
}

function splitLeft($leafEmoji = '🍁', $heading = 'Maple<em>wood</em>', $sub = 'Student &amp; Teacher Portal', $feats = []) {
    $defaultFeats = [
        ['fas fa-graduation-cap','<strong>Courses &amp; Timetables</strong> — manage your schedule'],
        ['fas fa-tasks',         '<strong>Homework Tracking</strong> — submissions &amp; due dates'],
        ['fas fa-credit-card',   '<strong>Payment Status</strong> — invoices &amp; balances'],
        ['fas fa-chalkboard-teacher','<strong>Teacher Admin</strong> — grades &amp; oversight'],
    ];
    $feats = $feats ?: $defaultFeats;
    echo '<div class="lp">';
    echo "<div class=\"lp-leaf\">{$leafEmoji}</div>";
    echo "<h1>{$heading}</h1>";
    echo "<p class=\"lp-sub\">{$sub}</p>";
    echo '<div class="lp-feats">';
    foreach ($feats as $f)
        echo "<div class=\"lp-feat\"><i class=\"{$f[0]}\"></i><span>{$f[1]}</span></div>";
    echo '</div></div>';
}
?>