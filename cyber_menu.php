<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cyberpunk HUD Menu Selection System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Teko:wght@400;600;700&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg-dark: #0a0205;
    --hud-red: #ff1d4b;
    --hud-red-bright: #ff3864;
    --hud-red-dim: rgba(255, 29, 75, 0.22);
    --hud-red-glow: rgba(255, 29, 75, 0.55);
    --hud-red-border: rgba(255, 29, 75, 0.4);
    --hud-cyan: #00ffd0;
    --hud-cyan-dim: rgba(0, 255, 208, 0.2);
    --hud-text: #ffd5dc;
    --hud-text-dim: #9e4659;
    --hud-gold: #ffbe3b;
    --font-mono: 'Share Tech Mono', monospace;
    --font-header: 'Teko', sans-serif;
    --font-body: 'Rajdhani', sans-serif;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    user-select: none;
  }

  body {
    background-color: var(--bg-dark);
    color: var(--hud-text);
    font-family: var(--font-body);
    min-height: 100vh;
    overflow-x: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
  }

  /* Scanlines & Grid Backdrop */
  .hud-backdrop {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    background: 
      radial-gradient(circle at 75% 45%, rgba(255, 20, 60, 0.12) 0%, transparent 60%),
      radial-gradient(circle at 20% 50%, rgba(255, 20, 60, 0.08) 0%, transparent 50%),
      linear-gradient(to bottom, #0d0206 0%, #080104 100%);
  }

  .hud-grid {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 2;
    background-size: 40px 40px;
    background-image: 
      linear-gradient(to right, rgba(255, 29, 75, 0.04) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(255, 29, 75, 0.04) 1px, transparent 1px);
    perspective: 600px;
  }

  .hud-scanlines {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 3;
    background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.35) 50%);
    background-size: 100% 4px;
    opacity: 0.6;
  }

  .hud-vignette {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 4;
    box-shadow: inset 0 0 100px rgba(0,0,0,0.85);
  }

  /* Main HUD Container */
  .hud-screen {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    padding: 18px 32px 30px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  /* Top Navigation & Status Bar */
  .hud-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(255, 29, 75, 0.25);
    position: relative;
  }

  .hud-topbar::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    width: 60%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--hud-red), transparent);
  }

  .hud-status-left {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .potion-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-mono);
    font-size: 0.95rem;
    color: var(--hud-text);
  }

  .potion-box {
    width: 32px;
    height: 32px;
    border: 1px solid var(--hud-red);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: rgba(255, 29, 75, 0.08);
  }

  .potion-box svg {
    width: 18px;
    height: 18px;
    fill: var(--hud-red);
  }

  .health-gauge {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .health-notch {
    width: 6px;
    height: 14px;
    background: var(--hud-red);
    clip-path: polygon(30% 0, 100% 0, 70% 100%, 0 100%);
    box-shadow: 0 0 6px var(--hud-red);
  }

  .health-bar-line {
    width: 110px;
    height: 4px;
    background: rgba(255, 29, 75, 0.25);
    position: relative;
  }

  .health-bar-fill {
    width: 78%;
    height: 100%;
    background: var(--hud-red);
    box-shadow: 0 0 8px var(--hud-red);
  }

  /* Center Tab Navigation: [L1] [ Equipment ] Inventory Missions System [R1] */
  .hud-tabs {
    display: flex;
    align-items: center;
    gap: 22px;
  }

  .tab-trigger-bumper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 7px;
    font-family: var(--font-mono);
    font-size: 0.72rem;
    border: 1px solid var(--hud-text-dim);
    color: var(--hud-text-dim);
    border-radius: 4px;
    letter-spacing: 0.05em;
  }

  .hud-tab-item {
    font-family: var(--font-header);
    font-size: 1.45rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hud-text-dim);
    cursor: pointer;
    padding: 4px 14px;
    position: relative;
    transition: all 0.25s ease;
    background: transparent;
    border: none;
  }

  .hud-tab-item:hover {
    color: var(--hud-text);
  }

  .hud-tab-item.active {
    color: var(--hud-red-bright);
    text-shadow: 0 0 10px var(--hud-red-glow);
  }

  /* Active Tab Bracket Frame */
  .hud-tab-item.active::before,
  .hud-tab-item.active::after {
    content: '';
    position: absolute;
    width: 8px;
    height: 100%;
    top: 0;
    border: 1px solid var(--hud-red);
    pointer-events: none;
  }

  .hud-tab-item.active::before {
    left: 0;
    border-right: none;
  }

  .hud-tab-item.active::after {
    right: 0;
    border-left: none;
  }

  .hud-tab-notch {
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 6px;
    background: var(--hud-red);
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    display: none;
  }

  .hud-tab-item.active .hud-tab-notch {
    display: block;
  }

  /* Top Right Status (Karma, District) */
  .hud-status-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
  }

  .karma-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--hud-red);
    color: #000;
    font-family: var(--font-header);
    font-size: 1.1rem;
    font-weight: 700;
    padding: 1px 9px 0;
    letter-spacing: 0.08em;
    box-shadow: 0 0 10px var(--hud-red-glow);
  }

  .karma-tag .plus-glyph {
    font-size: 0.9rem;
  }

  .district-title {
    font-family: var(--font-mono);
    font-size: 0.8rem;
    color: var(--hud-text);
    letter-spacing: 0.1em;
    margin-top: 4px;
    text-shadow: 0 0 6px var(--hud-red-glow);
  }

  /* ──────── MAIN INTERACTIVE STAGE ──────── */
  .hud-stage {
    display: grid;
    grid-template-columns: 340px 1fr 480px;
    gap: 30px;
    align-items: center;
    margin: 30px 0;
    min-height: 520px;
    position: relative;
  }

  /* LEFT: Equipment Category Menu Column */
  .menu-column {
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 20;
  }

  /* Section Header: [Kanji] Equipment [Code] */
  .menu-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    background: rgba(255, 29, 75, 0.12);
    border: 1px solid var(--hud-red);
    box-shadow: 0 0 10px rgba(255, 29, 75, 0.2);
    margin-bottom: 24px;
  }

  .menu-header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-body);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--hud-red-bright);
    letter-spacing: 0.08em;
  }

  .kanji-emblem {
    width: 22px;
    height: 22px;
    border: 1px solid var(--hud-red);
    display: grid;
    place-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(255, 29, 75, 0.2);
    line-height: 1;
  }

  .tech-stamp {
    font-family: var(--font-mono);
    font-size: 0.75rem;
    color: var(--hud-red);
    letter-spacing: 0.15em;
  }

  /* Category Item Groups */
  .menu-category-group {
    margin-bottom: 24px;
    position: relative;
  }

  .category-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-mono);
    font-size: 0.85rem;
    color: var(--hud-text);
    margin-bottom: 8px;
    letter-spacing: 0.08em;
  }

  .category-bullet {
    display: inline-block;
    width: 7px;
    height: 7px;
    border: 1.5px solid var(--hud-red);
    border-radius: 50%;
  }

  /* ── THE ICON SLOT BOX (Key Selection Target) ── */
  .item-slot-row {
    display: flex;
    align-items: center;
    position: relative;
    padding-left: 28px; /* space for arrow cursor */
  }

  /* The Glowing Red Pointer Arrow: >> */
  .selection-pointer {
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--hud-red-bright);
    font-size: 1.5rem;
    font-weight: 900;
    line-height: 1;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.18, 0.89, 0.32, 1.28);
    pointer-events: none;
    filter: drop-shadow(0 0 8px var(--hud-red));
  }

  .item-slot-row.active .selection-pointer {
    opacity: 1;
    transform: translateY(-50%) translateX(2px);
    animation: pointerPulse 1.4s ease-in-out infinite alternate;
  }

  @keyframes pointerPulse {
    0% { transform: translateY(-50%) translateX(0px); filter: drop-shadow(0 0 6px var(--hud-red)); }
    100% { transform: translateY(-50%) translateX(4px); filter: drop-shadow(0 0 14px var(--hud-red-bright)); }
  }

  /* The Item Slot Frame */
  .item-slot-card {
    width: 62px;
    height: 62px;
    background: rgba(20, 5, 10, 0.7);
    border: 1.5px solid var(--hud-red-border);
    position: relative;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
  }

  /* Cyber corner accents */
  .item-slot-card::before,
  .item-slot-card::after {
    content: '';
    position: absolute;
    width: 6px;
    height: 6px;
    border-color: var(--hud-red);
    pointer-events: none;
    transition: all 0.2s ease;
  }

  .item-slot-card::before {
    top: -2px;
    left: -2px;
    border-top: 2px solid;
    border-left: 2px solid;
  }

  .item-slot-card::after {
    bottom: -2px;
    right: -2px;
    border-bottom: 2px solid;
    border-right: 2px solid;
  }

  .item-slot-card:hover {
    border-color: var(--hud-red);
    box-shadow: 0 0 12px rgba(255, 29, 75, 0.3);
    background: rgba(35, 8, 16, 0.8);
  }

  /* ACTIVE SELECTION STATE ON SLOT CARD */
  .item-slot-row.active .item-slot-card {
    border-color: var(--hud-red-bright);
    background: rgba(45, 10, 20, 0.85);
    box-shadow: 
      0 0 16px var(--hud-red-glow),
      inset 0 0 12px rgba(255, 29, 75, 0.35);
  }

  .item-slot-row.active .item-slot-card::before,
  .item-slot-row.active .item-slot-card::after {
    width: 9px;
    height: 9px;
    border-color: #fff;
    filter: drop-shadow(0 0 4px var(--hud-red));
  }

  .item-slot-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
  }

  .item-slot-icon svg {
    width: 100%;
    height: 100%;
    fill: var(--hud-red);
    filter: drop-shadow(0 0 4px rgba(255, 29, 75, 0.6));
    transition: all 0.2s ease;
  }

  .item-slot-row.active .item-slot-icon svg {
    fill: #ffebee;
    filter: drop-shadow(0 0 8px var(--hud-red-bright));
    transform: scale(1.06);
  }

  /* Small status gem on corner */
  .item-slot-gem {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 6px;
    height: 6px;
    background: var(--hud-cyan);
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    box-shadow: 0 0 6px var(--hud-cyan);
  }

  /* Appearance Button at Bottom */
  .appearance-btn {
    margin-top: 10px;
    width: 100%;
    padding: 10px 16px;
    background: rgba(255, 29, 75, 0.08);
    border: 1px solid var(--hud-red);
    color: var(--hud-red-bright);
    font-family: var(--font-header);
    font-size: 1.3rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    position: relative;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .appearance-btn::before,
  .appearance-btn::after {
    content: '';
    position: absolute;
    width: 6px;
    height: 6px;
    border-color: var(--hud-red);
  }

  .appearance-btn::before { top: 2px; left: 2px; border-top: 1.5px solid; border-left: 1.5px solid; }
  .appearance-btn::after { bottom: 2px; right: 2px; border-bottom: 1.5px solid; border-right: 1.5px solid; }

  .appearance-btn:hover {
    background: rgba(255, 29, 75, 0.22);
    box-shadow: 0 0 16px var(--hud-red-glow);
    color: #fff;
  }

  /* ──────── CENTER: THE DYNAMIC LASER CONNECTOR & DETAILS ──────── */
  .center-details-stage {
    position: relative;
    height: 100%;
    min-height: 480px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
  }

  /* Dynamic Horizontal Connecting Laser Line */
  .laser-track-container {
    position: absolute;
    top: 0;
    left: -30px;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 5;
  }

  /* Line rows for passive connection */
  .laser-wire {
    position: absolute;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 29, 75, 0.18);
    transition: top 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
  }

  /* Active Laser Line (Bright, Pulsing, with Terminal Node) */
  .active-laser-line {
    position: absolute;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, 
      var(--hud-red-bright) 0%, 
      var(--hud-red) 75%, 
      rgba(255, 29, 75, 0.3) 100%);
    box-shadow: 0 0 10px var(--hud-red-glow);
    transition: top 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    z-index: 8;
  }

  .active-laser-line::after {
    content: '';
    position: absolute;
    right: 0;
    top: -4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid var(--hud-red);
    background: #000;
    box-shadow: 0 0 8px var(--hud-red);
  }

  /* Detail Card anchored to the active connector line */
  .active-details-panel {
    position: absolute;
    left: 40px;
    transition: top 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    z-index: 15;
    pointer-events: auto;
  }

  /* Bracket Header: 【 Takeminakata 】 Equipped [icon] */
  .item-title-bracket-row {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    top: -15px; /* Centers with laser line */
    background: var(--bg-dark);
    padding: 2px 14px;
    border-radius: 3px;
    border: 1px solid rgba(255, 29, 75, 0.3);
    width: max-content;
    box-shadow: 0 0 14px rgba(0,0,0,0.8);
  }

  .bracket-glyph {
    color: var(--hud-red-bright);
    font-size: 1.3rem;
    font-weight: 700;
  }

  .item-primary-name {
    font-family: var(--font-header);
    font-size: 1.85rem;
    letter-spacing: 0.1em;
    color: var(--hud-text);
    font-weight: 600;
    line-height: 1;
    text-shadow: 0 0 10px var(--hud-red-glow);
  }

  .status-badge-equipped {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-body);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--hud-red-bright);
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .status-badge-equipped::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    border: 1.5px solid var(--hud-red);
    background: rgba(255, 29, 75, 0.3);
  }

  .tech-bracket-code {
    font-family: var(--font-mono);
    font-size: 0.78rem;
    color: var(--hud-red);
    letter-spacing: 0.1em;
    margin-left: 6px;
  }

  /* Subtitle Block Beneath Line */
  .item-sub-details {
    padding-left: 20px;
    margin-top: 10px;
  }

  .slot-count-badge {
    font-family: var(--font-mono);
    font-size: 1.1rem;
    color: var(--hud-red);
    letter-spacing: 0.1em;
    margin-bottom: 2px;
  }

  .item-category-subtitle {
    font-family: var(--font-body);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--hud-text);
    letter-spacing: 0.05em;
    margin-bottom: 6px;
  }

  .item-unlock-alert {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-body);
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--hud-cyan);
    letter-spacing: 0.04em;
    text-shadow: 0 0 8px var(--hud-cyan);
    animation: cyanGlow 2s ease-in-out infinite alternate;
  }

  @keyframes cyanGlow {
    0% { opacity: 0.85; text-shadow: 0 0 5px var(--hud-cyan); }
    100% { opacity: 1; text-shadow: 0 0 12px var(--hud-cyan); }
  }

  /* Extra Stat Grid Preview */
  .item-stats-box {
    margin-top: 18px;
    padding: 12px 16px;
    border: 1px solid rgba(255, 29, 75, 0.2);
    background: rgba(255, 29, 75, 0.03);
    width: 320px;
  }

  .stat-row {
    display: flex;
    justify-content: space-between;
    font-family: var(--font-mono);
    font-size: 0.82rem;
    margin-bottom: 6px;
    color: var(--hud-text-dim);
  }

  .stat-row strong {
    color: var(--hud-text);
  }

  .stat-bar {
    width: 100%;
    height: 3px;
    background: rgba(255, 29, 75, 0.2);
    margin-top: 2px;
    position: relative;
  }

  .stat-bar-fill {
    height: 100%;
    background: var(--hud-red);
    box-shadow: 0 0 6px var(--hud-red);
    transition: width 0.3s ease;
  }

  /* ──────── RIGHT: HOLOGRAPHIC ANATOMICAL RADAR ──────── */
  .hologram-stage {
    position: relative;
    width: 100%;
    height: 520px;
    display: flex;
    align-items: center;
    justify-content: center;
    perspective: 800px;
  }

  .radar-ring {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(255, 29, 75, 0.18);
    pointer-events: none;
    box-shadow: 0 0 12px rgba(255, 29, 75, 0.05);
  }

  .radar-ring.ring-1 {
    width: 440px;
    height: 440px;
    border-style: dashed;
    animation: rotateSlow 60s linear infinite;
  }

  .radar-ring.ring-2 {
    width: 360px;
    height: 360px;
    border-width: 1.5px;
    border-color: rgba(255, 29, 75, 0.3);
  }

  .radar-ring.ring-3 {
    width: 280px;
    height: 280px;
    border-style: dotted;
    animation: rotateReverse 40s linear infinite;
  }

  @keyframes rotateSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  @keyframes rotateReverse { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }

  /* Hologram Canvas / SVG Silhouette */
  .hologram-figure {
    position: relative;
    z-index: 10;
    width: 300px;
    height: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    filter: drop-shadow(0 0 15px rgba(255, 29, 75, 0.45));
  }

  .hologram-figure svg {
    width: 100%;
    height: 100%;
  }

  /* Scanline sweep across hologram */
  .hologram-sweep {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(255, 29, 75, 0.25) 50%, transparent 100%);
    height: 40px;
    animation: scanSweep 3.5s ease-in-out infinite;
    pointer-events: none;
  }

  @keyframes scanSweep {
    0% { top: -10%; opacity: 0; }
    30% { opacity: 0.8; }
    70% { opacity: 0.8; }
    100% { top: 110%; opacity: 0; }
  }

  /* ──────── BOTTOM BAR: CONTROLLER ACTIONS & CURRENCY ──────── */
  .hud-bottombar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 29, 75, 0.25);
    margin-top: 20px;
    position: relative;
  }

  .hud-controller-prompts {
    display: flex;
    align-items: center;
    gap: 28px;
    margin: 0 auto;
  }

  .prompt-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-header);
    font-size: 1.25rem;
    letter-spacing: 0.1em;
    color: var(--hud-text);
    text-transform: uppercase;
  }

  .btn-glyph {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 1.5px solid var(--hud-red);
    display: grid;
    place-content: center;
    font-size: 0.85rem;
    font-weight: 900;
    color: var(--hud-red-bright);
    background: rgba(255, 29, 75, 0.15);
    line-height: 1;
    box-shadow: 0 0 6px var(--hud-red-glow);
  }

  .currency-panel {
    display: flex;
    align-items: center;
    gap: 16px;
    font-family: var(--font-header);
    font-size: 1.45rem;
    color: var(--hud-gold);
    letter-spacing: 0.05em;
  }

  .currency-badge {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .currency-glyph {
    width: 20px;
    height: 20px;
    border: 1px solid var(--hud-gold);
    display: grid;
    place-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #000;
    background: var(--hud-gold);
  }

  /* ── TOP UTILITY TOOLBAR (Sound, Mode Switcher) ── */
  .hud-utility-bar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    margin-bottom: 10px;
    font-family: var(--font-mono);
    font-size: 0.78rem;
  }

  .util-btn {
    background: rgba(255, 29, 75, 0.1);
    border: 1px solid var(--hud-red-border);
    color: var(--hud-text);
    padding: 4px 10px;
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
    letter-spacing: 0.08em;
    transition: all 0.2s ease;
  }

  .util-btn:hover {
    border-color: var(--hud-red);
    background: rgba(255, 29, 75, 0.25);
    color: #fff;
  }

  .util-btn.active {
    background: var(--hud-red);
    color: #000;
    font-weight: 700;
  }

  /* Responsive tweak */
  @media (max-width: 1200px) {
    .hud-stage {
      grid-template-columns: 280px 1fr 340px;
      gap: 20px;
    }
  }

  @media (max-width: 900px) {
    .hud-stage {
      grid-template-columns: 1fr;
    }
    .hologram-stage {
      display: none;
    }
    .center-details-stage {
      min-height: 280px;
    }
  }
