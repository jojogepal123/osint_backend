<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Phone Intelligence Report</title>
  @include('report.partials.theme')
  <style>
    .text-green { color: #166534; font-weight: bold; }
    .text-red   { color: #991b1b; font-weight: bold; }
    .img-cell { display: table-cell; width: 90px; padding-right: 10px; vertical-align: top; text-align: center; }
    .img-cell img { width: 72px; height: 72px; border-radius: 8px; border: 2px solid #0f3460; object-fit: cover; }
    .img-src { font-size: 8px; color: #888; margin-top: 2px; }
  </style>
</head>
<body>

@php
  $s = function($v, $fallback = '') {
      if (is_null($v)) return $fallback;
      if (is_bool($v)) return $v ? 'Yes' : 'No';
      if (is_array($v)) {
          $flat = array_filter($v, 'is_scalar');
          return count($flat) ? implode(', ', $flat) : $fallback;
      }
      $str = (string)$v;
      return ($str === '' || strtolower($str) === 'n/a') ? $fallback : $str;
  };
  $profile = $data['profile'] ?? [];
  $subjectPhone = '';
  if (!empty($profile['phones'][0]['value'])) $subjectPhone = $profile['phones'][0]['value'];
  elseif (!empty($data['phone'])) $subjectPhone = $data['phone'];
@endphp

@include('report.partials.header', ['reportType' => 'Phone Intelligence Report'])

@if ($subjectPhone)
<div class="rpt-subject">
  <div class="rpt-subject-label">Target Phone Number</div>
  <div class="rpt-subject-value">{{ $subjectPhone }}</div>
</div>
@endif

{{-- ── Confidential Disclaimer ── --}}
<div class="section" style="margin-top:16px;">
  <div class="section-title">CONFIDENTIAL – FOR AUTHORIZED LAW ENFORCEMENT PERSONNEL ONLY</div>
  <p style="font-size:11px; line-height:1.6; color:#333; margin-bottom:6px;">This report is intended strictly for legitimate investigative use by authorized law enforcement officers, in accordance with applicable Indian laws and regulations. It contains intelligence derived solely from publicly accessible sources and licensed investigative tools. No unauthorized, leaked, or unlawfully obtained data is included.</p>
  <p style="font-size:11px; line-height:1.6; color:#333; margin-bottom:6px;">Distribution of this report through unauthorized channels—including but not limited to WhatsApp, Telegram, email groups, or other social media platforms—is strictly prohibited.</p>
  <p style="font-size:11px; line-height:1.6; color:#333; margin-bottom:6px;">All information contained herein must be handled with utmost confidentiality and used in full compliance with applicable legal frameworks, including the <strong>Information Technology Act, 2000</strong>, and the <strong>Digital Personal Data Protection Act, 2023</strong> (upon its enforcement).</p>
  <p style="font-size:11px; line-height:1.6; color:#333; margin-bottom:6px;">Law enforcement personnel are solely responsible for ensuring that any use of this information is supported by appropriate legal authorization or explicit consent from the data subject, as required.</p>
  <p style="font-size:11px; line-height:1.6; color:#333;"><strong>{{ config('app.name', 'DRASHTA') }}</strong> operates solely as a technical intermediary and does not store or retain any personal data. It assumes no liability for the unauthorized use, distribution, or interpretation of the information contained in this report.</p>
</div>

{{-- ── Profile Images ── --}}
@if(!empty($profile['profileImages']))
<div class="section">
  <div class="section-title">Profile Images</div>
  <table width="100%" cellspacing="0" cellpadding="8">
    @foreach(array_chunk($profile['profileImages'], 3) as $row)
      <tr>
        @foreach($row as $img)
          <td align="center" style="border:1px solid #d0dce8; background:#f8faff; border-radius:4px;">
            <p style="font-size:10px; font-weight:bold; color:#0f3460; margin:0 0 4px;">{{ $img['source'] ?? 'Unknown' }}</p>
            @if(!empty($img['base64']))
              <img src="{{ $img['base64'] }}" style="width:80px;height:80px;border-radius:8px;border:2px solid #0f3460;object-fit:cover;" />
            @else
              <p style="font-size:10px;color:#888;">Not available</p>
            @endif
          </td>
        @endforeach
      </tr>
    @endforeach
  </table>
</div>
@endif

{{-- ── Main Info Fields ── --}}
@foreach([
  'fullNames'       => 'Full Names',
  'userNames'       => 'Usernames',
  'phones'          => 'Phone Numbers',
  'emails'          => 'Emails',
  'locations'       => 'Locations',
  'verifiedAddress' => 'Verified Address',
  'lastUpdated'     => 'Last Updated',
  'basicInfo'       => 'Basic Info',
  'bankDetails'     => 'Bank Details',
  'upiDetails'      => 'UPI Details',
  'idProofs'        => 'ID Proofs',
  'rcNumber'        => 'RC Number',
] as $field => $label)
  @if(!empty($profile[$field]))
  <div class="section">
    <div class="section-title">{{ $label }}</div>
    <table>
      @foreach($profile[$field] as $item)
        @php $val = is_array($item['value'] ?? '') ? json_encode($item['value']) : ($item['value'] ?? (is_scalar($item) ? $item : '')); @endphp
        @if($val)
        <tr>
          <th>{{ $label }}</th>
          <td>{{ $val }}@if(!empty($item['source']))<span class="source-label">({{ $item['source'] }})</span>@endif</td>
        </tr>
        @endif
      @endforeach
    </table>
  </div>
  @endif
@endforeach

{{-- ── SignalHire Professional Data ── --}}
@foreach([
  'telBio'           => 'Bio',
  'jobProfiles'      => 'Job Profiles',
  'shExperience'     => 'Work Experience',
  'shEducation'      => 'Education',
  'shSkills'         => 'Skills',
  'shCertifications' => 'Certifications',
  'shOrganizations'  => 'Organizations',
  'shHonorAwards'    => 'Awards & Honors',
] as $field => $label)
  @if(!empty($profile[$field]))
  <div class="section">
    <div class="section-title">{{ $label }}</div>
    <table>
      @foreach($profile[$field] as $item)
        @php $val = is_array($item['value'] ?? '') ? json_encode($item['value']) : ($item['value'] ?? ''); @endphp
        @if($val)
        <tr>
          <th>{{ $s($item['key'] ?? '') ?: $label }}</th>
          <td>{{ $val }}@if(!empty($item['source']))<span class="source-label">({{ $item['source'] }})</span>@endif</td>
        </tr>
        @endif
      @endforeach
    </table>
  </div>
  @endif
@endforeach

{{-- ── Social Profiles ── --}}
@if(!empty($profile['shSocialLinks']))
<div class="section">
  <div class="section-title">Social Profiles</div>
  <table>
    @foreach($profile['shSocialLinks'] as $link)
      <tr>
        <th>{{ $s($link['key'] ?? '') ?: 'Profile' }}</th>
        <td>
          @if(!empty($link['url']))<a href="{{ $link['url'] }}">{{ $s($link['value'] ?? $link['url']) }}</a>
          @else{{ $s($link['value'] ?? '') }}@endif
          @if(!empty($link['source']))<span class="source-label">({{ $link['source'] }})</span>@endif
        </td>
      </tr>
    @endforeach
  </table>
</div>
@endif

{{-- ── Boolean Fields ── --}}
@foreach([
  'numberIsActivate' => 'Number Active',
  'isSpam'           => 'Spam Number',
  'isBusiness'       => 'Business Number',
] as $key => $label)
  @if(isset($profile[$key]))
  <div class="section">
    <div class="section-title">{{ $label }}</div>
    <div><span class="{{ $profile[$key] ? 'badge-yes' : 'badge-no' }}">{{ $profile[$key] ? 'Yes' : 'No' }}</span></div>
  </div>
  @endif
@endforeach

{{-- ── Social Media Presence ── --}}
@if(!empty($profile['socialMediaPresence']) && is_array($profile['socialMediaPresence']))
<div class="section">
  <div class="section-title">Social Media Presence</div>
  <div style="padding:4px 0;">
    @foreach($profile['socialMediaPresence'] as $platform => $status)
      @php $active = is_bool($status) ? $status : !empty($status); @endphp
      <span class="{{ $active ? 'pill-active' : 'pill-inactive' }}">{{ ucfirst($platform) }}: {{ $active ? 'Active' : 'Inactive' }}</span>
    @endforeach
  </div>
</div>
@endif

{{-- ── Carriers ── --}}
@if(!empty($profile['carriers']))
<div class="section">
  <div class="section-title">Carrier Information</div>
  <table>
    @foreach($profile['carriers'] as $carrier)
      @php $val = is_array($carrier) ? ($carrier['value'] ?? '') : $carrier; $src = is_array($carrier) ? ($carrier['source'] ?? '') : ''; @endphp
      @if($val)
      <tr>
        <th>Carrier</th>
        <td>{{ $val }}@if($src)<span class="source-label">({{ $src }})</span>@endif</td>
      </tr>
      @endif
    @endforeach
  </table>
</div>
@endif

{{-- ── Country Codes ── --}}
@if(!empty($profile['countryCodes']))
<div class="section">
  <div class="section-title">Country Codes</div>
  <table>
    @foreach($profile['countryCodes'] as $code)
      @php $val = is_array($code) ? ($code['value'] ?? '') : $code; $src = is_array($code) ? ($code['source'] ?? '') : ''; @endphp
      @if($val)
      <tr>
        <th>Country Code</th>
        <td>{{ $val }}@if($src)<span class="source-label">({{ $src }})</span>@endif</td>
      </tr>
      @endif
    @endforeach
  </table>
</div>
@endif

{{-- ── OSINT / Leak Data ── --}}
@if(!empty($data['osintData']))
<div class="section">
  <div class="section-title">Leak Data Found</div>
  <table>
    @foreach($data['osintData'] as $value)
      <tr>
        <td colspan="2">
          @if(is_array($value))
            @foreach($value as $item)
              @if(is_string($item) && filter_var($item, FILTER_VALIDATE_URL))
                <a href="{{ $item }}">{{ $item }}</a><br>
              @elseif(is_scalar($item))
                {{ $item }}<br>
              @endif
            @endforeach
          @elseif(is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
            <a href="{{ $value }}">{{ $value }}</a>
          @elseif(is_scalar($value))
            {{ $value }}
          @endif
        </td>
      </tr>
    @endforeach
  </table>
</div>
@endif

{{-- ── Legal Disclaimer ── --}}
<div class="disclaimer">
  <div class="disclaimer-title">Legal Disclaimer</div>
  <p style="margin-bottom:6px;">This Phone Intelligence report has been prepared by <strong>{{ config('app.name', 'DRASHTA') }}</strong>, acting solely as a technical intermediary, for the exclusive use of authorized personnel. Information is gathered from publicly accessible sources and legally verified digital tools.</p>
  <ol>
    <li><strong>Authorized Use:</strong> Intended solely for duly authorized personnel for legitimate investigative purposes under Indian law.</li>
    <li><strong>Legal Compliance:</strong> Must comply with the IT Act 2000, BNS, BNSS, BSA, Article 21, and the DPDP Act 2023.</li>
    <li><strong>Verification Required:</strong> Data must be independently verified before use in legal proceedings.</li>
    <li><strong>Confidentiality:</strong> Strictly confidential — do not share beyond the scope of the official investigation.</li>
    <li><strong>Limited Liability:</strong> {{ config('app.name', 'DRASHTA') }} assumes no liability for use, misuse, or interpretation of information herein.</li>
    <li><strong>No Legal Advice:</strong> This report does not constitute legal advice. Consult your legal counsel before use in proceedings.</li>
  </ol>
</div>

@include('report.partials.footer')

</body>
</html>
