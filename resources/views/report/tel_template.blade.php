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
<div class="section" style="margin-bottom: 24px;">
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
<div class="section" style="page-break-before: always;">
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
</body>
</html>