</style>
</head>
<body>

<div class="hud-backdrop"></div>
<div class="hud-grid"></div>
<div class="hud-scanlines"></div>
<div class="hud-vignette"></div>

<main class="hud-screen">
  
  <!-- Utility toolbar: Sound Toggle & Mode Switch -->
  <aside class="hud-utility-bar">
    <span>SYSTEM CONTROLS:</span>
    <button class="util-btn active" id="btnSoundToggle" onclick="toggleAudio()" title="Toggle synthesizer SFX">🔊 SFX ON</button>
    <button class="util-btn" id="btnModeGhostwire" onclick="switchTheme('ghostwire')" title="Ghostwire 1:1 Game Menu">GHOSTWIRE HUD</button>
    <button class="util-btn" id="btnModeMaplewood" onclick="switchTheme('maplewood')" title="Preview on Maplewood School System">MAPLEWOOD ACADEMIC HUD</button>
  </aside>

  <!-- ── TOP NAVIGATION BAR ── -->
  <header class="hud-topbar">
    <div class="hud-status-left">
      <div class="potion-indicator">
        <div class="potion-box">
          <svg viewBox="0 0 24 24"><path d="M9 2h6v2h-2v2h3a2 2 0 0 1 2 2v10a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4V8a2 2 0 0 1 2-2h3V4H9V2zm1 6v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V8h-8z"/></svg>
        </div>
        <span>x0</span>
      </div>
      <div class="health-gauge">
        <div class="health-notch"></div>
        <div class="health-bar-line">
          <div class="health-bar-fill"></div>
        </div>
        <div class="health-notch" style="opacity:0.5"></div>
      </div>
    </div>

    <!-- Center Tab Switchers -->
    <nav class="hud-tabs" id="hudTabsNav">
      <span class="tab-trigger-bumper">L1</span>
      <button class="hud-tab-item active" data-tab="equipment" onclick="switchTopTab('equipment', this)">
        Equipment
        <span class="hud-tab-notch"></span>
      </button>
      <button class="hud-tab-item" data-tab="inventory" onclick="switchTopTab('inventory', this)">
        Inventory
        <span class="hud-tab-notch"></span>
      </button>
      <button class="hud-tab-item" data-tab="missions" onclick="switchTopTab('missions', this)">
        Missions
        <span class="hud-tab-notch"></span>
      </button>
      <button class="hud-tab-item" data-tab="system" onclick="switchTopTab('system', this)">
        System
        <span class="hud-tab-notch"></span>
      </button>
      <span class="tab-trigger-bumper">R1</span>
    </nav>

    <!-- Top Right Status -->
    <div class="hud-status-right">
      <div class="karma-tag" id="karmaDisplay">
        <span class="plus-glyph">✚</span>
        <span>KARMA <span id="karmaNum">0</span></span>
      </div>
      <div class="district-title" id="districtLabel">Haunted District</div>
    </div>
  </header>

  <!-- ── MAIN STAGE ── -->
  <section class="hud-stage">
    
    <!-- LEFT: Equipment Selection Menu Column -->
    <section class="menu-column" id="menuColumn">
      <header class="menu-header-bar">
        <div class="menu-header-title">
          <div class="kanji-emblem" id="hdrEmblem">器</div>
          <span id="hdrMenuTitle">Equipment</span>
        </div>
        <span class="tech-stamp" id="hdrStamp">ΞHUN¬</span>
      </header>

      <!-- Slot 1: Weapons -->
      <div class="menu-category-group" id="catGroup1">
        <div class="category-label">
          <span class="category-bullet"></span>
          <span id="catLabel1">Weapons</span>
        </div>
        <div class="item-slot-row active" id="slotRow-1" onclick="selectSlot(1)" data-index="1">
          <div class="selection-pointer">❯❯</div>
          <div class="item-slot-card">
            <span class="item-slot-gem"></span>
            <div class="item-slot-icon" id="slotIcon1">
              <!-- Crossed Katanas / Blades Icon -->
              <svg viewBox="0 0 24 24"><path d="M19.7 4.3a1 1 0 0 0-1.4 0L14.4 8.2l-2.1-2.1-1.4 1.4 2.1 2.1-4.9 4.9H6v2.1l-3.3 3.3 1.4 1.4 3.3-3.3H9.5l4.9-4.9 2.1 2.1 1.4-1.4-2.1-2.1 3.9-3.9a1 1 0 0 0 0-1.4zM4.3 4.3a1 1 0 0 0 0 1.4l3.9 3.9-2.1 2.1 1.4 1.4 2.1-2.1 4.9 4.9v2.1l3.3 3.3 1.4-1.4-3.3-3.3v-2.1l-4.9-4.9 2.1-2.1-1.4-1.4-2.1 2.1L5.7 4.3a1 1 0 0 0-1.4 0z"/></svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Slot 2: Accessories -->
      <div class="menu-category-group" id="catGroup2">
        <div class="category-label">
          <span class="category-bullet"></span>
          <span id="catLabel2">Accessories</span>
        </div>
        <div class="item-slot-row" id="slotRow-2" onclick="selectSlot(2)" data-index="2">
          <div class="selection-pointer">❯❯</div>
          <div class="item-slot-card">
            <div class="item-slot-icon" id="slotIcon2">
              <!-- Magatama / Beads Bracelet Icon -->
              <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0-13a5 5 0 0 0-5 5 3 3 0 0 0 3 3 2 2 0 0 0 2-2 1 1 0 0 1 1-1 1 1 0 0 0 1-1 3 3 0 0 0-2-4z"/></svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Slot 3: Combat Skills -->
      <div class="menu-category-group" id="catGroup3">
        <div class="category-label">
          <span class="category-bullet"></span>
          <span id="catLabel3">Combat Skills</span>
        </div>
        <div class="item-slot-row" id="slotRow-3" onclick="selectSlot(3)" data-index="3">
          <div class="selection-pointer">❯❯</div>
          <div class="item-slot-card">
            <div class="item-slot-icon" id="slotIcon3">
              <!-- Seal Hand / Mudra Icon -->
              <svg viewBox="0 0 24 24"><path d="M18 7V4a2 2 0 0 0-2-2h-1a2 2 0 0 0-2 2v3h-1V3a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v7H6a2 2 0 0 0-2 2v4a8 8 0 0 0 8 8h2a8 8 0 0 0 8-8V9a2 2 0 0 0-2-2zm0 9a6 6 0 0 1-6 6h-2a6 6 0 0 1-6-6v-4h2v3h2V4h1v8h2V3h1v9h2V5h1v4h1z"/></svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Slot 4 / Button: Appearance -->
      <button class="appearance-btn" onclick="selectSlot(4)">
        Appearance
      </button>
    </section>

    <!-- ── CENTER: DYNAMIC LASER CONNECTOR & DETAILS ── -->
    <section class="center-details-stage" id="centerStage">
      <!-- Passive guide lines -->
      <div class="laser-track-container" id="laserTrackContainer">
        <div class="laser-wire" id="wire1"></div>
        <div class="laser-wire" id="wire2"></div>
        <div class="laser-wire" id="wire3"></div>
      </div>

      <!-- Active Laser Line Beam -->
      <div class="active-laser-line" id="activeLaserBeam"></div>

      <!-- Active Details Panel attached to the laser line -->
      <article class="active-details-panel" id="activeDetailsPanel">
        <div class="item-title-bracket-row">
          <span class="bracket-glyph">【</span>
          <h2 class="item-primary-name" id="detTitle">Takeminakata</h2>
          <span class="bracket-glyph">】</span>
          <div class="status-badge-equipped" id="detStatusBadge">Equipped</div>
          <span class="tech-bracket-code" id="detCode">Ξ31</span>
        </div>

        <div class="item-sub-details">
          <div class="slot-count-badge" id="detCount">2/9</div>
          <div class="item-category-subtitle" id="detSubtitle">Twin Blades / Tachi</div>
          <div class="item-unlock-alert" id="detAlert">
            <span>✦</span>
            <span id="detAlertText">New Weapon Skill Can Be Unlocked</span>
          </div>

          <!-- Extended Stat Bars -->
          <div class="item-stats-box" id="detStatsBox">
            <div class="stat-row">
              <span id="statLbl1">ATTACK POWER</span>
              <strong id="statVal1">428</strong>
            </div>
            <div class="stat-bar"><div class="stat-bar-fill" id="statFill1" style="width: 82%"></div></div>

            <div class="stat-row" style="margin-top:10px">
              <span id="statLbl2">ETHER SYNERGY</span>
              <strong id="statVal2">+35%</strong>
            </div>
            <div class="stat-bar"><div class="stat-bar-fill" id="statFill2" style="width: 65%"></div></div>

            <div class="stat-row" style="margin-top:10px">
              <span id="statLbl3">SPEED / DEFLECTION</span>
              <strong id="statVal3">S-TIER</strong>
            </div>
            <div class="stat-bar"><div class="stat-bar-fill" id="statFill3" style="width: 94%"></div></div>
          </div>
        </div>
      </article>
    </section>

    <!-- ── RIGHT: HOLOGRAPHIC ANATOMICAL RADAR ── -->
    <aside class="hologram-stage" aria-hidden="true">
      <div class="radar-ring ring-1"></div>
      <div class="radar-ring ring-2"></div>
      <div class="radar-ring ring-3"></div>

      <div class="hologram-figure">
        <div class="hologram-sweep"></div>
        <!-- Stylized Anatomical Wireframe Body with glowing energy lines -->
        <svg viewBox="0 0 200 400" fill="none" stroke="var(--hud-red)" stroke-width="1.2">
          <!-- Head / Aura -->
          <circle cx="100" cy="45" r="22" stroke="var(--hud-red)" stroke-dasharray="2,2"/>
          <ellipse cx="100" cy="45" rx="15" ry="19" stroke="var(--hud-red)"/>
          <circle cx="100" cy="45" r="4" fill="var(--hud-red)" id="holoHeadNode"/>

          <!-- Spine / Core Line -->
          <line x1="100" y1="65" x2="100" y2="210" stroke="var(--hud-red)" stroke-width="2"/>
          <circle cx="100" cy="110" r="5" stroke="var(--hud-cyan)" fill="rgba(0,255,208,0.3)" id="holoHeartNode"/>
          <circle cx="100" cy="155" r="4" stroke="var(--hud-red)" fill="rgba(255,29,75,0.4)"/>
          <circle cx="100" cy="195" r="6" stroke="var(--hud-red)" fill="rgba(255,29,75,0.6)"/>

          <!-- Torso / Rib Contours -->
          <path d="M 82 85 Q 100 95 118 85" stroke="var(--hud-red)" opacity="0.6"/>
          <path d="M 78 105 Q 100 120 122 105" stroke="var(--hud-red)" opacity="0.6"/>
          <path d="M 80 125 Q 100 140 120 125" stroke="var(--hud-red)" opacity="0.6"/>
          <path d="M 84 145 Q 100 155 116 145" stroke="var(--hud-red)" opacity="0.6"/>

          <!-- Shoulders & Arms -->
          <path d="M 100 70 L 65 85 L 48 135 L 36 185" stroke="var(--hud-red)"/>
          <path d="M 100 70 L 135 85 L 152 135 L 164 185" stroke="var(--hud-red)"/>
          <!-- Hands/Weapons Anchor nodes -->
          <circle cx="36" cy="185" r="4" fill="var(--hud-red-bright)" id="holoLeftHandNode"/>
          <circle cx="164" cy="185" r="4" fill="var(--hud-red-bright)" id="holoRightHandNode"/>

          <!-- Pelvis & Legs -->
          <path d="M 85 195 L 75 255 L 70 330 L 68 385" stroke="var(--hud-red)"/>
          <path d="M 115 195 L 125 255 L 130 330 L 132 385" stroke="var(--hud-red)"/>
          
          <!-- Outer circular HUD bracket around body -->
          <circle cx="100" cy="200" r="95" stroke="rgba(255,29,75,0.25)" stroke-width="0.8" stroke-dasharray="6,4"/>
          <!-- Radial crosshairs -->
          <line x1="100" y1="10" x2="100" y2="390" stroke="rgba(255,29,75,0.12)"/>
          <line x1="5" y1="200" x2="195" y2="200" stroke="rgba(255,29,75,0.12)"/>
        </svg>
      </div>
    </aside>

  </section>

  <!-- ── BOTTOM BAR ── -->
  <footer class="hud-bottombar">
    <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--hud-text-dim)">
      [ ↑ / ↓ / CLICK TO SELECT ] &nbsp; [ ENTER TO EQUIP ]
    </div>

    <!-- PlayStation Controller Actions -->
    <div class="hud-controller-prompts">
      <div class="prompt-item">
        <span class="btn-glyph">✕</span>
        <span>OK</span>
      </div>
      <div class="prompt-item">
        <span class="btn-glyph">○</span>
        <span>Close</span>
      </div>
      <div class="prompt-item">
        <span class="btn-glyph">□</span>
        <span>Equip</span>
      </div>
    </div>

    <!-- Currency Counters -->
    <div class="currency-panel">
      <div class="currency-badge">
        <span>5932</span>
        <span class="currency-glyph">魂</span>
      </div>
      <div class="currency-badge">
        <span>10400</span>
        <span class="currency-glyph">銭</span>
      </div>
    </div>
  </footer>

