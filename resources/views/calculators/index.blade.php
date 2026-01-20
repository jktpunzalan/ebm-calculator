@extends('layouts.app')

@section('title_prefix', 'Calculators')
@section('title', 'Evidence-Based Calculators')

@push('styles')
<style>
    .page-shell { display:flex; flex-direction:column; gap:20px; }
    .intro-card { background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:26px 30px; box-shadow:0 18px 40px -32px rgba(15,23,42,0.45); }
    .intro-card p { margin:.5rem 0 0; color:#475569; line-height:1.6; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap:18px; }
    .calc-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px 22px; display:flex; flex-direction:column; gap:12px; box-shadow:0 16px 32px -28px rgba(15,23,42,0.42); }
    .calc-card h3 { margin:0; font-size:1.2rem; color:#1e293b; }
    .calc-card p { margin:0; color:#64748b; line-height:1.55; }
    .calc-card a { align-self:flex-start; padding:.55rem .95rem; border-radius:10px; text-decoration:none; font-weight:600; display:inline-flex; gap:6px; align-items:center; }
    .calc-card.ready a { background:#2563eb; color:#fff; border:1px solid #2563eb; }
    .calc-card.pending a { background:#f8fafc; color:#1e293b; border:1px solid #cbd5f5; }
    .calc-card.pending a:hover { background:#e2e8f0; }
    .badge { display:inline-flex; align-items:center; padding:4px 8px; border-radius:999px; font-size:.78rem; font-weight:600; border:1px solid currentColor; }
    .badge.ready { background:rgba(34,197,94,0.15); color:#15803d; border-color:rgba(34,197,94,0.32); }
    .badge.pending { background:rgba(250,204,21,0.16); color:#ca8a04; border-color:rgba(250,204,21,0.32); }
</style>
@endpush

@section('content')
<div class="page-shell">
    <div class="intro-card">
        <h1>Evidence-Based Medicine Calculators</h1>
        <p>Select a calculator to evaluate literature using EBM principles. Diagnostics and Prognosis calculators are currently being built—check back soon.</p>
    </div>

    <div class="grid">
        <div class="calc-card ready">
            <div class="badge ready">Available</div>
            <h3>Therapy / Harm</h3>
            <p>Estimate risk ratios, ARR, and NNT from therapeutic or harm-based studies. Includes schema checks and study management.</p>
            <a href="{{ route('therapy.article.form') }}">Open Therapy Calculator →</a>
        </div>
        <div class="calc-card pending">
            <div class="badge pending">Under Construction</div>
            <h3>Diagnostics</h3>
            <p>Soon you’ll be able to calculate sensitivity, specificity, likelihood ratios, and post-test probabilities.</p>
            <a href="{{ route('calculators.diagnostics') }}">Preview Diagnostics →</a>
        </div>
        <div class="calc-card pending">
            <div <style code truncated>
