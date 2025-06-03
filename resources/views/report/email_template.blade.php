<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report</title>
    <style>
        body {
            background-color: #1a202c;
            color: #fff;
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

        .section {
            margin-bottom: 24px;
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

        ul {
            margin-top: 8px;
            padding-left: 16px;
        }

        li {
            margin-bottom: 4px;
        }

        .osint-card, .gravatar-card, .breach-card {
            background-color: #2d3748;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .gravatar-avatar {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            border: 2px solid #9ca3af;
            object-fit: cover;
        }

        .avatar-placeholder {
            width: 96px;
            height: 96px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #374151;
            border-radius: 12px;
            color: #9ca3af;
            font-size: 12px;
            border: 2px solid #6b7280;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 8px;
            background-color: #2d3748;
            margin-top: 8px;
            padding: 8px;
        }

        .grid-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
        }

        .grid-item span.status-active {
            color: #4ade80;
            font-weight: bold;
        }

        .grid-item span.status-inactive {
            color: #f87171;
            font-weight: bold;
        }
    </style>
</head>

<body>
<h1>Email OSINT Report</h1>
<div class="section">
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

    @foreach(['fullNames' => 'Full Names', 'userNames' => 'Usernames', 'emails' => 'Emails', 'phones' => 'Phone Numbers', 'locations' => 'Locations', 'lastUpdated' => 'Last Updated', 'basicInfo' => 'Basic Info'] as $field => $label)
        @if(!empty($profile[$field]))
            <div class="section">
                <h2>{{ $label }}</h2>
                <ul>
                    @foreach($profile[$field] as $item)
                        <li>
                            {{ $item['value'] ?? 'N/A' }}
                            @if(!empty($item['source']))
                                <span class="source-label">(Source: {{ $item['source'] }})</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach

    @if(!empty($profile['socialMediaPresence']))
        <div class="section">
            <h2>Social Media Presence</h2>
            <ul class="grid">
                @foreach($profile['socialMediaPresence'] as $platform => $status)
                    <li class="grid-item">
                        <span class="capitalize">{{ $platform }}</span>
                        <span class="{{ $status ? 'status-active' : 'status-inactive' }}">
                            {{ is_bool($status) ? ($status ? 'Active' : 'Inactive') : 'Active' }}
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
                <div class="osint-card">
                    @if(is_array($value))
                        @foreach($value as $subKey => $subValue)
                            <div>{{ $subKey }}: {{ is_array($subValue) ? implode(', ', $subValue) : $subValue }}</div>
                        @endforeach
                    @else
                        <div>{{ $value }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($data['breachData']))
        <div class="section">
            <h2>Breach Data</h2>
            <div class="breach-card">
                @foreach($data['breachData'] as $key => $value)
                    @if(is_array($value))
                        <div style="margin-bottom: 10px;">
                            @if(!empty($value['LogoBase64']))
                                <img src="{{ $value['LogoBase64'] }}" alt="Logo"
                                     style="width: 24px; height: 24px; border-radius: 50%; vertical-align: middle; display: inline-block; margin-right: 10px;" />
                            @endif
                            <span style="font-size: 14px; font-weight: bold; color: #fff; vertical-align: middle; display: inline-block;">
                                {{ $value['Name'] ?? 'Unknown' }}
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($data['gravatar']))
        @foreach($data['gravatar'] as $item)
            @if($item['source'] === 'Gravatar' && $item['status'] === 'found')
                <div class="section">
                    <h2>Gravatar Profile</h2>
                    <div class="gravatar-card" style="display: flex; align-items: flex-start; gap: 24px;">
                        @if(!empty($item['avatar_url']))
                            <img src="{{ $item['avatar_url'] }}" alt="{{ $item['username'] ?? 'avatar' }}" class="gravatar-avatar">
                        @else
                            <div class="avatar-placeholder">No Avatar</div>
                        @endif

                        <div style="color: #e5e7eb;">
                            @if(!empty($item['username']))
                                <p><strong>Username:</strong> {{ $item['username'] }}</p>
                            @endif
                            @if(!empty($item['profile_url']))
                                <p><strong>Profile:</strong> <a href="{{ $item['profile_url'] }}" style="color: #60a5fa; text-decoration: underline; word-break: break-all;">
                                    {{ $item['profile_url'] }}</a></p>
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