</main>

<script>
  // ── DATA STORES FOR SLOTS & THEMES ──
  const GHOSTWIRE_DATA = {
    1: {
      category: 'Weapons',
      title: 'Takeminakata',
      subtitle: 'Twin Blades / Tachi',
      status: 'Equipped',
      count: '2/9',
      alert: 'New Weapon Skill Can Be Unlocked',
      code: 'Ξ31',
      stats: [
        { label: 'ATTACK POWER', val: '428', fill: '82%' },
        { label: 'ETHER SYNERGY', val: '+35%', fill: '65%' },
        { label: 'SPEED / DEFLECTION', val: 'S-TIER', fill: '94%' }
      ],
      highlightNode: 'hands'
    },
    2: {
      category: 'Accessories',
      title: 'Prayer Beads of Fortune',
      subtitle: 'Talisman / Beads',
      status: 'Equipped',
      count: '1/3',
      alert: 'Karma Gain Increased by 25%',
      code: 'Ξ14',
      stats: [
        { label: 'DAMAGE RESISTANCE', val: '+20%', fill: '60%' },
        { label: 'SPIRIT REGENERATION', val: 'High', fill: '78%' },
        { label: 'ETHER CAPACITY', val: 'Max 150', fill: '85%' }
      ],
      highlightNode: 'heart'
    },
    3: {
      category: 'Combat Skills',
      title: 'Kuzuryu Fire Stream',
      subtitle: 'Ethereal Weaving / Seal',
      status: 'Active',
      count: '4/8',
      alert: 'Mastery Level 3 Achieved',
      code: 'Ξ88',
      stats: [
        { label: 'BURST RADIUS', val: '12m', fill: '90%' },
        { label: 'ETHER CONSUMPTION', val: '18 SP', fill: '45%' },
        { label: 'STUN POWER', val: 'Critical', fill: '96%' }
      ],
      highlightNode: 'head'
    },
    4: {
      category: 'Appearance',
      title: 'Shinobi Street Outfit',
      subtitle: 'Cosmetic / Custom Thread',
      status: 'Equipped',
      count: '1/1',
      alert: 'Exclusive Haunting Edition',
      code: 'Ξ00',
      stats: [
        { label: 'STYLE RATING', val: '100%', fill: '100%' },
        { label: 'VISIBILITY IN RAIN', val: 'Ghostly', fill: '88%' },
        { label: 'AUDIO FOOTSTEP DAMP', val: '-40dB', fill: '72%' }
      ],
      highlightNode: 'all'
    }
  };

  const MAPLEWOOD_DATA = {
    1: {
      category: 'Academic Courses',
      title: 'CS101 Computer Science',
      subtitle: 'Prof. Anderson · Lab Room B-204',
      status: 'Enrolled',
      count: '3/4 Credits',
      alert: 'Next Lecture: Tomorrow 10:00 AM',
      code: 'MW-CS',
      stats: [
        { label: 'COURSE ATTENDANCE', val: '96%', fill: '96%' },
        { label: 'MIDTERM GRADE', val: '92 / A+', fill: '92%' },
        { label: 'COMPLETED LABS', val: '8 of 10', fill: '80%' }
      ],
      highlightNode: 'head'
    },
    2: {
      category: 'Timetable & Schedule',
      title: 'Weekly Lecture Plan',
      subtitle: 'Mon - Fri · 5 Courses Active',
      status: 'Synchronized',
      count: 'Week 8',
      alert: 'Quiz Upcoming in 2 Days',
      code: 'MW-TT',
      stats: [
        { label: 'TOTAL HOURS / WEEK', val: '18 hrs', fill: '75%' },
        { label: 'FREE PERIODS', val: '6 slots', fill: '50%' },
        { label: 'ACADEMIC LOAD', val: 'Optimal', fill: '82%' }
      ],
      highlightNode: 'heart'
    },
    3: {
      category: 'Homework & Tasks',
      title: 'Database Normalization Project',
      subtitle: 'Due: Friday 23:59 · 15% Weight',
      status: 'In Progress',
      count: '2 Pending',
      alert: 'Submission Portal Open Now',
      code: 'MW-HW',
      stats: [
        { label: 'ASSIGNMENT COMPLETION', val: '75%', fill: '75%' },
        { label: 'CODE LINTING SCORE', val: '100%', fill: '100%' },
        { label: 'DIFFICULTY LEVEL', val: 'Intermediate', fill: '65%' }
      ],
      highlightNode: 'hands'
    },
    4: {
      category: 'Student Portal Settings',
      title: 'Student Profile & Fees',
      subtitle: 'Year 2 · Tuition Status: Paid',
      status: 'Active',
      count: 'Paid in Full',
      alert: 'All Account Invoices Cleared',
      code: 'MW-ID',
      stats: [
        { label: 'CUMULATIVE MARKS', val: '92% / Grade A', fill: '92%' },
        { label: 'PORTAL NOTIFICATIONS', val: '0 Unread', fill: '100%' },
        { label: 'CAMPUS LIBRARY LOANS', val: '2 Books', fill: '40%' }
      ],
      highlightNode: 'all'
    }
  };

  let currentTheme = 'ghostwire';
  let activeIndex = 1;
  let audioEnabled = true;

  // ── WEB AUDIO API SYNTHESIZER ──
  let audioCtx = null;

  function initAudio() {
    if (!audioCtx) {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (AudioContext) audioCtx = new AudioContext();
    }
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
  }

  function playCyberTone(type = 'select') {
    if (!audioEnabled) return;
    try {
      initAudio();
      if (!audioCtx) return;
      const now = audioCtx.currentTime;

      if (type === 'select') {
        // High-tech snappy click/chirp
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, now);
        osc.frequency.exponentialRampToValueAtTime(1760, now + 0.05);
        gain.gain.setValueAtTime(0.08, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.06);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now);
        osc.stop(now + 0.06);
      } else if (type === 'tab') {
        // Tab switch frequency
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(440, now);
        osc.frequency.linearRampToValueAtTime(660, now + 0.08);
        gain.gain.setValueAtTime(0.09, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.09);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now);
        osc.stop(now + 0.09);
      }
    } catch (e) {
      console.warn('Audio synthesis note:', e);
    }
  }

  function toggleAudio() {
    audioEnabled = !audioEnabled;
    const btn = document.getElementById('btnSoundToggle');
    if (audioEnabled) {
      btn.textContent = '🔊 SFX ON';
      btn.classList.add('active');
      playCyberTone('select');
    } else {
      btn.textContent = '🔇 SFX OFF';
      btn.classList.remove('active');
    }
  }

  // ── SELECTION LOGIC & CONNECTOR LINE POSITIONING ──
  function updateConnectorPositions() {
    const centerStage = document.getElementById('centerStage');
    const stageRect = centerStage.getBoundingClientRect();
    
    // Position passive wires for each slot row
    for (let i = 1; i <= 3; i++) {
      const row = document.getElementById('slotRow-' + i);
      const wire = document.getElementById('wire' + i);
      if (row && wire) {
        const rowRect = row.getBoundingClientRect();
        const relativeY = (rowRect.top + rowRect.height / 2) - stageRect.top;
        wire.style.top = relativeY + 'px';
      }
    }

    // Position active laser line and details panel
    let targetRow = document.getElementById('slotRow-' + activeIndex);
    if (!targetRow && activeIndex === 4) {
      targetRow = document.querySelector('.appearance-btn');
    }

    if (targetRow) {
      const rowRect = targetRow.getBoundingClientRect();
      const relativeY = (rowRect.top + rowRect.height / 2) - stageRect.top;
      
      const laser = document.getElementById('activeLaserBeam');
      const panel = document.getElementById('activeDetailsPanel');
      
      laser.style.top = relativeY + 'px';
      panel.style.top = relativeY + 'px';
    }
  }

  function selectSlot(index) {
    if (activeIndex === index) return;
    activeIndex = index;
    playCyberTone('select');

    // Update active row classes
    document.querySelectorAll('.item-slot-row').forEach(row => {
      row.classList.remove('active');
    });

    const activeRow = document.getElementById('slotRow-' + index);
    if (activeRow) activeRow.classList.add('active');

    // Populate data
    const dataSet = currentTheme === 'ghostwire' ? GHOSTWIRE_DATA : MAPLEWOOD_DATA;
    const item = dataSet[index];
    if (item) {
      document.getElementById('detTitle').textContent = item.title;
      document.getElementById('detSubtitle').textContent = item.subtitle;
      document.getElementById('detStatusBadge').textContent = item.status;
      document.getElementById('detCount').textContent = item.count;
      document.getElementById('detAlertText').textContent = item.alert;
      document.getElementById('detCode').textContent = item.code;

      // Stats
      if (item.stats && item.stats.length >= 3) {
        document.getElementById('statLbl1').textContent = item.stats[0].label;
        document.getElementById('statVal1').textContent = item.stats[0].val;
        document.getElementById('statFill1').style.width = item.stats[0].fill;

        document.getElementById('statLbl2').textContent = item.stats[1].label;
        document.getElementById('statVal2').textContent = item.stats[1].val;
        document.getElementById('statFill2').style.width = item.stats[1].fill;

        document.getElementById('statLbl3').textContent = item.stats[2].label;
        document.getElementById('statVal3').textContent = item.stats[2].val;
        document.getElementById('statFill3').style.width = item.stats[2].fill;
      }
    }

    updateConnectorPositions();
  }

  function switchTopTab(tabName, el) {
    playCyberTone('tab');
    document.querySelectorAll('.hud-tab-item').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
  }

  function switchTheme(theme) {
    currentTheme = theme;
    playCyberTone('tab');

    const btnG = document.getElementById('btnModeGhostwire');
    const btnM = document.getElementById('btnModeMaplewood');

    if (theme === 'maplewood') {
      btnG.classList.remove('active');
      btnM.classList.add('active');

      document.getElementById('hdrMenuTitle').textContent = 'Academic Portal';
      document.getElementById('hdrEmblem').textContent = '學';
      document.getElementById('hdrStamp').textContent = 'MAPLE¬';
      document.getElementById('catLabel1').textContent = 'Enrolled Courses';
      document.getElementById('catLabel2').textContent = 'Class Timetable';
      document.getElementById('catLabel3').textContent = 'Active Homework';
      document.getElementById('districtLabel').textContent = 'Maplewood Academy · Main Hall';
      document.getElementById('karmaDisplay').innerHTML = '<span class="plus-glyph">★</span><span>GRADE A</span>';
    } else {
      btnM.classList.remove('active');
      btnG.classList.add('active');

      document.getElementById('hdrMenuTitle').textContent = 'Equipment';
      document.getElementById('hdrEmblem').textContent = '器';
      document.getElementById('hdrStamp').textContent = 'ΞHUN¬';
      document.getElementById('catLabel1').textContent = 'Weapons';
      document.getElementById('catLabel2').textContent = 'Accessories';
      document.getElementById('catLabel3').textContent = 'Combat Skills';
      document.getElementById('districtLabel').textContent = 'Haunted District';
      document.getElementById('karmaDisplay').innerHTML = '<span class="plus-glyph">✚</span><span>KARMA <span id="karmaNum">0</span></span>';
    }

    // Refresh content for current active slot
    const prevIndex = activeIndex;
    activeIndex = -1;
    selectSlot(prevIndex);
  }

  // ── KEYBOARD NAVIGATION (UP/DOWN/ENTER) ──
  window.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowDown' || e.key === 's' || e.key === 'S') {
      e.preventDefault();
      let next = activeIndex + 1;
      if (next > 4) next = 1;
      selectSlot(next);
    } else if (e.key === 'ArrowUp' || e.key === 'w' || e.key === 'W') {
      e.preventDefault();
      let prev = activeIndex - 1;
      if (prev < 1) prev = 4;
      selectSlot(prev);
    } else if (e.key === 'Enter' || e.key === ' ') {
      playCyberTone('select');
      const badge = document.getElementById('detStatusBadge');
      badge.textContent = 'CONFIRMED ✓';
      setTimeout(() => {
        const item = (currentTheme === 'ghostwire' ? GHOSTWIRE_DATA : MAPLEWOOD_DATA)[activeIndex];
        badge.textContent = item ? item.status : 'Equipped';
      }, 900);
    }
  });

  // Re-calculate laser positions on window resize
  window.addEventListener('resize', updateConnectorPositions);
  window.addEventListener('load', () => {
    updateConnectorPositions();
  });
  // Initial calculation after font load
  setTimeout(updateConnectorPositions, 100);
</script>

</body>
</html>
