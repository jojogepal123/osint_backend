<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Challan Report</title>
  @include('report.partials.theme')
  <style>
    .challan-card { border: 1px solid #d0dce8; border-radius: 4px; margin-bottom: 14px; overflow: hidden; }
    .challan-card-head { background: #0f3460; color: #a8ff78; font-weight: bold; font-size: 11px; padding: 7px 12px; }
  </style>
</head>
<body>

  @include('report.partials.header', ['reportType' => 'RC Challan Details Report'])

  <div class="rpt-subject">
    <div class="rpt-subject-label">Vehicle Registration Number</div>
    <div class="rpt-subject-value">{{ strtoupper($data['rc_number'] ?? 'N/A') }}</div>
  </div>

  <div class="section">
    <div class="section-title">Challan Records</div>

    @if (!empty($data['challan_details']) && is_array($data['challan_details']))
      @foreach ($data['challan_details'] as $index => $challan)
        <div class="challan-card">
          <div class="challan-card-head">Challan #{{ $index + 1 }}
            @if (!empty($challan['challan_number'])) &nbsp;&mdash;&nbsp; {{ $challan['challan_number'] }} @endif
          </div>
          <table>
            @foreach ($challan as $key => $value)
              @if (!is_array($value) && !is_null($value) && $value !== '' && strtolower((string)$value) !== 'n/a')
                <tr>
                  <td class="td-key">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                  <td class="td-val">{{ $value }}</td>
                </tr>
              @endif
              @if (is_array($value) && count($value) > 0)
                @foreach ($value as $subKey => $subVal)
                  @if (!is_array($subVal) && !is_null($subVal) && $subVal !== '')
                    <tr>
                      <td class="td-key" style="padding-left:20px; color:#555;">
                        &rsaquo; {{ ucwords(str_replace('_', ' ', $subKey)) }}
                      </td>
                      <td class="td-val">{{ $subVal }}</td>
                    </tr>
                  @endif
                @endforeach
              @endif
            @endforeach
          </table>
        </div>
      @endforeach
    @else
      <p class="no-data">No challan records found for this vehicle.</p>
    @endif
  </div>

  @include('report.partials.footer')

</body>
</html>
