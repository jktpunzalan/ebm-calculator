@extends('layouts.app')

@section('title', 'Individualize Study #' . $study->id)

@push('styles')
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f8f9fb; margin:0; }
  .wrap { max-width: 1080px; margin: 0 auto; padding: 32px 20px 56px; }
  h1 { font-size: 1.9rem; margin-bottom: 1rem; }
  .card { background:#fff; border:1px solid #e6e6e6; border-radius:14px; padding:20px 22px; margin-bottom:20px; box-shadow:0 18px 36px -28px rgba(71,85,105,.65); }
  .toolbar { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:18px; }
  .btn { padding:.6rem .95rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
  .btn.muted { background:#f1f2f6; color:#1f2937; border-color:#d0d7e3; }
  .grid { display:grid; gap:14px; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); }
  label { font-weight:600; color:#1f2937; }
  input[type="number"], input[type="text"], textarea { width:100%; padding:.65rem .75rem; border-radius:10px; border:1px solid #d0d7e3; background:#fdfefe; font-size:1rem; }
  textarea { min-height: 110px; resize: vertical; }
  .help { font-size:.86rem; color:#6b7280; margin-top:4px; }
  .metrics { display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); margin-top:12px; }
  .metric { background:#eef3ff; border-radius:12px; padding:14px; text-align:center; border:1px solid #d4defa; }
  .metric .value { font-size:1.6rem; font-weight:700; color:#0d6efd; }
  .metric .label { font-size:.85rem; margin-top:6px; text-transform:uppercase; color:#4b5563; letter-spacing:.05em; }
  .muted { color:#6b7280; font-size:.9rem; }
  .latest { background:#fef9e8; border:1px solid #fde68a; border-radius:12px; padding:16px; }
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="toolbar">
    <a href="{{ route('therapy.ind.list', $study->id) }}" class="btn muted">← Back to Individualizations</a>
    <a href="{{ route('therapy.studies.list') }}" class="btn muted">📑 Studies</a>
  </div>

  <h1>Individualize Study #{{ $study->id }}</h1>

  <div class="card">
    <h2>Study Summary</h2>
    <div class="grid">
      <div>
        <label>Title</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->article_title ?? '—' }}</p>
      </div>
      <div>
        <label>Authors</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $authors }}</p>
      </div>
      <div>
        <label>Journal</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->journal_title ?? '—' }}</p>
      </div>
      <div>
        <label>Population</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->population_pico ?? '—' }}</p>
      </div>
      <div>
        <label>Exposure</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->exposure_pico ?? '—' }}</p>
      </div>
      <div>
        <label>Comparator</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->comparator_pico ?? '—' }}</p>
      </div>
      <div>
        <label>Outcome</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->outcome_pico ?? '—' }}</p>
      </div>
      <div>
        <label>RR</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->rr !== null ? number_format($study->rr, 3) : '—' }}</p>
      </div>
      <div>
        <label>ARR</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->arr !== null ? number_format($study->arr, 3) : '—' }}</p>
      </div>
      <div>
        <label>NNT</label>
        <p class="muted" style="margin:.35rem 0 0;">{{ $study->nnt !== null ? number_format($study->nnt, 0) : '—' }}</p>
      </div>
    </div>
  </div>

  @if($latest)
  <div class="card latest">
    <strong>Last Individualization</strong>
    <p style="margin:.5rem 0 0;">ARR<sub>ind</sub>: {{ $latest->arr_ind !== null ? number_format($latest->arr_ind, 3) : '—' }}, NNT<sub>ind</sub>: {{ $latest->nnt_ind !== null ? $latest->nnt_ind : '—' }}</p>
    <p style="margin:.25rem 0 0;" class="muted">Baseline risk: {{ $latest->baseline_risk !== null ? number_format($latest->baseline_risk, 3) : '—' }} | Saved {{ \Carbon\Carbon::parse($latest->created_at)->diffForHumans() }}</p>
  </div>
  @endif

  <form method="post" action="{{ route('therapy.ind.store', $study->id) }}" class="card">
    @csrf
    <h2>Individualization Inputs</h2>
    @if($errors->any())
      <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:12px; padding:12px; margin-bottom:14px; color:#991b1b;">
        <strong>There were problems with your submission:</strong>
        <ul style="margin:8px 0 0 20px;">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="grid">
      <div>
        <label for="baseline_risk">Baseline Risk (0 – 1)</label>
        <input type="number" step="0.0001" min="0" max="1" id="baseline_risk" name="baseline_risk" value="{{ old('baseline_risk') }}">
        <p class="help">Probability of outcome without treatment. Used with study RR.</p>
      </div>
      <div>
        <label for="scenario_age">Age</label>
        <input type="number" min="0" id="scenario_age" name="scenario_age" value="{{ old('scenario_age') }}">
      </div>
      <div>
        <label for="scenario_sex">Sex</label>
        <input type="text" id="scenario_sex" name="scenario_sex" value="{{ old('scenario_sex') }}" placeholder="e.g., Female">
      </div>
      <div>
        <label for="scenario_setting">Setting</label>
        <input type="text" id="scenario_setting" name="scenario_setting" value="{{ old('scenario_setting') }}" placeholder="e.g., Outpatient">
      </div>
      <div style="grid-column:1 / -1;">
        <label for="scenario_comorbidities">Comorbidities</label>
        <textarea id="scenario_comorbidities" name="scenario_comorbidities" placeholder="e.g., Diabetes, CKD">{{ old('scenario_comorbidities') }}</textarea>
      </div>
      <div style="grid-column:1 / -1;">
        <label for="scenario_notes">Notes</label>
        <textarea id="scenario_notes" name="scenario_notes" placeholder="Anything important to apply the evidence">{{ old('scenario_notes') }}</textarea>
      </div>
    </div>

    <div class="metrics">
      <div class="metric">
        <div class="value" id="treated-risk">—</div>
        <div class="label">Estimated Treated Risk</div>
      </div>
      <div class="metric">
        <div class="value" id="arr-ind">—</div>
        <div class="label">ARR<sub>ind</sub></div>
      </div>
      <div class="metric">
        <div class="value" id="nnt-ind">—</div>
        <div class="label">NNT<sub>ind</sub></div>
      </div>
    </div>

    <p class="muted" style="margin-top:12px;">Values update automatically as you enter a baseline risk. They will be saved together with this individualization entry.</p>

    <div class="toolbar" style="margin-top:18px;">
      <button type="submit" class="btn">Save Individualization</button>
      <a href="{{ route('therapy.ind.list', $study->id) }}" class="btn muted">Cancel</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
  (function(){
    const rr = {{ $study->rr !== null ? json_encode((float) $study->rr) : 'null' }};
    const baselineInput = document.getElementById('baseline_risk');
    const treatedOut = document.getElementById('treated-risk');
    const arrOut = document.getElementById('arr-ind');
    const nntOut = document.getElementById('nnt-ind');

    function fmt(num){
      if (num === null || !isFinite(num)) return '—';
      return Number(num).toFixed(3);
    }

    function fmtInt(num){
      if (num === null || !isFinite(num)) return '—';
      return Math.round(num);
    }

    function compute(){
      const baseline = parseFloat(baselineInput.value);
      if (isNaN(baseline) || rr === null){
        treatedOut.textContent = '—';
        arrOut.textContent = '—';
        nntOut.textContent = '—';
        return;
      }
      const treated = Math.max(0, Math.min(1, baseline * rr));
      const arr = baseline - treated;
      let nnt = null;
      if (arr !== 0){
        nnt = 1 / Math.abs(arr);
      }
      treatedOut.textContent = fmt(treated);
      arrOut.textContent = fmt(arr);
      nntOut.textContent = fmtInt(nnt);
    }

    baselineInput && baselineInput.addEventListener('input', compute);
    compute();
  })();
</script>
@endpush
@endsection
