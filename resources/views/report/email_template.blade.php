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
            background-color:  #1a202c;
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
<div style="margin-bottom: 24px;">
    <h2 style=" margin-bottom: 8px;">CONFIDENTIAL – FOR AUTHORIZED LAW ENFORCEMENT PERSONNEL ONLY</h2>
    <p style="font-size: 14px; color: #e5e7eb; line-height: 1.6;">
        This report is intended strictly for legitimate investigative use by authorized law enforcement officers, in
        accordance with applicable Indian laws and regulations. It contains intelligence derived solely from publicly
        accessible sources and licensed investigative tools. No unauthorized, leaked, or unlawfully obtained data is
        included.
    </p>
    <p style="font-size: 14px; color: #e5e7eb;">
        Distribution of this report through unauthorized channels—including but not limited to WhatsApp, Telegram, email
        groups, or other social media platforms—is strictly prohibited.
    </p>
    <p style="font-size: 14px; color: #e5e7eb;">
        All information contained herein must be handled with utmost confidentiality and used in full compliance with
        applicable legal frameworks, including the <strong>Information Technology Act, 2000</strong>, and the
        <strong>Digital Personal Data Protection Act, 2023</strong> (upon its enforcement).
    </p>
    <p style="font-size: 14px; color: #e5e7eb;">
        Law enforcement personnel are solely responsible for ensuring that any use of this information is supported by
        appropriate legal authorization or explicit consent from the data subject, as required.
    </p>
    <p style="font-size: 14px; color: #e5e7eb;">
        <strong>OSINTWORK</strong> operates solely as a technical intermediary and does not store or retain any personal
        data. It assumes no liability for the unauthorized use, distribution, or interpretation of the information
        contained in this report.
    </p>
</div>

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

