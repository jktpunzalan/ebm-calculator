@extends('layouts.app')

@section('title', 'Reading Journal — Validity & 2×2')

@push('styles')
<style>
    .page-shell { max-width: 1080px; margin: 0 auto; display:flex; flex-direction:column; gap:18px; }
    .card { background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:22px 26px; box-shadow:0 18px 38px -30px rgba(15,23,42,0.45); }
    h1 { margin:0; font-size:1.65rem; }
    h2 { margin:0 0 12px; font-size:1.18rem; }
    .muted { color:#64748b; font-size:.95rem; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap:18px; }
    .row { display:grid; grid-template-columns: minmax(220px, 1fr) minmax(260px, 1.5fr); gap:16px; align-items:start; }
    label { font-weight:600; color:#1f2937; margin-bottom:6px; display:block; }
    textarea, input[type="number"], input[type="text"] {
        width:100%;
        padding:.65rem .75rem;
        border:1px solid #cbd5f5;
        border-radius:10px;
        background:#f8fafc;
        transition:border-color .2s ease, box-shadow .2s ease;
    }
    textarea:focus, input[type="number"]:focus, input[type="text"]:focus {
        outline:none;
        border-color:#2563eb;
        box-shadow:0 0 0 3px rgba(37,99,235,0.15);
    }
    textarea { min-height:72px; resize: vertical; }
    .actions { display:flex; gap:12px; flex-wrap:wrap; }
    .btn { padding:.65rem 1.05rem; border-radius:10px; border:1px solid #2563eb; background:#2563eb; color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; }
    .btn.secondary { background:#f8fafc; color:#1f2937; border-color:#cbd5f5; }
    .btn.secondary:hover { background:#e2e8f0; }
    table { width:100%; border-collapse: collapse; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
    th, td { padding:.7rem .75rem; border-bottom:1px solid #e2e8f0; text-align:center; }
    th { background:#f8fafc; font-weight:600; color:#1f2937; }
    tbody tr:last-child td { border-bottom:none; }
</style>
@endpush

@section('content')
<div class="page-shell">
  <h1>Reading Journal — Validity & 2×2</h1>

  <form method="post" action="{{ route('therapy.compute.results.post') }}">
    @csrf
    <input type="hidden" name="article_id" value="{{ $article['id'] ?? '' }}">
    <input type="hidden" name="doi" value="{{ $doi }}">
    <input type="hidden" name="population_pico" value="{{ $population_pico }}">
    <input type="hidden" name="exposure_pico" value="{{ $exposure_pico }}">
    <input type="hidden" name="comparator_pico" value="{{ $comparator_pico }}">
    <input type="hidden" name="outcome_pico" value="{{ $outcome_pico }}">

    <div class="card">
      <h2>Article Details</h2>
      <div class="grid">
        <div><strong>Title:</strong> {{ $title ?: '—' }}</div>
        <div><strong>Journal:</strong> {{ $journal ?: '—' }}</div>
        <div><strong>Year:</strong> {{ $pub_year ?: '—' }}</div>
        <div><strong>Publisher:</strong> {{ $publisher ?: '—' }}</div>
        <div><strong>DOI:</strong> {{ $doi ?: '—' }}</div>
        <div style="grid-column:1/-1"><strong>Population:</strong> {{ $population_pico ?: '—' }}</div>
        <div><strong>Exposure:</strong> {{ $exposure_pico ?: '—' }}</div>
        <div><strong>Comparator:</strong> {{ $comparator_pico ?: '—' }}</div>
        <div style="grid-column:1/-1"><strong>Outcome:</strong> {{ $outcome_pico ?: '—' }}</div>
      </div>
    </div>

    <div class="card">
      <h2>Validity Checklist (Therapies)</h2>
      <p class="muted">Tick items that are satisfied. Add brief remarks if needed.</p>

      @php
        $items = [
          'rand'    => 'Random allocation of treatment?',
          'conceal' => 'Allocation concealment adequate?',
          'blind'   => 'Blinding of patients, clinicians, and outcome assessors?',
          'itt'     => 'Intention-to-treat analysis used?',
          'follow'  => 'Follow-up long enough and complete?',
        ];
      @endphp

      @foreach($items as $key => $label)
        <div class="row" style="margin:.35rem 0;">
          <label>
            <input type="checkbox" name="valid[{{ $key }}]" value="1"> {{ $label }}
          </label>
          <textarea name="valid_remarks[{{ $key }}]" placeholder="Remarks (optional)"></textarea>
        </div>
      @endforeach
    </div>

    <div class="card">
      <h2>2×2 Table Inputs</h2>
      <p class="muted">Provide <strong>A and C</strong> (required), plus either <strong>(B & D)</strong> or <strong>(N1 & N0)</strong>. System will derive the rest.</p>
      <table>
        <thead>
          <tr>
            <th></th>
            <th>Outcome = Yes</th>
            <th>Outcome = No</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>Exposed (Treatment)</th>
            <td>
              <label class="muted" for="A">A (events) *</label>
              <input type="number" min="0" id="A" name="A" placeholder="A" required>
            </td>
            <td>
              <label class="muted" for="B">B (no events)</label>
              <input type="number" min="0" id="B" name="B" placeholder="B">
            </td>
            <td>
              <label class="muted" for="N1">N1 (total exposed)</label>
              <input type="number" min="0" id="N1" name="N1" placeholder="N1">
            </td>
          </tr>
          <tr>
            <th>Unexposed (Control)</th>
            <td>
              <label class="muted" for="C">C (events) *</label>
              <input type="number" min="0" id="C" name="C" placeholder="C" required>
            </td>
            <td>
              <label class="muted" for="D">D (no events)</label>
              <input type="number" min="0" id="D" name="D" placeholder="D">
            </td>
            <td>
              <label class="muted" for="N0">N0 (total unexposed)</label>
              <input type="number" min="0" id="N0" name="N0" placeholder="N0">
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="actions">
        <button class="btn" type="submit">Continue to Compute</button>
        <a class="btn secondary" href="{{ route('therapy.article.form') }}">Back</a>
      </div>
    </div>
  </form>

</div>
@endsection
