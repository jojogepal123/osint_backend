<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RC Details Report</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

    /* ── Header ── */
    .header {
      background: #0f3460;
      color: #fff;
      padding: 18px 24px 14px;
    }
    .header-top {
      display: table;
      width: 100%;
    }
    .header-brand {
      display: table-cell;
      vertical-align: middle;
    }
    .app-name {
      font-size: 22px;
      font-weight: bold;
      letter-spacing: 3px;
      color: #a8ff78;
    }
    .app-tagline {
      font-size: 9px;
      color: #b0c4de;
      letter-spacing: 1px;
      margin-top: 2px;
    }
    .header-meta {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
    }
    .report-type {
      font-size: 13px;
      font-weight: bold;
      color: #a8ff78;
    }
    .report-date {
      font-size: 9px;
      color: #b0c4de;
      margin-top: 2px;
    }

    /* ── Accent bar ── */
    .accent-bar { height: 4px; background: #a8ff78; }

    /* ── Subject banner ── */
    .subject {
      background: #eaf4fb;
      border-left: 5px solid #0f3460;
      padding: 10px 20px;
      margin: 16px 24px;
    }
    .subject-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
    .subject-value { font-size: 16px; font-weight: bold; color: #0f3460; margin-top: 2px; }

    /* ── Section ── */
    .section { margin: 0 24px 18px; }
    .section-title {
      font-size: 10px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #0f3460;
      border-bottom: 2px solid #a8ff78;
      padding-bottom: 4px;
      margin-bottom: 8px;
    }

    /* ── Data table ── */
    table { width: 100%; border-collapse: collapse; }
    tr:nth-child(odd)  td { background: #f8faff; }
    tr:nth-child(even) td { background: #fff; }
    td {
      padding: 7px 12px;
      border-bottom: 1px solid #e8eaf0;
      vertical-align: top;
    }
    .td-key {
      width: 40%;
      font-weight: bold;
      color: #0f3460;
      font-size: 11px;
    }
    .td-val {
      color: #333;
      font-size: 11px;
    }
    .badge-yes { color: #1a7a1a; font-weight: bold; }
    .badge-no  { color: #c0392b; font-weight: bold; }

    /* ── Footer ── */
    .footer {
      margin-top: 24px;
      padding: 12px 24px;
      background: #0f3460;
      color: #b0c4de;
      font-size: 9px;
    }
    .footer-table { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-brand { color: #a8ff78; font-weight: bold; font-size: 11px; }
  </style>
</head>
<body>

  {{-- ── Header ── --}}
  <div class="header">
    <div class="header-top">
      <div class="header-brand">
        <div class="app-name">{{ config('app.name', 'DRASHTA') }}</div>
        <div class="app-tagline">INTELLIGENCE PLATFORM</div>
      </div>
      <div class="header-meta">
        <div class="report-type">RC Full Details Report</div>
        <div class="report-date">Generated: {{ now()->format('d M Y, H:i') }}</div>
      </div>
    </div>
  </div>
  <div class="accent-bar"></div>

  {{-- ── Subject ── --}}
  <div class="subject">
    <div class="subject-label">Vehicle Registration Number</div>
    <div class="subject-value">{{ strtoupper($data['rc_number'] ?? $data['id_number'] ?? 'N/A') }}</div>
  </div>

  {{-- ── Data ── --}}
  <div class="section">
    <div class="section-title">Vehicle Information</div>
    <table>
      @foreach ($data as $key => $value)
        @if (!is_array($value) && !is_null($value) && $value !== '' && strtolower((string)$value) !== 'n/a' && !in_array(strtolower($key), ['client_id','clientid']))
          <tr>
            <td class="td-key">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
            <td class="td-val">
              @if ($value === true || $value === 'true' || $value === 1)
                <span class="badge-yes">&#10003; Yes</span>
              @elseif ($value === false || $value === 'false' || $value === 0)
                <span class="badge-no">&#10007; No</span>
              @else
                {{ $value }}
              @endif
            </td>
          </tr>
        @endif
      @endforeach
    </table>
  </div>

  {{-- ── Footer ── --}}
  <div class="footer">
    <div class="footer-table">
      <div class="footer-left">
        <span class="footer-brand">{{ config('app.name', 'DRASHTA') }}</span> &nbsp;&middot;&nbsp; Confidential Intelligence Report
      </div>
      <div class="footer-right">
        Downloaded by: {{ $userEmail }} &nbsp;&middot;&nbsp; {{ now()->format('d M Y H:i:s') }}
      </div>
    </div>
  </div>

</body>
</html>
