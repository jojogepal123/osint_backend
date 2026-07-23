<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification Report</title>
  @include('report.partials.theme')
  <style>
    .verified-badge { display: inline-block; background: #1a7a1a; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 8px; border-radius: 3px; margin-top: 6px; letter-spacing: 1px; }
    .nested-head { background: #0f3460; color: #fff; font-weight: bold; font-size: 10px; padding: 5px 12px; }
    .nested-key { padding-left: 24px; color: #555; font-size: 10px; }
  </style>
</head>
<body>

  @include('report.partials.header', ['reportType' => ($title ?? 'Verification') . ' Report'])

  <div class="rpt-subject">
    <div class="rpt-subject-label">Search Query</div>
    <div class="rpt-subject-value">{{ strtoupper($searchInput ?? 'N/A') }}</div>
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

  @include('report.partials.footer')

</body>
</html>
