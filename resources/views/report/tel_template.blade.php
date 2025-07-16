<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phone OSINT Report</title>
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
        .text-green { color: #16a34a; font-weight: bold; }
        .text-red { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <h1 id="title">Phone OSINT Report</h1>

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

    {{-- Main Info Fields --}}
    @foreach([
    'fullNames' => 'Full Names',
    'userNames' => 'Usernames',
    'phones' => 'Phone Numbers',
    'emails' => 'Emails',
    'locations' => 'Locations',
    'verifiedAddress' => 'Verified Address',
    'lastUpdated' => 'Last Updated',
    'basicInfo' => 'Basic Info',
    'bankDetails' => 'Bank Details',
    'upiDetails' => 'UPI Details',
    'idProofs' => 'ID Proofs',
    'rcNumber' => 'RC Number'
] as $field => $label)
        @if(!empty($profile[$field]))
            <div class="section">
                <h2>{{ $label }}</h2>
                <table class="info-table">
                    @foreach($profile[$field] as $item)
                        <tr>
                            <th>{{ $label }}</th>
                            <td>
                                {{ is_array($item['value'] ?? '') ? json_encode($item['value']) : ($item['value'] ?? $item ?? 'N/A') }}
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
            <div class="info-table">
                @foreach ($profile['socialMediaPresence'] as $platform => $status)
                    <div style="display: flex; justify-content: space-between; padding: 8px;">
                        <span class="capitalize">{{ $platform }}:</span>
                        <span class="{{ $status ? 'text-green' : 'text-red' }}">
                            {{ is_bool($status) ? ($status ? 'Yes' : 'No') : (empty($status) ? 'No' : 'Yes') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Carriers --}}
    @if(!empty($profile['carriers']))
        <div class="section">
            <h2>Carrier Information</h2>
            <table class="info-table">
                @foreach($profile['carriers'] as $carrier)
                    <tr>
                        <th>Carrier</th>
                        <td>
                            {{ is_array($carrier) ? ($carrier['value'] ?? '') : $carrier }}
                            @if(is_array($carrier) && !empty($carrier['source']))
                                <span class="source-label">(Source: {{ $carrier['source'] }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Country Codes --}}
    @if(!empty($profile['countryCodes']))
        <div class="section">
            <h2>Country Codes</h2>
            <table class="info-table">
                @foreach($profile['countryCodes'] as $code)
                    <tr>
                        <th>Country Code</th>
                        <td>
                            {{ is_array($code) ? ($code['value'] ?? 'N/A') : $code }}
                            @if(is_array($code) && !empty($code['source']))
                                <span class="source-label">(Source: {{ $code['source'] }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
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

               
               
               
            <li><strong>Data Protection and Retention:</strong> OSINTWORK compiles this report using ethical and leg
         a      lly compliant OSINT methodologies. No personal data is stored post-transmission. The requesting law 
           e    nforcement agency bears sole responsibility for ensuring compliance with relevant data protection regulations and internal data handling policies.</li>

            <li><strong>Verification Requirement:</strong> The information in this report is derived from OSINT t
            e   chniques and should be treated as preliminary intelligence. It must be independently verified 
             b  y the requesting agency through official and legally admissible channels prior to being used in legal p
              r oceedings or enforcement actions.</li>

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