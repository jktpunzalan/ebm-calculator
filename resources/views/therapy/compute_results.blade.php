@extends('layouts.app')

@section('title', 'Computation Results')

@push('styles')
<style>
  body {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    background: #fcfcfc;
    margin: 0;
    color: #111;
  }
  .wrap { max-width: 1080px; margin: 0 auto; padding: 32px 20px 56px; }
  h1 { font-size: 2rem; margin-bottom: 1rem; }
  .toolbar { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
  .btn { padding:.6rem .95rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
  .btn.muted { background:#f4f4f4; color:#222; border-color:#999; }
  .grid { display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
  .section { background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding:20px; margin-bottom:18px; box-shadow:0 12px 30px -22px rgba(15,23,42,0.65); }
  .section h2 { margin:0 0 12px; font-size:1.2rem; }
  table { width:100%; border-collapse:collapse; }
  th, td { border:1px solid #ececec; padding:.6rem .7rem; text-align:left; }
  th { background:#fafafa; font-weight:600; }
  .muted { color:#666; font-size:.92rem; }
  .badge { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:999px; font-size:.78rem; font-weight:600; border:1px solid; }
  .badge.ok { background:#e6f6ee; border-color:#a7e0c4; color:#0f5132; }
  .badge.warn { background:#fff4e5; border-color:#ffd8a8; color:#7a3e00; }
  .metrics { display:grid; gap:10px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
  .metric { background:#f6f7fb; border-radius:10px; padding:16px; text-align:center; border:1px solid #e0e6f0; }
  .metric .value { font-size:1.5rem; font-weight:700; color:#0d6efd; }
  .metric .label { font-size:.85rem; color:#57607a; margin-top:4px; text-transform:uppercase; letter-spacing:0.04em; }
  .interpretation { margin-top:12px; padding:14px 16px; border-radius:12px; border:1px solid #cfe2ff; background:#f5f9ff; }
  .valid-list { margin:10px 0 0; padding-left:1.1rem; color:#424a5d; }
  .valid-list li + li { margin-top:4px; }
  .meta { display:flex; flex-direction:column; gap:6px; font-size:.95rem; }
  .meta strong { color:#1a2142; }
  @media (max-width: 640px){
    .toolbar { flex-direction:column; align-items:flex-start; }
  }
</style>
@endpush

@php
    $format3 = fn($value) => $value === null ? '—' : number_format((float) $value, 3);
    $formatInt = fn($value) => $value === null ? '—' : number_format((float) $value, 0);
@endphp

@section('content')
<div class="wrap">
  <div class="toolbar">
    <a href="{{ route('therapy.article.form') }}" class="btn">← New Study</a>
    <a href="{{ route('therapy.studies.list') }}" class="btn muted">📑 View Studies</a>
    <a href="{{ route('therapy.ind.create', $studyId) }}" class="btn">✨ Individualize This Study</a>
  </div>

  <h1>Computation Results</h1>

  <div class="section">
    <h2>Study Summary</h2>
    <div class="grid">
      <div class="meta">
        <strong>Title</strong>
        <span>{{ optional($article)->article_title ?: '—' }}</span>
      </div>
      <div class="meta">
        <strong>Authors</strong>
        <span>{{ $authors }}</span>
      </div>
      <div class="meta">
        <strong>Journal</strong>
        <span>{{ optional($article)->journal_title ?: '—' }}</span>
      </div>
      <div class="meta">
        <strong>Population</strong>
        <span>{{ $articlePopulation ?: '—' }}</span>
      </div>
      <div class="meta">
        <strong>Exposure</strong>
        <span>{{ $treatment }}</span>
      </div>
      <div class="meta">
        <strong>Comparator</strong>
        <span>{{ $control }}</span>
      </div>
      <div class="meta">
        <strong>Outcome</strong>
        <span>{{ $outcome }}</span>
      </div>
      <div class="meta">
        <strong>DOI</strong>
        @if($doi)
          <a href="https://doi.org/{{ $doi }}" target="_blank" rel="noopener">{{ $doi }}</a>
        @else
          <span>—</span>
        @endif
      </div>
    </div>
  </div>

  <div class="section">
    <h2>Validity Checklist</h2>
    <div class="badge {{ $validCount >= 3 ? 'ok' : 'warn' }}">{{ $validMessage }}</div>
    <ul class="valid-list">
      @foreach($validLabels as $key => $label)
        <li>
          <strong>{{ $label }}:</strong>
          {{ array_key_exists($key, $validChecks) ? 'Yes' : 'No' }}
          @if(!empty($validNotes[$key]))
            — <em>{{ $validNotes[$key] }}</em>
          @endif
        </li>
      @endforeach
    </ul>
  </div>

  <div class="section">
    <h2>2×2 Table</h2>
    <table aria-label="2 by 2 contingency table">
      <thead>
        <tr>
          <th></th>
          <th>{{ $outcome }}</th>
          <th>No {{ $outcome }}</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>{{ $treatment }}</th>
          <td>{{ $counts['A'] }}</td>
          <td>{{ $counts['B'] }}</td>
          <td>{{ $counts['N1'] }}</td>
        </tr>
        <tr>
          <th>{{ $control }}</th>
          <td>{{ $counts['C'] }}</td>
          <td>{{ $counts['D'] }}</td>
          <td>{{ $counts['N0'] }}</td>
        </tr>
      </tbody>
    </table>
    <p class="muted" style="margin-top:8px;">Values derived automatically where possible to ensure each row sums to the total.</p>
  </div>

  <div class="section">
    <h2>Computed Metrics</h2>
    <div class="metrics">
      <div class="metric">
        <div class="value">{{ $format3($metrics['Re']) }}</div>
        <div class="label">Risk (Exposed)</div>
      </div>
      <div class="metric">
        <div class="value">{{ $format3($metrics['Ru']) }}</div>
        <div class="label">Risk (Unexposed)</div>
      </div>
      <div class="metric">
        <div class="value">{{ $format3($metrics['RR']) }}</div>
        <div class="label">Risk Ratio (RR)</div>
      </div>
      <div class="metric">
        <div class="value">{{ $format3($metrics['ARR']) }}</div>
        <div class="label">Absolute Risk Reduction (ARR)</div>
      </div>
      <div class="metric">
        <div class="value">{{ $formatInt($metrics['NNT']) }}</div>
        <div class="label">NNT</div>
      </div>
      <div class="metric">
        <div class="value">{{ $formatInt($metrics['NNH']) }}</div>
        <div class="label">NNH (Harm)</div>
      </div>
    </div>
    <div class="interpretation">
      <strong>Interpretation:</strong>
      <p style="margin:8px 0 0;">{{ $interpretation }}</p>
    </div>
  </div>

  <div class="section">
    <h2>What Happens Next?</h2>
    <p class="muted">This study has been saved. You can now individualize the results for a specific patient context or return to the studies list.</p>
    <div class="toolbar" style="margin:0;">
      <a href="{{ route('therapy.ind.create', $studyId) }}" class="btn">✨ Individualize Now</a>
      <a href="{{ route('therapy.ind.list', $studyId) }}" class="btn muted">📂 View Individualizations</a>
      <a href="{{ route('therapy.studies.list') }}" class="btn muted">📑 Back to Studies</a>
    </div>
  </div>
</div>
@endsection
