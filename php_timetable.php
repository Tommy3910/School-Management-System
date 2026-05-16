<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PHP School Timetable</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f5f6fa;
    color: #1a1a2e;
    min-height: 100vh;
    padding: 2rem 1rem;
  }

  .page {
    max-width: 1100px;
    margin: 0 auto;
  }

  header {
    margin-bottom: 2rem;
  }

  header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a2e;
    letter-spacing: -0.5px;
  }

  header p {
    font-size: 14px;
    color: #6b7280;
    margin-top: 4px;
  }

  .section {
    margin-bottom: 2.5rem;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    overflow: hidden;
  }

  .sec-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
  }

  .sec-header h2 {
    font-size: 17px;
    font-weight: 600;
    color: #1a1a2e;
  }

  .badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
  }

  .badge-green  { background: #d1fae5; color: #065f46; }
  .badge-blue   { background: #dbeafe; color: #1e40af; }
  .badge-amber  { background: #fef3c7; color: #92400e; }

  .tbl-wrap { overflow-x: auto; }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 680px;
  }

  thead th {
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: 11px;
    padding: 10px 6px;
    text-align: center;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
  }

  thead th.day-col {
    text-align: left;
    padding-left: 16px;
    min-width: 72px;
  }

  tbody td {
    padding: 5px 4px;
    text-align: center;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    height: 48px;
  }

  tbody tr:last-child td { border-bottom: none; }

  tbody td.day-label {
    text-align: left;
    padding-left: 16px;
    font-weight: 600;
    font-size: 11px;
    color: #6b7280;
    background: #f9fafb;
    border-right: 1px solid #e5e7eb;
    white-space: nowrap;
  }

  .cell {
    border-radius: 8px;
    padding: 5px 4px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.3;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    height: 36px;
  }

  .cls   { background: #d1fae5; color: #065f46; }
  .sport { background: #fef3c7; color: #92400e; }
  .brk   { background: #f3f4f6; color: #6b7280; font-weight: 500; font-size: 10px; }
  .cls-b { background: #dbeafe; color: #1e40af; }
  .short { background: #ede9fe; color: #5b21b6; font-size: 10px; }

  .note {
    font-size: 11px;
    color: #9ca3af;
    padding: 10px 20px 14px;
    border-top: 1px solid #f3f4f6;
  }

  .legend {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px 20px;
    border-top: 1px solid #f3f4f6;
    background: #fafafa;
  }

  .leg-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #6b7280;
  }

  .leg-dot {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    flex-shrink: 0;
  }

  @media print {
    body { background: white; padding: 0.5rem; }
    .section { box-shadow: none; border: 1px solid #ddd; }
    .page { max-width: 100%; }
  }
</style>
</head>
<body>
<div class="page">

  <header>
    <h1>PHP School Timetable</h1>
    <p>Weekly schedule — all year groups</p>
  </header>

  
  <div class="section">
    <div class="sec-header">
      <h2>Year 1 – 6</h2>
      <span class="badge badge-green">8:00 am – 12:45 pm</span>
      <span class="badge badge-green">1 hr classes</span>
    </div>
    <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th class="day-col">Day</th>
          <th>8:00 – 9:00</th>
          <th>9:00 – 10:00</th>
          <th>10:00 – 10:45</th>
          <th>10:45 – 11:45</th>
          <th>11:45 – 12:45</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="day-label">Monday</td>
          <td colspan="2" style="padding:4px"><div class="cell sport">⚽ Sport Session (8:00 – 10:00, 2 hrs)</div></td>
          <td><div class="cell brk">Break<br>(45 min)</div></td>
          <td><div class="cell cls">Period 1</div></td>
          <td><div class="cell cls">Period 2</div></td>
        </tr>
        <tr>
          <td class="day-label">Tuesday</td>
          <td><div class="cell cls">Period 1</div></td>
          <td><div class="cell cls">Period 2</div></td>
          <td><div class="cell brk">Break<br>(45 min)</div></td>
          <td><div class="cell cls">Period 3</div></td>
          <td><div class="cell cls">Period 4</div></td>
        </tr>
        <tr>
          <td class="day-label">Wednesday</td>
          <td><div class="cell cls">Period 1</div></td>
          <td><div class="cell cls">Period 2</div></td>
          <td><div class="cell brk">Break<br>(45 min)</div></td>
          <td><div class="cell cls">Period 3</div></td>
          <td><div class="cell cls">Period 4</div></td>
        </tr>
        <tr>
          <td class="day-label">Thursday</td>
          <td><div class="cell cls">Period 1</div></td>
          <td><div class="cell cls">Period 2</div></td>
          <td><div class="cell brk">Break<br>(45 min)</div></td>
          <td><div class="cell cls">Period 3</div></td>
          <td><div class="cell cls">Period 4</div></td>
        </tr>
        <tr>
          <td class="day-label">Friday</td>
          <td><div class="cell cls">Period 1</div></td>
          <td><div class="cell cls">Period 2</div></td>
          <td><div class="cell brk">Break<br>(45 min)</div></td>
          <td><div class="cell cls">Period 3</div></td>
          <td><div class="cell cls">Period 4</div></td>
        </tr>
      </tbody>
    </table>
    </div>
    <div class="legend">
      <div class="leg-item"><div class="leg-dot" style="background:#d1fae5"></div>Class (1 hr)</div>
      <div class="leg-item"><div class="leg-dot" style="background:#fef3c7"></div>Sport session (2 hrs) — Monday</div>
      <div class="leg-item"><div class="leg-dot" style="background:#f3f4f6"></div>Break (45 min)</div>
    </div>
  </div>

  
  <div class="section">
    <div class="sec-header">
      <h2>Year 7 – 9</h2>
      <span class="badge badge-blue">8:00 am – 1:45 pm</span>
      <span class="badge badge-blue">45 min classes</span>
    </div>
    <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th class="day-col">Day</th>
          <th>8:00–8:45</th>
          <th>8:45–9:30</th>
          <th>9:30–10:00</th>
          <th>10:00–11:00</th>
          <th>11:00–11:45</th>
          <th>11:45–12:30</th>
          <th>12:30–1:15</th>
          <th>1:15–1:45</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="day-label">Monday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Tuesday</td>
          <td colspan="2" style="padding:4px"><div class="cell sport">⚽ Sport (8:00–10:00, 2 hrs)</div></td>
          <td><div class="cell brk" style="font-size:9px">Break starts<br>10:00–11:00</div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P3</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
        </tr>
        <tr>
          <td class="day-label">Wednesday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Thursday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Friday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
      </tbody>
    </table>
    </div>
    <div class="legend">
      <div class="leg-item"><div class="leg-dot" style="background:#dbeafe"></div>Class (45 min)</div>
      <div class="leg-item"><div class="leg-dot" style="background:#ede9fe"></div>Short period (30 min) — fits before/after break</div>
      <div class="leg-item"><div class="leg-dot" style="background:#fef3c7"></div>Sport session (2 hrs) — Tuesday</div>
      <div class="leg-item"><div class="leg-dot" style="background:#f3f4f6"></div>Break (1 hr, 10:00–11:00)</div>
    </div>
    <p class="note">On Tuesday, sport (8:00–10:00) flows directly into the break (10:00–11:00), giving 3 hrs of sport+rest before classes resume at 11:00.</p>
  </div>

 
  <div class="section">
    <div class="sec-header">
      <h2>Year 10 – 11</h2>
      <span class="badge badge-amber">8:00 am – 1:45 pm</span>
      <span class="badge badge-amber">45 min classes</span>
    </div>
    <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th class="day-col">Day</th>
          <th>8:00–8:45</th>
          <th>8:45–9:30</th>
          <th>9:30–10:00</th>
          <th>10:00–11:00</th>
          <th>11:00–11:45</th>
          <th>11:45–12:30</th>
          <th>12:30–1:15</th>
          <th>1:15–1:45</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="day-label">Monday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Tuesday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Wednesday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Thursday</td>
          <td><div class="cell cls-b">P1</div></td>
          <td><div class="cell cls-b">P2</div></td>
          <td><div class="cell short">P3<br><span style="font-weight:400">(30 min)</span></div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
          <td><div class="cell short">P7<br><span style="font-weight:400">(30 min)</span></div></td>
        </tr>
        <tr>
          <td class="day-label">Friday</td>
          <td colspan="2" style="padding:4px"><div class="cell sport">⚽ Sport (8:00–10:00, 2 hrs)</div></td>
          <td><div class="cell brk" style="font-size:9px">Break starts<br>10:00–11:00</div></td>
          <td><div class="cell brk">Break<br>(1 hr)</div></td>
          <td><div class="cell cls-b">P3</div></td>
          <td><div class="cell cls-b">P4</div></td>
          <td><div class="cell cls-b">P5</div></td>
          <td><div class="cell cls-b">P6</div></td>
        </tr>
      </tbody>
    </table>
    </div>
    <div class="legend">
      <div class="leg-item"><div class="leg-dot" style="background:#dbeafe"></div>Class (45 min)</div>
      <div class="leg-item"><div class="leg-dot" style="background:#ede9fe"></div>Short period (30 min) — fits before/after break</div>
      <div class="leg-item"><div class="leg-dot" style="background:#fef3c7"></div>Sport session (2 hrs) — Friday</div>
      <div class="leg-item"><div class="leg-dot" style="background:#f3f4f6"></div>Break (1 hr, 10:00–11:00)</div>
    </div>
    <p class="note">On Friday, sport (8:00–10:00) flows directly into the break (10:00–11:00), giving 3 hrs of sport+rest before classes resume at 11:00.</p>
  </div>

</div>
</body>
</html>
