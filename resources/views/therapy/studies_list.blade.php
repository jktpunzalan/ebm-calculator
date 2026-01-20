@extends('layouts.app')

@section('title', 'Studies — EBM')

@push('styles')
<style>
  :root { --pad: 16px; }
  html, body { margin:0; padding:0; }
  body {
    font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    background:#fcfcfc; color:#111;
  }
  .wrap { max-width: 1320px; margin: 0 auto; padding: 20px var(--pad); }
  .btn { padding:.55rem .85rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-block; }
  .btn.muted { background:#f4f4f4; color:#222; border-color:#999; }
  .search { display:flex; gap:8px; flex-wrap: wrap; width:100%; max-width: 520px; }
  .search input[type="text"] { flex:1; min-width: 200px; padding:.55rem .65rem; border:1px solid #ccc; border-radius:8px; }
  .card { background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding: 12px; margin-top:12px; }
  .table-wrap { width:100%; overflow-x:auto; }
  table { width:100%; border-collapse: collapse; table-layout: auto; }
  thead th { position: sticky; top: 0; z-index: 1; }
  th, td {
    border:1px solid #ececec; padding:.55rem .6rem; text-align:left; vertical-align: top;
    white-space: normal; word-break: break-word;
  }
  th { background:#fafafa; font-weight:600; }
  td a { color:#0d6efd; text-decoration:none; }
  td a:hover { text-decoration:underline; }
  .danger-link { background:none;border:none;color:#b00020;padding:0;cursor:pointer;text-decoration:underline; }
  .badge { display:inline-block; padding:2px 6px; border-radius:4px; font-size:.75rem; border:1px solid; }
  .badge.valid { color:#0f5132; background:#e7f5ed; border-color:#a7e0c4; }
  .badge.warn { color:#7a3e00; background:#fff4e5; border-color:#ffd8a8; }
  .num { text-align: right; font-variant-numeric: tabular-nums; }
  .toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
  .banner { padding:10px 12px; border-radius:8px; margin:10px 0; }
  .banner.success { background:#e8f5e9; color:#1b5e20; border:1px solid #a5d6a7; }
  .banner.error { background:#fdecea; color:#b00020; border:1px solid #f5c2c7; }
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="toolbar">
    <div>
      <h1 style="margin:0; font-size:1.8rem;">Studies</h1>
      <p style="margin:4px 0 0; color:#666;">{{ count($studies) }} studies found</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <a href="{{ route('therapy.article.form') }}" class="btn">➕ New Study</a>
      <a href="{{ route('home') }}" class="btn muted">🏠 Home</a>
    </div>
  </div>

  @if(session('success'))
    <div class="banner success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="banner error">{{ session('error') }}</div>
  @endif

  <form method="get" class="search">
    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search by Exposure or Outcome...">
    <button type="submit" class="btn">Search</button>
    @if($searchQuery)
      <a href="{{ route('therapy.studies.list') }}" class="btn muted">Clear</a>
    @endif
  </form>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Exposure</th>
            <th>Comparator</th>
            <th>Outcome</th>
            <th class="num">RR</th>
            <th class="num">ARR</th>
            <th class="num">NNT</th>
            <th class="num">NNH</th>
            <th>Individualizations</th>
            <th>Validity</th>
            <th>DOI</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($studies as $study)
            <tr>
              <td style="text-align:center;">{{ $study->id }}</td>
              <td>{{ $study->exposure_pico ?: '—' }}</td>
              <td>{{ $study->comparator_pico ?: '—' }}</td>
              <td>{{ $study->outcome_pico ?: '—' }}</td>
              <td class="num">{{ $study->rr !== null ? number_format($study->rr, 3) : '—' }}</td>
              <td class="num">{{ $study->arr !== null ? number_format($study->arr, 3) : '—' }}</td>
              <td class="num">{{ $study->nnt !== null ? number_format($study->nnt, 0) : '—' }}</td>
              <td class="num">{{ $study->nnh !== null ? number_format($study->nnh, 0) : '—' }}</td>
              <td>
                @if($study->ind_count > 0)
                  {{ $study->ind_count }} saved
                  @if($study->last_ind_created)
                    <br><small class="muted">Last: {{ \Carbon\Carbon::parse($study->last_ind_created)->diffForHumans() }}</small>
                  @endif
                @else
                  <span class="muted">None yet</span>
                @endif
              </td>
              <td style="text-align:center;">
                @php
                  $validCount = 0;
                  if ($study->valid_rand) $validCount++;
                  if ($study->valid_conceal) $validCount++;
                  if ($study->valid_blind) $validCount++;
                  if ($study->valid_itt) $validCount++;
                  if ($study->valid_follow) $validCount++;
                  $badgeClass = $validCount >= 3 ? 'valid' : 'warn';
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $validCount }}/5</span>
              </td>
              <td style="max-width:200px; word-break:break-all;">
                @if($study->doi)
                  <a href="https://doi.org/{{ $study->doi }}" target="_blank" rel="noopener">{{ $study->doi }}</a>
                @else
                  —
                @endif
              </td>
              <td style="white-space:nowrap;">
                <a href="{{ route('therapy.ind.list', $study->id) }}" class="btn" style="font-size:.8rem;padding:.4rem .6rem;">Individualize</a>
                <form method="post" action="{{ route('therapy.study.delete', $study->id) }}" style="display:inline;" onsubmit="return confirm('Delete this study?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="danger-link">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" style="text-align:center; padding:2rem;">
                No studies found. <a href="{{ route('therapy.article.form') }}">Create your first study</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
