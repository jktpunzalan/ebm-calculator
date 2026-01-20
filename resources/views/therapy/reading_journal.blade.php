@extends('layouts.app')

@section('title', 'Reading Journal')

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
  .toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="toolbar">
    <h1 style="margin:0;">Reading Journal</h1>
    <div style="display:flex; gap:8px;">
      <a href="{{ route('therapy.article.form') }}" class="btn">➕ New Entry</a>
      <a href="{{ route('home') }}" class="btn muted">🏠 Home</a>
    </div>
  </div>

  <div class="card">
    <h2>Recent Studies ({{ count($studies) }})</h2>
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Exposure</th>
          <th>Comparator</th>
          <th>Outcome</th>
          <th>RR</th>
          <th>ARR</th>
          <th>NNT</th>
          <th>DOI</th>
        </tr>
      </thead>
      <tbody>
        @forelse($studies as $study)
          <tr>
            <td>{{ \Carbon\Carbon::parse($study->created_at)->format('Y-m-d') }}</td>
            <td>{{ $study->exposure_pico ?: '—' }}</td>
            <td>{{ $study->comparator_pico ?: '—' }}</td>
            <td>{{ $study->outcome_pico ?: '—' }}</td>
            <td>{{ $study->rr !== null ? number_format($study->rr, 3) : '—' }}</td>
            <td>{{ $study->arr !== null ? number_format($study->arr, 3) : '—' }}</td>
            <td>{{ $study->nnt !== null ? number_format($study->nnt, 1) : '—' }}</td>
            <td>
              @if($study->doi)
                <a href="https://doi.org/{{ $study->doi }}" target="_blank" rel="noopener">{{ $study->doi }}</a>
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align:center; padding:2rem;">No studies yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
