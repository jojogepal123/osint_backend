<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RC Details Report</title>
  @include('report.partials.theme')
</head>
<body>

  @include('report.partials.header', ['reportType' => 'RC Full Details Report'])

  <div class="rpt-subject">
    <div class="rpt-subject-label">Vehicle Registration Number</div>
    <div class="rpt-subject-value">{{ strtoupper($data['rc_number'] ?? $data['id_number'] ?? 'N/A') }}</div>
  </div>

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

  @include('report.partials.footer')

</body>
</html>
