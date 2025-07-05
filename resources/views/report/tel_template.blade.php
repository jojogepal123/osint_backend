<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phone OSINT Report</title>
    <style>
        body { background-color: #1a202c; color: #ffffff; padding: 24px; font-family: Arial, sans-serif; }
        h1 { font-size: 24px; font-weight: bold; color: #a3e635; margin-bottom: 24px; }
        h2 { font-size: 18px; font-weight: bold; border-bottom: 1px solid #4b5563; padding-bottom: 4px; margin-bottom: 16px; }
        ul { padding-left: 16px; }
        li { margin-bottom: 4px; }
        .section { margin-bottom: 24px; }
        .text-xs { font-size: 12px; }
        .text-gray { color: #9ca3af; }
        .text-green { color: #4ade80; }
        .text-red { color: #f87171; }
        .font-semibold { font-weight: 600; }
        .osint-card {
            background-color: #2d3748;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .profile-images {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .image-box {
            width: 120px;
            text-align: center;
            margin-right: 16px;
            margin-bottom: 16px;
        }

        .image-box img {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #4b5563;
        }

        .source-label {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<h1>Phone OSINT Report</h1>

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
{{-- Dynamic Fields --}}
@foreach([
    'fullNames' => 'Full Names',
    'userNames' => 'Usernames',
    'emails' => 'Emails',
    'phones' => 'Phone Numbers',
    'locations' => 'Locations',
    'verifiedAddress' => 'Verified Address',
    'lastUpdated' => 'Phone status',
    'basicInfo' => 'Basic Info',
    'bankDetails' => 'Bank Details',
    'upiDetails' => 'UPI Details',
    'idProofs' => 'ID Proofs',
    'rcNumber' => 'RC Number'
] as $field => $label)
        @if(!empty($profile[$field]))
            <div class="section">
                <h2>{{ $label }}</h2>
                <ul>
                    @foreach($profile[$field] as $item)
                        @php
            // Normalize value & source
            $value = 'N/A';
            $source = null;

            if (is_array($item)) {
                $value = is_string($item['value'] ?? null) ? $item['value'] : 'N/A';
                $source = is_string($item['source'] ?? null) ? $item['source'] : null;
            } elseif (is_string($item)) {
                $value = $item;
            }
                        @endphp
                        <li>
                            {{ $value }}
                            @if ($source)
                                <span class="text-xs text-gray">(Source: {{ $source }})</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
@endforeach

{{-- Boolean Fields --}}
@foreach([
    'numberIsActivate' => 'Number Is Activate',
    'isSpam' => 'Is Spam',
    'isBusiness' => 'Is Business'
] as $key => $label)
    @if(isset($profile[$key]))
        <div class="section">
            <h2>{{ $label }}</h2>
            <div>
                <span class="{{ $profile[$key] ? 'text-green' : 'text-red' }}">
                    {{ $profile[$key] ? 'Yes' : 'No' }}
                </span>
            </div>
        </div>
    @endif
@endforeach


{{-- Social Media --}}
@if (!empty($profile['socialMediaPresence']) && is_array($profile['socialMediaPresence']))
    <div class="section">
        <h2>Social Media Presence</h2>
        <ul>
            @foreach ($profile['socialMediaPresence'] as $platform => $status)
                <li class="font-semibold">
                    <span class="capitalize">{{ $platform }}:</span>
                    <span class="{{ $status ? 'text-green' : 'text-red' }}">
                        {{ is_bool($status) ? ($status ? 'Yes' : 'No') : (empty($status) ? 'No' : 'Yes') }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Carriers --}}
@if(!empty($profile['carriers']))
    <div class="section">
        <h2>Carrier Information</h2>
        <ul>
            @foreach($profile['carriers'] as $carrier)
                <li>
                    @php
        $carrierVal = is_array($carrier) ? ($carrier['value'] ?? '') : '';
        $carrierSrc = is_array($carrier) ? ($carrier['source'] ?? '') : '';
        $carrierKey = is_array($carrier) ? ($carrier['key'] ?? '') : '';
                    @endphp
                    {{ $carrierKey }}: {{ $carrierVal }}
                    @if($carrierSrc)
                        <span class="text-xs text-gray">(Source: {{ $carrierSrc }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Country Codes --}}
@if(!empty($profile['countryCodes']))
    <div class="section">
        <h2>Country Codes</h2>
        <ul>
            @foreach($profile['countryCodes'] as $code)
                @php
        $codeVal = is_array($code) ? ($code['value'] ?? 'N/A') : 'N/A';
        $codeSrc = is_array($code) ? ($code['source'] ?? null) : null;
                @endphp
                <li>
                    {{ $codeVal }}
                    @if($codeSrc)
                        <span class="text-xs text-gray">(Source: {{ $codeSrc }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
{{-- OSINT Data --}}
@if(!empty($data['osintData']))
  <div class="section">
    <h2>OSINT Data</h2>
    @foreach($data['osintData'] as $key => $value)
    <div class="osint-card">
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
</body>
</html>

