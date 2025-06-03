<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Report</title>
  <style>
    body {
      background-color: #1a202c;
      color: #ffffff;
      padding: 24px;
      font-family: Arial, sans-serif;
    }

    h1 {
      font-size: 24px;
      font-weight: bold;
      color: #a3e635;
      margin-bottom: 24px;
    }

    h2 {
      font-size: 18px;
      font-weight: bold;
      border-bottom: 1px solid #4b5563;
      padding-bottom: 4px;
      margin-bottom: 16px;
    }

    ul {
      padding-left: 16px;
    }

    li {
      margin-bottom: 4px;
    }

    .section {
      margin-bottom: 24px;
    }

    .text-xs {
      font-size: 12px;
    }

    .text-sm {
      font-size: 14px;
    }

    .text-center {
      text-align: center;
    }

    .text-gray {
      color: #9ca3af;
    }

    .text-green {
      color: #4ade80;
    }

    .text-red {
      color: #f87171;
    }

    .font-semibold {
      font-weight: 600;
    }

    .rounded {
      border-radius: 6px;
    }

    .border {
      border: 1px solid #4b5563;
    }

    .shadow {
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .bg-dark {
      background-color: #2d3748;
    }

    .profile-images {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-top: 8px;
    }

    .image-box {
      width: 128px;
    }

    .image-box img {
      width: 100%;
      height: auto;
      border: 1px solid #4b5563;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .source-label {
      font-size: 12px;
      color: #9ca3af;
      text-align: center;
      margin-top: 4px;
    }
  </style>
</head>

<body>
  <h1>Phone OSINT Report</h1>

  <div class="section-profile">
    @php $profile = $data['profile'] ?? []; @endphp

    @if(!empty($profile['profileImages']))
    <div class="section">
      <h2>Profile Images</h2>
      <div class="profile-images">
        @foreach($profile['profileImages'] as $img)
        <div class="image-box">
          <img src="{{ $img['value'] }}" alt="Profile Image">
          @if(!empty($img['source']))
          <p class="source-label">(Source: {{ $img['source'] }})</p>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @endif

    @foreach ([
      'fullNames' => 'Full Names',
      'userNames' => 'Usernames',
      'emails' => 'Emails',
      'phones' => 'Phone Numbers',
      'locations' => 'Locations',
      'lastUpdated' => 'Last Updated',
      'basicInfo' => 'Basic Info',
      'bankDetails' => 'Bank Details',
      'upiDetails' => 'UPI Details',
      'idProofs' => 'ID Proofs'
    ] as $field => $title)
      @if(!empty($profile[$field]))
      <div class="section">
        <h2>{{ $title }}</h2>
        <ul>
          @foreach($profile[$field] as $item)
          <li>
            {{ $item['value'] ?? 'N/A' }}
            @if(!empty($item['source']))
            <span class="text-xs text-gray">(Source: {{ $item['source'] }})</span>
            @endif
          </li>
          @endforeach
        </ul>
      </div>
      @endif
    @endforeach

    @foreach ([
      'numberIsActivate' => 'Number Is Activate',
      'isSpam' => 'Is Spam',
      'isBusiness' => 'Is Business'
    ] as $field => $title)
      @if(isset($profile[$field]))
      <div class="section">
        <h2>{{ $title }}</h2>
        <div>
          {{ $profile[$field] ? 'Yes' : 'No' }}
        </div>
      </div>
      @endif
    @endforeach

    @if(!empty($profile['socialMediaPresence']))
    <div class="section">
      <h2>Social Media Presence</h2>
      <ul>
        @foreach($profile['socialMediaPresence'] as $platform => $status)
        <li class="font-semibold">
          <span class="capitalize">{{ $platform }}:</span>
          <span class="{{ $status ? 'text-green' : 'text-red' }}">
            {{ is_bool($status) ? ($status ? 'Yes' : 'No') : $status }}
          </span>
        </li>
        @endforeach
      </ul>
    </div>
    @endif

    @if(!empty($data['osintData']))
    <div class="section">
      <h2>OSINT Data</h2>
      @foreach($data['osintData'] as $key => $value)
      <div class="bg-dark p-3 rounded shadow section">
        @if(is_array($value))
        @foreach($value as $subKey => $subValue)
        <div>
          {{ $subKey }}: {{ is_array($subValue) ? implode(', ', $subValue) : $subValue }}
        </div>
        @endforeach
        @else
        <div>{{ $value }}</div>
        @endif
      </div>
      @endforeach
    </div>
    @endif

  </div>
</body>

</html>
