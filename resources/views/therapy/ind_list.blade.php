@extends('layouts.app')

@section('title', 'Individualizations for Study #' . $study->id)

@push('styles')
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; background:#fcfcfc; }
  .container { max-width: 1040px; margin: 0 auto; }
  .card { background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding: 1.2rem; margin-bottom: 16px; }
  h1 { font-size: 1.5rem; margin-bottom: 1rem; }
  .btn { padding:.55rem .85rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-block; }
  .btn.muted { background:#f4f4f4; color:#222; border-color:#999; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #ececec; padding:.6rem; text-align:left; }
  th { background:#fafafa; font-weight:600; }
  td a { color:#0d6efd; }
  .muted { color: #666; }
</style>
@endpush

@section('content')
<div class="container">
  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <h1 style="margin:0;">Individualizations for Study #{{ $study->id }}</h1>
    <div class="toolbar">
      <a href="{{ route('therapy.ind.create', $study->id) }}" class="btn">➕ New Individualization</a>
      <a href="{{ route('therapy.studies.list') }}" class="btn muted">← Back to Studies</a>
    </div>
  </div>

  @if(session('success'))
    <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:12px; padding:12px 14px; margin:16px 0; color:#1b5e20;">
      {{ session('success') }}
    </div>
  @endif

  <div class="card">
    <h2 style="margin-top:0;">Study Summary</h2>
    <div style="display:grid; gap:10px; grid-template-columns: repeat(auto-fit, minmax(220px,1fr));">
      <div><strong>Title:</strong> <br><span class="muted">{{ $study->article_title ?? '—' }}</span></div>
      <div><strong>Journal:</strong> <br><span class="muted">{{ $study->journal_title ?? '—' }}</span></div>
      <div><strong>Population:</strong> <br><span class="muted">{{ $study->population_pico ?? '—' }}</span></div>
      <div><strong>Exposure:</strong> <br><span class="muted">{{ $study->exposure_pico ?? '—' }}</span></div>
      <div><strong>Comparator:</strong> <br><span class="muted">{{ $study->comparator_pico ?? '—' }}</span></div>
      <div><strong>Outcome:</strong> <br><span class="muted">{{ $study->outcome_pico ?? '—' }}</span></div>
      <div><strong>RR:</strong> <br><span class="muted">{{ $study->rr !== null ? number_format($study->rr, 3) : '—' }}</span></div>
      <div><strong>ARR:</strong> <br><span class="muted">{{ $study->arr !== null ? number_format($study->arr, 3) : '—' }}</span></div>
      <div><strong>NNT:</strong> <br><span class="muted">{{ $study->nnt !== null ? number_format($study->nnt, 0) : '—' }}</span></div>
      <div><strong>NNH:</strong> <br><span class="muted">{{ $study->nnh !== null ? number_format($study->nnh, 0) : '—' }}</span></div>
      <div><strong>DOI:</strong> <br>
        @if($study->doi)
          <a href="https://doi.org/{{ $study->doi }}" target="_blank" rel="noopener">{{ $study->doi }}</a>
        @else
          <span class="muted">—</span>
        @endif
      </div>
    </div>
  </div>

  <div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
      <h2 style="margin:0;">Individualizations ({{ count($individualizations) }})</h2>
      <a href="{{ route('therapy.ind.create', $study->id) }}" class="btn" style="font-size:.9rem;">➕ Add New</a>
    </div>
    @if(count($individualizations) > 0)
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>ARR (Individualized)</th>
            <th>NNT (Individualized)</th>
            <th>Baseline Risk</th>
            <th>Setting</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($individualizations as $ind)
            <tr>
              <td>{{ $ind->id }}</td>
              <td>{{ $ind->arr_ind !== null ? number_format($ind->arr_ind, 3) : '—' }}</td>
              <td>{{ $ind->nnt_ind !== null ? $ind->nnt_ind : '—' }}</td>
              <td>{{ $ind->baseline_risk !== null ? number_format($ind->baseline_risk, 3) : '—' }}</td>
              <td>{{ $ind->scenario_setting ?? '—' }}</td>
              <td>{{ \Carbon\Carbon::parse($ind->created_at)->format('M d, Y H:i') }}</td>
              <td><a href="{{ route('therapy.ind.results', $ind->id) }}">View Details</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p>No individualizations yet for this study. <a href="{{ route('therapy.ind.create', $study->id) }}">Create the first one</a>.</p>
    @endif
  </div>
</div>
@endsection
