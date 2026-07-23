<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>UPI Intelligence Report</title>
  @include('report.partials.theme')
</head>
<body>

  @include('report.partials.header', ['reportType' => 'UPI Intelligence Report'])

  @if(!empty($data['upi_id']))
  <div class="rpt-subject">
    <div class="rpt-subject-label">Target UPI ID</div>
    <div class="rpt-subject-value">{{ $data['upi_id'] }}</div>
  </div>
  @endif

  <div class="section">
    <div class="section-title">UPI Details</div>
    <table>
      @foreach ($data as $key => $value)
        @if ($value !== null && $value !== '' && strtolower((string)$value) !== 'n/a')
          <tr>
            <td class="td-key">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
            <td class="td-val">
              @if ($value === true)
                <span class="badge-yes">Yes</span>
              @elseif ($value === false)
                <span class="badge-no">No</span>
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
