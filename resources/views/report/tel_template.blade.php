<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Report</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-900 text-white p-6">
  <h1 class="text-2xl font-bold text-lime-400 mb-6">Phone OSINT Report</h1>

  <div class="section-profile">
    @php
    $profile = $data['profile'] ?? [];
    @endphp

    {{-- Profile Images --}}
    @if(!empty($profile['profileImages']))
    <div class="mb-6">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Profile Images</h2>
      <div class="flex flex-wrap gap-4 mt-2">
      @foreach($profile['profileImages'] as $img)
      <div class="w-32">
        <img src="{{ $img['value'] }}" alt="Profile Image"
        class="rounded shadow-md w-full h-auto border border-gray-700">
        @if(!empty($img['source']))
      <p class="text-xs text-gray-400 text-center mt-1">(Source: {{ $img['source'] }})</p>
      @endif
      </div>
    @endforeach
      </div>
    </div>
  @endif

    {{-- Full Names --}}
    @if(!empty($profile['fullNames']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold text-white border-b border-gray-600 pb-1">Full Names</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['fullNames'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Usernames --}}
    @if(!empty($profile['userNames']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Usernames</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['userNames'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Emails --}}
    @if(!empty($profile['emails']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Emails</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['emails'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Phones --}}
    @if(!empty($profile['phones']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Phone Numbers</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['phones'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Locations --}}
    @if(!empty($profile['locations']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Locations</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['locations'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Last Updated --}}
    @if(!empty($profile['lastUpdated']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Last Updated</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['lastUpdated'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Basic Info --}}
    @if(!empty($profile['basicInfo']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Basic Info</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['basicInfo'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Social Media Presence --}}
    @if(!empty($profile['socialMediaPresence']))
    <div class="mb-6">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Social Media Presence</h2>
      <ul class="grid grid-cols-2 md:grid-cols-3 bg-gray-800 gap-2 mt-2 text-sm">
      @foreach($profile['socialMediaPresence'] as $platform => $status)
      <li class="flex justify-between p-2 rounded">
      <span class="capitalize">{{ $platform }}</span>
      <span class="font-semibold {{ $status ? 'text-green-400' : 'text-red-400' }}">
      {{ is_bool($status) ? ($status ? 'Yes' : 'No') : $status }}
      </span>
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Bank Details --}}
    @if(!empty($profile['bankDetails']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Bank Details</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['bankDetails'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- UPI Details --}}
    @if(!empty($profile['upiDetails']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">UPI Details</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['upiDetails'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Number Is Activate --}}
    @if(isset($profile['numberIsActivate']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Number Is Activate</h2>
      <div class="mt-2">
      {{ $profile['numberIsActivate'] ? 'Yes' : 'No' }}
      </div>
    </div>
  @endif

    {{-- ID Proofs --}}
    @if(!empty($profile['idProofs']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">ID Proofs</h2>
      <ul class="mt-2 space-y-1">
      @foreach($profile['idProofs'] as $item)
      <li>
        {{ $item['value'] ?? 'N/A' }}
        @if(!empty($item['source']))
      <span class="text-xs text-gray-400">(Source: {{ $item['source'] }})</span>
      @endif
      </li>
    @endforeach
      </ul>
    </div>
  @endif

    {{-- Spam Status --}}
    @if(isset($profile['isSpam']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Is Spam</h2>
      <div class="mt-2">
      {{ $profile['isSpam'] ? 'Yes' : 'No' }}
      </div>
    </div>
  @endif

    {{-- Business Status --}}
    @if(isset($profile['isBusiness']))
    <div class="mb-4">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Is Business</h2>
      <div class="mt-2">
      {{ $profile['isBusiness'] ? 'Yes' : 'No' }}
      </div>
    </div>
  @endif
    @if(!empty($data['osintData']))
    <div class="mb-6">
      <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">OSINT Data</h2>

      <div class="mt-4">
      @foreach($data['osintData'] as $key => $value)
      <div class="bg-gray-800 p-4 rounded shadow mb-4">
        @if(is_array($value))
        <div class="space-y-1 text-gray-200">
        @foreach($value as $subKey => $subValue)
      <div>
        {{ $subKey }}: {{ is_array($subValue) ? implode(', ', $subValue) : $subValue }}
      </div>
      @endforeach
        </div>
      @else
      <div class="text-gray-300">{{ $value }}</div>
      @endif
      </div>
    @endforeach
      </div>
    </div>
  @endif
  </div>
</body>

</html>