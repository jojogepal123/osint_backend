<div class="rpt-header">
  <div class="rpt-header-top">
    <div class="rpt-brand">
      <div class="rpt-app-name">{{ config('app.name', 'DRASHTA') }}</div>
      <div class="rpt-tagline">INTELLIGENCE PLATFORM</div>
    </div>
    <div class="rpt-meta">
      <div class="rpt-type">{{ $reportType ?? 'Intelligence Report' }}</div>
      <div class="rpt-date">Generated: {{ now()->format('d M Y, H:i') }}</div>
    </div>
  </div>
</div>
<div class="rpt-accent-bar"></div>
