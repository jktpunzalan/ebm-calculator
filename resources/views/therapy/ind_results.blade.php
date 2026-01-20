@extends('layouts.app')

@section('title', 'Individualization Results #' . $ind->id)

@push('styles')
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; background:#fcfcfc; }
  .container { max-width: 1040px; margin: 0 auto; }
  .card { background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding: 1.2rem; margin-bottom: 16px; }
  h1 { font-size: 1.5rem; margin-bottom: 1rem; }
  .btn { padding:.55rem .85rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-block; }
  .btn.muted { background:#f4f4f4; color:#222; border-color:#999; }
  .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 16px; }
  .metric { padding: 1rem; background:#f8f9fa; border-radius:8px; text-align:center; }
  .metric-value { font-size: 2rem; font-weight:700; color:#0d6efd; }
  .metric-label { font-size: .9rem; color:#666; margin-top: .5rem; }
</style>
@endpush

@section('content')
<div class="container">
  <h1>Individualization Results #{{ $ind->id }}</h1>
  
  <div class="card">
    <p><a href="{{ route('therapy.ind.list', $ind->study_id) }}" class="btn muted">← Back to Study</a></p>
  </div>

  <div class="card">
    <h2>Study Results</h2>
    <div class="grid">
      <div class="metric">
        <div class="metric-value">{{ $study->rr ?? '—' }}</div>
        <div class="metric-label">Relative Risk (RR)</div>
      </div>
      <div class="metric">
        <div class="metric-value">{{ $study->arr ?? '—' }}</div>
        <div class="metric-label">Absolute Risk Reduction (ARR)</div>
      </div>
      <div class="metric">
        <div class="metric-value">{{ $study->nnt ?? '—' }}</div>
        <div class="metric-label">Number Needed to Treat (NNT)</div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2>Individualized Results</h2>
    <div class="grid">
      <div class="metric">
        <div class="metric-value">{{ $ind->arr_ind ?? '—' }}</div>
        <div class="metric-label">Individualized ARR</div>
      </div>
      <div class="metric">
        <div class="metric-value">{{ $ind->nnt_ind ?? '—' }}</div>
        <div class="metric-label">Individualized NNT</div>
      </div>
    </div>
    <p style="margin-top:1rem;"><small>Created: {{ $ind->created_at }}</small></p>
  </div>
</div>
@endsection
