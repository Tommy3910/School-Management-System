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
    
    echo <<<HTML
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