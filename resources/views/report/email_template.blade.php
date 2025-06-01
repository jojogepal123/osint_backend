<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-900 text-white p-6">
    <h1 class="text-2xl font-bold text-lime-400 mb-6">Email OSINT Report</h1>

    < class="section-profile">
        @php $profile = $data['profile'] ?? []; @endphp

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

        @if(!empty($data['breachData']))
            <div class="mb-6">
                <h2 class="text-lg font-semibold border-b border-gray-600 pb-1">Breach Data</h2>

                <div class="mt-4">
                    <div class="bg-gray-800 p-4 rounded shadow mb-4 flex items-center space-x-4">
                        @foreach($data['breachData'] as $key => $value)
                            @if(is_array($value))
                                {{-- Logo Image --}}

                                @if(!empty($value['LogoPath']))
                                    <img src="{{ $value['LogoPath'] }}" alt="Logo" class="w-16 h-16 object-contain rounded p-1 ">
                                @endif

                                {{-- Name Text --}}
                                <div class="text-white text-lg font-semibold">
                                    {{ $value['Name'] ?? 'Unknown' }}
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>
            </div>
        @endif

        @if(!empty($data['gravatar']))
            @foreach($data['gravatar'] as $item)
                @if($item['source'] === 'Gravatar' && $item['status'] === 'found')
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold border-b border-gray-600 pb-1 text-gray-200">
                            Gravatar Profile
                        </h2>

                        <div class="bg-gray-800 p-4 rounded shadow mb-4 flex items-start space-x-6 mt-4">

                            {{-- Avatar --}}
                            @if(!empty($item['avatar_url']))
                                <img src="{{ $item['avatar_url'] }}" alt="{{ $item['username'] ?? 'avatar' }}"
                                    class="object-cover w-24 h-24 rounded-xl border-2 border-gray-400" />
                            @else
                                <div
                                    class="w-24 h-24 flex items-center justify-center bg-gray-700 rounded-xl text-gray-400 text-sm border-2 border-gray-500">
                                    No Avatar
                                </div>
                            @endif

                            {{-- Username & Profile --}}
                            <div class="text-gray-200 space-y-2 break-words">
                                @if(!empty($item['username']))
                                    <p><strong>Username:</strong> {{ $item['username'] }}</p>
                                @endif

                                @if(!empty($item['profile_url']))
                                    <p>
                                        <strong>Profile:</strong>
                                        <a href="{{ $item['profile_url'] }}" class="text-blue-400 underline break-all" target="_blank"
                                            rel="noopener noreferrer">
                                            {{ $item['profile_url'] }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        </div>
</body>

</html>