<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification Report</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

    .header { background: #0f3460; color: #fff; padding: 18px 24px 14px; }
    .header-top { display: table; width: 100%; }
    .header-brand { display: table-cell; vertical-align: middle; }
    .app-name { font-size: 22px; font-weight: bold; letter-spacing: 3px; color: #a8ff78; }
    .app-tagline { font-size: 9px; color: #b0c4de; letter-spacing: 1px; margin-top: 2px; }
    .header-meta { display: table-cell; vertical-align: middle; text-align: right; }
    .report-type { font-size: 13px; font-weight: bold; color: #a8ff78; }
    .report-date { font-size: 9px; color: #b0c4de; margin-top: 2px; }

    .accent-bar { height: 4px; background: #a8ff78; }

    .subject { background: #eaf4fb; border-left: 5px solid #0f3460; padding: 10px 20px; margin: 16px 24px; }
    .subject-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
    .subject-value { font-size: 16px; font-weight: bold; color: #0f3460; margin-top: 2px; }

    .verified-badge {
      display: inline-block;
      background: #1a7a1a;
      color: #fff;
      font-size: 9px;
      font-weight: bold;
      padding: 2px 8px;
      border-radius: 3px;
      letter-spacing: 1px;
      margin-top: 6px;
    }

    .section { margin: 0 24px 18px; }
    .section-title {
      font-size: 10px; font-weight: bold; text-transform: uppercase;
      letter-spacing: 1.5px; color: #0f3460;
      border-bottom: 2px solid #a8ff78; padding-bottom: 4px; margin-bottom: 8px;
    }

    table { width: 100%; border-collapse: collapse; }
    tr:nth-child(odd)  td { background: #f8faff; }
    tr:nth-child(even) td { background: #fff; }
    td { padding: 7px 12px; border-bottom: 1px solid #e8eaf0; vertical-align: top; }
    .td-key { width: 40%; font-weight: bold; color: #0f3460; font-size: 11px; }
    .td-val { color: #333; font-size: 11px; }
    .badge-yes { color: #1a7a1a; font-weight: bold; }
    .badge-no  { color: #c0392b; font-weight: bold; }

    .nested-head {
      background: #e8f0fe;
      font-weight: bold;
      font-size: 10px;
      color: #0f3460;
      padding: 5px 12px;
      border-bottom: 1px solid #d0dce8;
    }
    .nested-key { padding-left: 24px; color: #555; font-size: 10px; }

    .footer { margin-top: 24px; padding: 12px 24px; background: #0f3460; color: #b0c4de; font-size: 9px; }
    .footer-table { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-brand { color: #a8ff78; font-weight: bold; font-size: 11px; }
  </style>
</head>
<body>

  <div class="header">
    <div class="header-top">
      <div class="header-brand">
        <div class="app-name">{{ config('app.name', 'DRASHTA') }}</div>
        <div class="app-tagline">INTELLIGENCE PLATFORM</div>
      </div>
      <div class="header-meta">
        <div class="report-type">{{ $title ?? 'Verification' }} Report</div>
        <div class="report-date">Generated: {{ now()->format('d M Y, H:i') }}</div>
      </div>
    </div>
  </div>
  <div class="accent-bar"></div>

  <div class="subject">
    <div class="subject-label">Search Query</div>
    <div class="subject-value">{{ strtoupper($searchInput ?? 'N/A') }}</div>
    <div class="verified-badge">&#10003; VERIFIED</div>
  </div>

  <div class="section">
    <div class="section-title">Verification Details</div>
    <table>
      {{-- Scalar fields first --}}
      @foreach ($data as $key => $value)
        @if (!is_array($value) && !is_null($value) && $value !== '' && strtolower((string)$value) !== 'n/a')
          <tr>
            <td class="td-key">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
            <td class="td-val">
              @php $v = strtolower((string)$value); @endphp
              @if ($v === 'true' || $v === '1' || $v === 'yes')
                <span class="badge-yes">&#10003; Yes</span>
              @elseif ($v === 'false' || $v === '0' || $v === 'no')
                <span class="badge-no">&#10007; No</span>
              @else
                {{ $value }}
              @endif
            </td>
          </tr>
        @endif
      @endforeach

      {{-- Nested arrays --}}
      @foreach ($data as $key => $value)
        @if (is_array($value) && count($value) > 0)
          <tr>
            <td colspan="2" class="nested-head">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
          </tr>
          @foreach ($value as $subKey => $subVal)
            @if (!is_array($subVal) && !is_null($subVal) && $subVal !== '')
              <tr>
                <td class="td-key nested-key">&rsaquo; {{ ucwords(str_replace('_', ' ', $subKey)) }}</td>
                <td class="td-val">{{ $subVal }}</td>
              </tr>
            @endif
          @endforeach
        @endif
      @endforeach
    </table>
  </div>

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
