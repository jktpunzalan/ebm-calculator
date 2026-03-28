const API_BASE = 'https://api.saliksic.com/api';

async function apiGet(path) {
  const res = await fetch(`${API_BASE}${path}`);
  if (!res.ok) throw new Error(`GET ${path} ${res.status}`);
  return res.json();
}

function countUp(el, to, duration = 800) {
  const start = 0; const diff = to - start; const t0 = performance.now();
  function frame(t) {
    const p = Math.min(1, (t - t0) / duration);
    el.textContent = Math.round(start + diff * p).toLocaleString();
    if (p < 1) requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}

function fmtDate(iso) {
  if (!iso) return '';
  try { return new Date(iso).toLocaleDateString(); } catch { return iso; }
}

function studyTypeClass(type) {
  const key = (type || '').toLowerCase();
  if (key.includes('therapy')) return 'type-therapy';
  if (key.includes('diagn')) return 'type-diagnosis';
  if (key.includes('harm')) return 'type-harm';
  if (key.includes('progn')) return 'type-prognosis';
  return '';
}
