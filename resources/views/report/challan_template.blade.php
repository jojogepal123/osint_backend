<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Challan Report</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 14px;
      color: #333;
    }

    h2 {
      color: green;
      margin-bottom: 10px;
    }

    .section {
      margin-bottom: 20px;
    }

    .label {
      font-weight: bold;
    }

    .value {
      margin-bottom: 10px;
    }

    .challan {
      margin-left: 20px;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
    }

    th,
    td {
      border: 1px solid #888;
      padding: 6px;
      text-align: left;
    }
  </style>
</head>

<body>

  <h2>Challan Details: {{ $data['rc_number'] ?? '-' }}</h2>

  <div class="section">
    <div class="label">Rc Number:</div>
    <div class="value">{{ $data['rc_number'] ?? '-' }}</div>
  </div>

  <div class="section">
    <div class="label">Challan Details:</div>
    @if (!empty($data['challan_details']) && is_array($data['challan_details']))
      @foreach ($data['challan_details'] as $challan)
      <div class="challan-entry" style="margin-bottom: 20px; padding: 10px;">
        @foreach ($challan as $key => $value)
        @if (is_array($value))
        <div style="margin-bottom: 10px;">
        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
        <ul style="margin-left: 15px;">
        @foreach ($value as $item)
        <li>
        @if (is_array($item))
        @foreach ($item as $subKey => $subValue)
        <div>
        <strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong> {{ $subValue }}
        </div>
        @endforeach
        @else
        {{ $item }}
        @endif
        </li>
        @endforeach
        </ul>
        </div>
      @else
        <div style="margin-bottom: 5px;">
        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}
        </div>
      @endif
      @endforeach
      </div>
      <hr>
      @endforeach
  @else
    <div class="value">No challan details available.</div>
  @endif
  </div>

</body>

</html>