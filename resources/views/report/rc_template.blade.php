<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>RC Details Report</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .field {
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between;
      border-bottom: 1px dashed #ccc;
      padding: 4px 0;
    }

    .label {
      font-weight: bold;
    }

    .yes {
      color: green;
    }

    .no {
      color: red;
    }
  </style>
</head>

<body>
  <h2>{{ $data['rc_number'] ?? '' }} Report</h2>

  @foreach ($data as $key => $value)
    @if (!is_null($value) && strtolower($value) !== 'n/a')
    <div class="field">
    <div class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
    <div>
      @if ($value === true)
      <span class="yes">Yes</span>
    @elseif ($value === false)
      <span class="no">No</span>
    @else
      {{ $value }}
    @endif
    </div>
    </div>
    @endif
  @endforeach

</body>

</html>