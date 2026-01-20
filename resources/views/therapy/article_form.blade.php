@extends('layouts.app')

@section('title', 'EBM Study Entry')

@push('styles')
<style>
  :root { --gap: 18px; }
  .page-shell { max-width: 1080px; margin: 0 auto; display: flex; flex-direction: column; gap: 18px; }
  .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 22px 26px; box-shadow: 0 20px 40px -32px rgba(15, 23, 42, 0.45); }
  h1 { margin: 0; font-size: 1.75rem; }
  h2 { margin: 0 0 12px; font-size: 1.2rem; }
  .muted { color: #64748b; font-size: .95rem; }
  .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: var(--gap); }
  label { display: block; font-weight: 600; margin-bottom: 6px; color: #1f2937; }
  input[type="text"], input[type="number"] {
    width: 100%;
    padding: .65rem .75rem;
    border: 1px solid #cbd5f5;
    border-radius: 10px;
    background: #f8fafc;
    color: #0f172a;
    font-size: 1rem;
    transition: border-color .2s ease, box-shadow .2s ease;
  }
  input[type="text"]:focus, input[type="number"]:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  }
  input[readonly] { background: #e2e8f0; color: #475569; }
  .actions { display: flex; flex-wrap: wrap; gap: 12px; }
  .btn { padding: .65rem 1.05rem; border-radius: 10px; border: 1px solid #2563eb; background: #2563eb; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; }
  .btn.secondary { background: #f8fafc; color: #1f2937; border-color: #cbd5f5; }
  .btn.secondary:hover { background: #e2e8f0; }
  .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
  .hint { color: #64748b; font-size: .85rem; display: block; margin-top: 4px; }
  .status { font-size: .85rem; font-weight: 600; }
  .status.ok { color: #15803d; }
  .status.err { color: #b91c1c; }
  .banner { padding: 12px 16px; border-radius: 12px; margin: 10px 0; font-weight: 600; }
  .banner.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
  .banner.info { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
  .banner.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
  @media (max-width: 768px) {
    .card { padding: 18px 20px; }
  }
</style>
@endpush

@section('content')
<div class="page-shell">
  <div class="toolbar">
    <h1>EBM Study Entry</h1>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn secondary" type="button" id="load_example">Load Example</button>
      <button class="btn secondary" type="button" id="clear_example">Clear Example</button>
      <a class="btn secondary" href="{{ route('therapy.studies.list') }}">View Previous Studies</a>
    </div>
  </div>

  <div id="alerts">
    @if(session('success'))
      <div class="banner success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="banner error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
      <div class="banner info">{{ session('info') }}</div>
    @endif
  </div>

  <form method="post" action="{{ route('therapy.reading.journal.post') }}" novalidate>
    @csrf

    <!-- Section A: DOI & Article Details -->
    <div class="card">
      <h2>Step 1 — DOI & Article Details</h2>
      <p class="muted">Enter the DOI then click <em>Fetch from DOI</em>. Article metadata will be auto-filled.</p>
      <div class="grid">
        <div style="grid-column:1/-1;">
          <label for="doi">DOI</label>
          <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="doi" name="doi" placeholder="e.g., 10.1001/jama.2024.12345">
            <button class="btn" type="button" id="doi_fetch_btn" onclick="fetchDOI()">Fetch from DOI</button>
            <small class="hint">Enter a DOI to autofill article details.</small>
            <small class="status" id="doi_status"></small>
          </div>
        </div>

        <div>
          <label for="article_title">Article Title</label>
          <input type="text" id="article_title" name="article_title" readonly>
        </div>
        <div>
          <label for="journal">Journal</label>
          <input type="text" id="journal" name="journal" readonly>
        </div>
        <div>
          <label for="pub_year">Publication Year</label>
          <input type="number" id="pub_year" name="pub_year" readonly>
        </div>
        <div>
          <label for="publisher">Publisher</label>
          <input type="text" id="publisher" name="publisher" readonly>
        </div>
      </div>
      <input type="hidden" id="article_id" name="article_id">
    </div>

    <!-- Section A.1: PECO -->
    <div class="card">
      <h2>Step 2 — PECO (from Journal)</h2>
      <p class="muted">Capture the Population, Exposure, Comparator, and Outcome as described by the article.</p>
      <div class="grid">
        <div style="grid-column:1/-1;">
          <label for="population_pico">Population</label>
          <input type="text" id="population_pico" name="population_pico" placeholder="e.g., Adults with HFrEF NYHA II–IV">
        </div>
        <div>
          <label for="exposure_pico">Exposure</label>
          <input type="text" id="exposure_pico" name="exposure_pico" placeholder="e.g., Dapagliflozin">
        </div>
        <div>
          <label for="comparator_pico">Comparator</label>
          <input type="text" id="comparator_pico" name="comparator_pico" placeholder="e.g., Placebo">
        </div>
        <div style="grid-column:1/-1;">
          <label for="outcome_pico">Outcome</label>
          <input type="text" id="outcome_pico" name="outcome_pico" placeholder="e.g., CV death or hospitalization">
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="card">
      <div class="actions">
        <button class="btn" type="submit">Compute</button>
        <button class="btn secondary" type="reset">Clear</button>
        <a class="btn secondary" href="{{ route('therapy.studies.list') }}">View Previous Studies</a>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
async function fetchDOI() {
  const doiEl = document.getElementById('doi');
  const doi = (doiEl.value || '').trim();
  const statusEl = document.getElementById('doi_status');
  statusEl.className = 'status';
  statusEl.textContent = '';
  if (!doi) { statusEl.classList.add('err'); statusEl.textContent = 'Enter a DOI first'; return; }

  const fd = new FormData();
  fd.append('doi', doi);
  fd.append('_token', '{{ csrf_token() }}');

  try {
    const res = await fetch('{{ route('therapy.doi.autofetch') }}', { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.ok) {
      statusEl.classList.add('err');
      statusEl.textContent = 'DOI lookup failed: ' + (data.error || 'Unknown error');
      return;
    }

    const a = data.article || {};
    document.getElementById('article_id').value = a.id || '';
    document.getElementById('article_title').value = a.article_title || '';
    document.getElementById('journal').value = a.journal_title || '';
    document.getElementById('pub_year').value = a.pub_year || '';
    document.getElementById('publisher').value = a.publisher || '';

    statusEl.classList.add('ok');
    statusEl.textContent = 'DOI details loaded';
  } catch (e) {
    statusEl.classList.add('err');
    statusEl.textContent = 'Network error while fetching DOI';
  }
}

document.getElementById('load_example')?.addEventListener('click', function() {
  const eg = {
    doi: '10.1056/NEJMoa2033700',
    population_pico: 'Adults ≥18 years with condition X',
    exposure_pico: 'Drug A',
    comparator_pico: 'Placebo',
    outcome_pico: 'Composite CV outcome'
  };
  Object.entries(eg).forEach(([id, val]) => {
    const el = document.getElementById(id);
    if (el) el.value = val;
  });
});

document.getElementById('clear_example')?.addEventListener('click', function() {
  const ids = ['doi', 'article_title', 'journal', 'pub_year', 'publisher', 'article_id', 'population_pico', 'exposure_pico', 'comparator_pico', 'outcome_pico'];
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
});
</script>
@endpush
