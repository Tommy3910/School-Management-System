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

function pageFoot() {
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
