<div class="rpt-footer">
  <div class="rpt-footer-tbl">
    <div class="rpt-footer-left">
      <span class="rpt-footer-brand">{{ config('app.name', 'DRASHTA') }}</span> &nbsp;&middot;&nbsp; Confidential Intelligence Report
    </div>
    <div class="rpt-footer-right">
      Downloaded by: {{ $userEmail }} &nbsp;&middot;&nbsp; {{ now()->format('d M Y H:i:s') }}
    </div>
  </div>
</div>