{{-- Standard Fields --}}
@foreach([
    'fullNames' => 'Full Names',
    'userNames' => 'Usernames',
    'emails' => 'Emails',
    'phones' => 'Phone Numbers',
    'locations' => 'Locations',
    'lastUpdated' => 'Last Updated',
    'basicInfo' => 'Basic Info'
] as $field => $label)
    @if(!empty($profile[$field]))
        <div class="section">
            <h2>{{ $label }}</h2>
            <ul>
                @foreach($profile[$field] as $item)
                    <li>
                        {{ is_array($item['value'] ?? '') ? json_encode($item['value']) : ($item['value'] ?? 'N/A') }}
                        @if(!empty($item['source']))
                            <span class="source-label">(Source: {{ $item['source'] }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endforeach

{{-- Skills --}}
@if(!empty($profile['skills']))
    <div class="section">
        <h2>Skills</h2>
        <ul>
            @foreach($profile['skills'] as $skill)
                <li>
                    {{ is_array($skill['value'] ?? '') ? json_encode($skill['value']) : ($skill['value'] ?? 'N/A') }}
                    @if(!empty($skill['source']))
                        <span class="source-label">(Source: {{ $skill['source'] }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Qualifications --}}
@if(!empty($profile['qualifications']))
    <div class="section">
        <h2>Qualifications</h2>
        <ul>
            @foreach($profile['qualifications'] as $qual)
                <li>
                    <strong>{{ $qual['degree'] ?? 'N/A' }}</strong> in {{ $qual['field'] ?? 'N/A' }} @ {{ $qual['school'] ?? 'N/A' }}
                    ({{ $qual['startYear'] ?? '?' }} - {{ $qual['endYear'] ?? '?' }})
                    @if(!empty($qual['url']))
                        - <a href="{{ $qual['url'] }}" style="color: #60a5fa; text-decoration: underline;" target="_blank">View</a>
                    @endif
                    @if(!empty($qual['source']))
                        <span class="source-label">(Source: {{ $qual['source'] }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Experience --}}
@if(!empty($profile['experience']))
    <div class="section">
        <h2>Experience</h2>
        <ul>
            @foreach($profile['experience'] as $job)
                <li>
                    <strong>{{ $job['title'] ?? 'N/A' }}</strong> @ {{ $job['company'] ?? 'N/A' }}
                    ({{ $job['startYear'] ?? '?' }} - {{ $job['endYear'] ?? '?' }})
                    @if(!empty($job['url']))
                        - <a href="{{ $job['url'] }}" style="color: #60a5fa; text-decoration: underline;" target="_blank">View</a>
                    @endif
                    @if(!empty($job['source']))
                        <span class="source-label">(Source: {{ $job['source'] }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Social Media Presence --}}
@if(!empty($profile['socialMediaPresence']))
    <div class="section">
        <h2>Social Media Presence</h2>
        <ul class="grid">
            @foreach($profile['socialMediaPresence'] as $platform => $status)
                <li class="grid-item">
                    <span class="capitalize">{{ $platform }}:</span>
                    <span class="{{ $status ? 'status-active' : 'status-inactive' }}">
                        {{ is_bool($status) ? ($status ? 'Active' : 'Inactive') : 'Active' }}
                    </span>
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
                        <div>{{ $subKey }}: {{ is_array($subValue) ? implode(', ', $subValue) : $subValue }}</div>
                    @endforeach
                @else
                    <div>{{ $value }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif

{{-- Breach Data --}}
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

{{-- Gravatar --}}
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
<div  style="page-break-before: always;">
    <h2 style="">LEGAL DISCLAIMER FOR OSINT REPORT</h2>
    <p style="font-size: 14px; color: #d1d5db; line-height: 1.6;">
        This Open Source Intelligence (OSINT) report has been prepared by <strong>OSINTWORK</strong>, a private entity acting solely as a technical intermediary, at the request of, and for the exclusive use of, authorized law enforcement agencies. The information contained in this report has been gathered strictly from publicly accessible sources and legally verified digital tools as of the date of its generation. No leaked, unauthorized, or unlawfully obtained data has been used in its preparation.
    </p>
    <ol style="font-size: 14px; color: #d1d5db; line-height: 1.6; padding-left: 20px;">
        <li><strong>Authorized Use:</strong> This report is intended solely for use by duly authorized law enforcement officers for legitimate investigative purposes, as defined under Indian law. The requesting agency assumes full responsibility for the lawful, ethical, and appropriate use of the information contained herein.</li>

        <li><strong>Legal Compliance:</strong> Use of this report must be in strict compliance with all applicable Indian laws and regulations, including but not limited to:
            <ul style="margin-top: 4px; margin-bottom: 4px;">
                <li>The Information Technology Act, 2000</li>
                <li>The Bharatiya Nyaya Sanhita (BNS)</li>
                <li>The Bharatiya Nagarik Suraksha Sanhita (BNSS)</li>
                <li>The Bharatiya Sakshya Adhiniyam (BSA)</li>
                <li>Relevant constitutional protections, including Article 21 pertaining to the Right to Privacy</li>
                <li>Upon its enforcement, the Digital Personal Data Protection Act, 2023</li>
            </ul>
        </li>

        <li><strong>Data Protection and Retention:</strong> OSINTWORK compiles this report using ethical and legally compliant OSINT methodologies. No personal data is stored post-transmission. The requesting law enforcement agency bears sole responsibility for ensuring compliance with relevant data protection regulations and internal data handling policies.</li>

        <li><strong>Verification Requirement:</strong> The information in this report is derived from OSINT techniques and should be treated as preliminary intelligence. It must be independently verified by the requesting agency through official and legally admissible channels prior to being used in legal proceedings or enforcement actions.</li>

        <li><strong>Confidentiality:</strong> This report is strictly confidential and may not be disclosed, shared, or disseminated beyond the scope of the official investigation for which it was requested. The agency is responsible for maintaining the confidentiality of this document and limiting access to authorized personnel only.</li>

        <li><strong>Limited Liability:</strong> OSINTWORK shall not be held liable for any direct or indirect consequences arising from the use, misuse, or interpretation of the information provided herein. The report is furnished in good faith as an intermediary service, and OSINTWORK makes no representations or warranties regarding the completeness, accuracy, or reliability of the data.</li>

        <li><strong>No Legal Advice:</strong> This report does not constitute legal advice. The recipient agency must consult its own legal counsel for guidance on the lawful and appropriate use of the information contained in this report in investigations, legal filings, or court proceedings.</li>

        <li><strong>Ethical Use:</strong> OSINTWORK adheres to high ethical standards in OSINT collection and reporting. The receiving agency is expected to ensure that the information is used in a manner consistent with ethical law enforcement practices, upholding individual rights without compromising the integrity of legitimate investigations.</li>

        <li><strong>Contractual Obligations:</strong> Use of this report is further subject to any terms and conditions specified in the service agreement or memorandum of understanding between OSINTWORK and the requesting agency.</li>
    </ol>
    <p style="margin-top: 12px; font-size: 14px; color: #d1d5db;">
        By accepting and utilizing this report, the law enforcement agency affirms its agreement with the above terms and acknowledges that OSINTWORK acts strictly as an intermediary, without control over source data or its subsequent application. The agency is responsible for ensuring that all personnel handling this report are aware of and fully comply with these terms.
    </p>
</div>

</div>
</body>
</html>
