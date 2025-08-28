<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Email OSINT Report</title>
    <style>
        body {
            background-color: #f9fafb;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 24px;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 24px;
        }

        #title {
            background-color: rgba(209, 213, 219, 0.53);
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 20px;
            font-weight: bold;
            border-left: 4px solid #2563eb;
        }

        h2 {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 1px solid #d1d5db;
            margin-bottom: 12px;
            padding-bottom: 4px;
        }

        .section {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        ul {
            padding-left: 20px;
            margin-top: 8px;
        }

        li {
            margin-bottom: 6px;
        }

        .card-li {
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }

        .source-label {
            font-size: 11px;
            color: #6b7280;
            margin-left: 4px;
        }

        .profile-images {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .image-box {
            width: 120px;
            text-align: center;
        }

        .image-box img {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }

        .gravatar-avatar,
        .avatar-placeholder {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            border: 2px solid #9ca3af;
            object-fit: cover;
        }

        .avatar-placeholder {
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 12px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 8px;
        }

        .grid-item {
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .status-active {
            color: #16a34a;
            font-weight: bold;
        }

        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }

        .gravatar-card {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .osint-card,
        .breach-card {
            margin-top: 12px;
        }

        .breach-entry {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            gap: 10px;
        }

        .breach-entry img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 10px;
        }

        #legal-disclaimer {
            page-break-before: always;
            font-size: 12px;
            background-color: #fefce8;
            border: 1px solid #facc15;
            color: #78350f;
        }

        #confidential-disclaimer {
            font-size: 13px;
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            color: #0c4a6e;
        }

        a {
            color: #2563eb;
            text-decoration: underline;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #fff;
            border: 1px solid #e5e7eb;
            font-size: 15px;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            vertical-align: middle;
        }

        .info-table th {
            background: #f3f4f6;
            color: #1e40af;
            font-weight: 600;
            text-align: left;
            width: 200px;
            white-space: nowrap;
        }

        .info-table .source-label {
            font-size: 11px;
            color: #6b7280;
            margin-left: 4px;
        }
        .map-key{
            color: #1e40af;
            font-weight: 600;
            text-align: left;
            width: 200px;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <h1 id="title">Email OSINT Report</h1>

    @php $profile = $data['profile'] ?? []; @endphp

    {{-- Confidential Disclaimer --}}
    <div class="section" id="confidential-disclaimer">
        <h2>CONFIDENTIAL – FOR AUTHORIZED LAW ENFORCEMENT PERSONNEL ONLY</h2>
        <p>
            This report is intended strictly for legitimate investigative use by authorized law enforcement officers, in
            accordance with applicable Indian laws and regulations. It contains intelligence derived solely from
            publicly
            accessible sources and licensed investigative tools. No unauthorized, leaked, or unlawfully obtained data is
            included.
        </p>
        <p>
            Distribution of this report through unauthorized channels—including but not limited to WhatsApp, Telegram,
            email
            groups, or other social media platforms—is strictly prohibited.
        </p>
        <p>
            All information contained herein must be handled with utmost confidentiality and used in full compliance
            with
            applicable legal frameworks, including the <strong>Information Technology Act, 2000</strong>, and the
            <strong>Digital Personal Data Protection Act, 2023</strong> (upon its enforcement).
        </p>
        <p>
            Law enforcement personnel are solely responsible for ensuring that any use of this information is supported
            by
            appropriate legal authorization or explicit consent from the data subject, as required.
        </p>
        <p>
            <strong>OSINTWORK</strong> operates solely as a technical intermediary and does not store or retain any
            personal
            data. It assumes no liability for the unauthorized use, distribution, or interpretation of the information
            contained in this report.
        </p>
    </div>

    {{-- Profile Images --}}
    @if(!empty($profile['profileImages']))
        <div class="section">
            <h2>Profile Images</h2>
            <div class="profile-images">
                @foreach($profile['profileImages'] as $img)
                    <div class="image-box">
                        <img src="{{ $img['value'] }}" alt="Profile Image">
                        @if(!empty($img['source']))
                            <div class="source-label">(Source: {{ $img['source'] }})</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Basic Fields --}}
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
                <table class="info-table">
                    @foreach($profile[$field] as $item)
                        <tr>
                            <th>{{ $label }}</th>
                            <td>
                                {{ is_array($item['value'] ?? '') ? json_encode($item['value']) : ($item['value'] ?? 'N/A') }}
                                @if(!empty($item['source']))
                                    <span class="source-label">(Source: {{ $item['source'] }})</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    @endforeach

    {{-- Skills --}}
    @if(!empty($profile['skills']))
        <div class="section">
            <h2>Skills</h2>
            <table class="info-table">
                @foreach($profile['skills'] as $skill)
                    <tr>
                        <th>Skill</th>
                        <td>
                            {{ $skill['value'] ?? 'N/A' }}
                            @if(!empty($skill['source']))
                                <span class="source-label">(Source: {{ $skill['source'] }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Qualifications --}}
    @if(!empty($profile['qualifications']))

                 <div class="section">
            <h2>Qualifications</h2>
            <table class="info-table">
                @foreach($profile['qualifications'] as $qual)
                    <tr>
                        <th>Qualification</th>
                        <td>
                            <strong>{{ $qual['degree'] ?? 'N/A' }}</strong> in {{ $qual['field'] ?? 'N/A' }} @ {{ $qual['school'] ?? 'N/A' }}
                            ({{ $qual['startYear'] ?? '?' }} - {{ $qual['endYear'] ?? '?' }})
                            @if(!empty($qual['url']))
                                - <a href="{{ $qual['url'] }}">View</a>
                            @endif
                            @if(!empty($qual['source']))
                                <span class="source-label">(Source: {{ $qual['source'] }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Experience --}}
    @if(!empty($profile['experience']))
        <div class="section">
            <h2>Experience</h2>
            <table class="info-table">
                @foreach($profile['experience'] as $job)
                    <tr>
                        <th>Experience</th>
                        <td>
                            <strong>{{ $job['title'] ?? 'N/A' }}</strong> @ {{ $job['company'] ?? 'N/A' }}
                            ({{ $job['startYear'] ?? '?' }} - {{ $job['endYear'] ?? '?' }})
                            @if(!empty($job['url']))
                                - <a href="{{ $job['url'] }}">View</a>
                            @endif
                            @if(!empty($job['source']))
                                <span class="source-label">(Source: {{ $job['source'] }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Social Media --}}
    @if(!empty($profile['socialMediaPresence']))
        <div class="section">
            <h2>InternetPresence</h2>
            <div class="grid">
                @foreach($profile['socialMediaPresence'] as $platform => $status)
                    <div class="grid-item">
                        <span class="capitalize">{{ $platform }}:</span>
                        <span class="{{ $status ? 'status-active' : 'status-inactive' }}">
                            {{ is_bool($status) ? ($status ? 'Active' : 'Inactive') : 'Active' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- OSINT Data --}}
    @if(!empty($data['osintData']))
        <div class="section">
            <h2>OSINT Data</h2>
            <table class="info-table">
                <thead>
                    <tr>
                        <th>Leak Data Found</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['osintData'] as $key => $value)
                        <tr>
                            <td>
                                @if(is_array($value))
                                    @foreach($value as $item)
                                        @if(is_string($item) && filter_var($item, FILTER_VALIDATE_URL))
                                            <a href="{{ $item }}" target="_blank">{{ $item }}</a><br>
                                        @else
                                            {{ $item }}<br>
                                        @endif
                                    @endforeach
                                @else
                                    @if(is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
                                        <a href="{{ $value }}" target="_blank">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Breach Data --}}
    @if(!empty($data['breachData']))
        <div class="section">
            <h2>Breach Data</h2>
            <div class="breach-card">
                @foreach($data['breachData'] as $value)
                    @if(is_array($value))
                        <div class="breach-entry">
                            @if(!empty($value['LogoBase64']))
                                <img src="{{ $value['LogoBase64'] }}" alt="Logo">
                            @endif
                            <strong>{{ $value['Name'] ?? 'Unknown' }}</strong>
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
                    <div class="gravatar-card">
                        @if(!empty($item['avatar_url']))
                            <img src="{{ $item['avatar_url'] }}" class="gravatar-avatar" alt="{{ $item['username'] ?? 'avatar' }}">
                        @else
                            <div class="avatar-placeholder">No Avatar</div>
                        @endif
                        <div>
                            @if(!empty($item['username']))
                                <p><strong>Username:</strong> {{ $item['username'] }}</p>
                            @endif
                            @if(!empty($item['profile_url']))
                                <p><strong>Profile:</strong> <a href="{{ $item['profile_url'] }}">{{ $item['profile_url'] }}</a></p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
           @endforeach
    @endif

     

    @if(!empty($data['mapData']))
        <h2> Public Location Data</h2>
        @foreach($data['mapData'] as $index => $place)
            <div class="section">
                <h3 style="font-size:16px; font-weight:bold; color:#1f2937; margin-bottom:8px;">
                    {{ $place['name'] ?? 'Unknown Place' }}
                </h3>

                <p style="margin:4px 0;">
                    <strong class="map-key">Address:</strong> {{ $place['address'] ?? 'N/A' }}
                </p>

                <p style="margin:4px 0;">
                    <strong class="map-key">Date:</strong> {{ $place['date'] ?? '' }}
                </p>

                {{-- Map Image if available --}}
                @if(!empty($place['mapImage']))
                    <div style="margin-top:10px;">
                        <img src="data:image/png;base64,{{ $place['mapImage'] }}" 
                            alt="Map of {{ $place['name'] ?? 'Location' }}" 
                            style="width:100%; max-width:500px; border-radius:8px; border:1px solid #ddd;">
                    </div>
                @endif
            </div>
        @endforeach
    @endif

         
               
     
              {{-- Legal Disclaimer --}}
    <div id="legal-disclaimer" class="section">

        <h2>LEGAL DISCLAIMER FOR OSINT REPORT</h2>
        <p>
            This Open Source Intelligence (OSINT) report has been prepared by <strong>OSINTWORK</strong>, a private entity acting solely as a technical intermediary, at the request of, and for the exclusive use of, authorized law enforcement agencies. The information contained in this report has been gathered strictly from publicly accessible sources and legally verified digital tools as of the date of its generation. No leaked, unauthorized, or unlawfully obtained data has been used in its preparation.
        </p>
        <ol>
            <li><strong>Authorized Use:</strong> This report is intended solely for use by duly authorized law enfor
                    cement officers for legitimate investigative purposes, as defined under Indian law. The requesting agency assumes full responsibility for the lawful, ethical, and appropriate use of the information contained herein.</li>

            <li><strong>Legal Compliance:</strong> Use of this report must be in strict compliance with all applicable Indian laws and regulations, including but not limited to:
                <ul>
                    <li>The Information Technology Act, 2000</li>
                    <li>The Bharatiya Nyaya Sanhita (BNS)</li>

               
               
                                   <li>The Bharatiya Nagarik Suraksha Sanhita (BNSS)</li>
                    <li>The Bharatiya Sakshya Adhiniyam (BSA)</li>
              
        
        
                                   <li>Relevant constitutional protections, including Article 21 pertaining to the Right to Privacy</li>
                    <li>Upon its enforcement, the Digital Personal Data Protection Act, 2023</li>
           
                             
  
                   </ul>
            </li>

               
               
               
            <li><strong>Data Protection and Retention:</strong> OSINTWORK compiles this report using ethical and legally compliant OSINT methodologies. No personal data is stored post-transmission. The requesting lawenforcement agency bears sole responsibility for ensuring compliance with relevant data protection regulations and internal data handling policies.</li>

            <li><strong>Verification Requirement:</strong> The information in this report is derived from OSINT       techniques and should be treated as preliminary intelligence. It must be independently verified 
             by the requesting agency through official and legally admissible channels prior to being used in legal proceedings or enforcement actions.</li>

            <li><strong>Confidentiality:</strong> This report is strictly confidential and may not be disclosed,
                shared, or disseminated beyond the scope of the official investigation for which it was requested. The
                agency is responsible for maintaining the confidentiality of this document and limiting access to authorized personnel only.</li>

            <li><strong>Limited Liability:</strong> OSINTWORK shall not be held liable for any direct or indirect consequences arising from the use, misuse, or interpretation of the information provided herein. The report is furnished in good faith as an intermediary service, and OSINTWORK makes no representations or warranties regarding the completeness, accuracy, or reliability of the data.</li>


           
           
                       <li><strong>No Legal Advice:</strong> This report does not constitute legal advice. The recipient agency must consult its own legal counsel for guidance on the lawful and appropriate use of the information contained in this report in investigations, legal filings, or court proceedings.</li>

            <li><strong>Ethical Use:</strong> OSINTWORK adheres to high ethical standards in OSINT collection and reporting. The receiving agency is expected to ensure that the information is used in a manner consistent with ethical law enforcement practices, upholding individual rights without compromising the integrity of legitimate investigations.</li>

            <li><strong>Contractual Obligations:</strong> Use of this report is further subject to any terms and conditions specified in the service agreement or memorandum of understanding between OSINTWORK and the requesting agency.</li>
        </ol>
        <p>
            By accepting and utilizing this report, the law enforcement agency affirms its agreement with the above terms and acknowledges that OSINTWORK acts strictly as an intermediary, without control over source data or its subsequent application. The agency is responsible for ensuring that all personnel handling this report are aware of and fully comply with these terms.
        </p>
    </div>

</body>

</html>