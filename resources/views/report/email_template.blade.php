<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Intelligence Report</title>
  @include('report.partials.theme')
  <style>
    .img-row { display: table; width: 100%; }
    .img-cell { display: table-cell; width: 90px; padding-right: 10px; vertical-align: top; text-align: center; }
    .img-cell img { width: 72px; height: 72px; border-radius: 8px; border: 2px solid #0f3460; object-fit: cover; }
    .img-src { font-size: 8px; color: #888; margin-top: 2px; }
    .pills-wrap { padding: 4px 0; }
    .gravatar-tbl { display: table; width: 100%; }
    .gravatar-img-cell { display: table-cell; width: 70px; vertical-align: top; }
    .gravatar-img-cell img { width: 60px; height: 60px; border-radius: 8px; border: 2px solid #0f3460; object-fit: cover; }
    .gravatar-info-cell { display: table-cell; vertical-align: top; padding-left: 12px; font-size: 11px; color: #333; }
    .gravatar-info-cell p { margin-bottom: 4px; }
    .breach-card { border: 1px solid #d0dce8; border-radius: 4px; margin-bottom: 6px; padding: 6px 12px; background: #f8faff; display: table; width: 100%; }
    .breach-logo-cell { display: table-cell; width: 28px; vertical-align: middle; }
    .breach-logo-cell img { width: 22px; height: 22px; border-radius: 50%; }
    .breach-name-cell { display: table-cell; vertical-align: middle; font-size: 11px; font-weight: bold; color: #0f3460; padding-left: 8px; }
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
  $subjectEmail = '';
  if (!empty($profile['emails'][0]['value'])) $subjectEmail = $profile['emails'][0]['value'];
  elseif (!empty($data['email'])) $subjectEmail = $data['email'];
@endphp

@include('report.partials.header', ['reportType' => 'Email Intelligence Report'])

@if ($subjectEmail)
<div class="rpt-subject">
  <div class="rpt-subject-label">Target Email Address</div>
  <div class="rpt-subject-value">{{ $subjectEmail }}</div>
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
  <div class="img-row">
    @foreach($profile['profileImages'] as $img)
      <div class="img-cell">
        @if(!empty($img['base64']))
          <img src="{{ $img['base64'] }}" alt="Profile" />
        @else
          <div style="width:72px;height:72px;border-radius:8px;border:2px solid #0f3460;background:#f8faff;display:flex;align-items:center;justify-content:center;font-size:8px;color:#888;">Not available</div>
        @endif
        @if(!empty($img['source']))<div class="img-src">{{ $img['source'] }}</div>@endif
      </div>
    @endforeach
  </div>
</div>
@endif

{{-- ── Basic Fields ── --}}
@foreach([
  'fullNames'   => 'Full Names',
  'userNames'   => 'Usernames',
  'emails'      => 'Emails',
  'phones'      => 'Phone Numbers',
  'locations'   => 'Locations',
  'lastUpdated' => 'Last Updated',
  'basicInfo'   => 'Basic Info',
] as $field => $label)
  @if(!empty($profile[$field]))
  <div class="section">
    <div class="section-title">{{ $label }}</div>
    <table>
      @foreach($profile[$field] as $item)
        @php $val = is_array($item['value'] ?? '') ? json_encode($item['value']) : ($item['value'] ?? ''); @endphp
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

{{-- ── Skills ── --}}
@if(!empty($profile['skills']))
<div class="section">
  <div class="section-title">Skills</div>
  <div class="pills-wrap">
    @foreach($profile['skills'] as $skill)
      @php $sv = $s($skill['value'] ?? (is_string($skill) ? $skill : '')); @endphp
      @if($sv)<span class="skill-pill">{{ $sv }}</span>@endif
    @endforeach
  </div>
</div>
@endif

{{-- ── Qualifications ── --}}
@if(!empty($profile['qualifications']))
<div class="section">
  <div class="section-title">Qualifications</div>
  @foreach($profile['qualifications'] as $qual)
    <div class="card">
      <div class="card-head">{{ $s($qual['degree'] ?? '') ?: 'Degree' }}</div>
      <div class="card-body">
        @if($s($qual['field'] ?? ''))<div class="card-row"><span class="card-label">Field: </span>{{ $s($qual['field']) }}</div>@endif
        @if($s($qual['school'] ?? ''))<div class="card-row"><span class="card-label">School: </span>{{ $s($qual['school']) }}</div>@endif
        @if($s($qual['startYear'] ?? '') || $s($qual['endYear'] ?? ''))
          <div class="card-row"><span class="card-label">Period: </span>{{ $s($qual['startYear'] ?? '?') }} &mdash; {{ $s($qual['endYear'] ?? '?') }}</div>
        @endif
        @if(!empty($qual['source']))<div class="card-row"><span class="source-label">Source: {{ $qual['source'] }}</span></div>@endif
      </div>
    </div>
  @endforeach
</div>
@endif

{{-- ── Experience ── --}}
@if(!empty($profile['experience']))
<div class="section">
  <div class="section-title">Work Experience</div>
  @foreach($profile['experience'] as $job)
    <div class="card">
      <div class="card-head">{{ $s($job['title'] ?? '') ?: 'Role' }}@if($s($job['company'] ?? '')) &nbsp;at {{ $s($job['company']) }}@endif</div>
      <div class="card-body">
        @if($s($job['startYear'] ?? '') || $s($job['endYear'] ?? ''))
          <div class="card-row"><span class="card-label">Period: </span>{{ $s($job['startYear'] ?? '?') }} &mdash; {{ $s($job['endYear'] ?? '?') }}</div>
        @endif
        @if(!empty($job['source']))<div class="card-row"><span class="source-label">Source: {{ $job['source'] }}</span></div>@endif
      </div>
    </div>
  @endforeach
</div>
@endif

{{-- ── SignalHire Professional Data ── --}}
@foreach([
  'shBio'           => 'Bio',
  'shExperience'    => 'Experience (SH)',
  'shEducation'     => 'Education (SH)',
  'shSkills'        => 'Skills (SH)',
  'shCertifications'=> 'Certifications',
  'shOrganizations' => 'Organizations',
  'shHonorAwards'   => 'Awards & Honors',
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

{{-- ── Internet Presence ── --}}
@if(!empty($profile['socialMediaPresence']))
<div class="section">
  <div class="section-title">Internet Presence</div>
  <div class="pills-wrap">
    @foreach($profile['socialMediaPresence'] as $platform => $status)
      @php $active = is_bool($status) ? $status : true; @endphp
      <span class="{{ $active ? 'pill-active' : 'pill-inactive' }}">{{ ucfirst($platform) }}: {{ $active ? 'Active' : 'Inactive' }}</span>
    @endforeach
  </div>
</div>
@endif

{{-- ── OSINT / Leak Data ── --}}
@if(!empty($data['osintData']))
<div class="section">
  <div class="section-title">Leak Data Found</div>
  <table>
    @foreach($data['osintData'] as $key => $value)
      <tr>
        <td colspan="2">
          @if(is_array($value))
            @foreach($value as $item)
              @if(is_string($item))
                @if(filter_var($item, FILTER_VALIDATE_URL))
                  <a href="{{ $item }}">{{ $item }}</a><br>
                @else
                  {{ $item }}<br>
                @endif
              @else
                <pre style="font-size:9px; white-space:pre-wrap; word-break:break-all; margin:0;">{{ is_scalar($item) ? $item : json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
              @endif
            @endforeach
          @elseif(is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
            <a href="{{ $value }}">{{ $value }}</a>
          @else
            {{ $value }}
          @endif
        </td>
      </tr>
    @endforeach
  </table>
</div>
@endif

{{-- ── Breach Data ── --}}
@if(!empty($data['breachData']))
<div class="section">
  <div class="section-title">Data Breaches</div>
  @foreach($data['breachData'] as $breach)
    @if(is_array($breach))
    <div class="breach-card">
      <div class="breach-logo-cell">
        @if(!empty($breach['LogoBase64']))<img src="{{ $breach['LogoBase64'] }}" alt="logo">@endif
      </div>
      <div class="breach-name-cell">{{ $s($breach['Name'] ?? '') ?: 'Unknown Breach' }}</div>
    </div>
    @endif
  @endforeach
</div>
@endif

{{-- ── Gravatar ── --}}
@if(!empty($data['gravatar']))
  @foreach($data['gravatar'] as $item)
    @if(($item['source'] ?? '') === 'Gravatar' && ($item['status'] ?? '') === 'found')
    <div class="section">
      <div class="section-title">Gravatar Profile</div>
      <div class="gravatar-tbl">
        <div class="gravatar-img-cell">
          @if(!empty($item['avatar_base64']))<img src="{{ $item['avatar_base64'] }}" alt="avatar">
          @elseif(!empty($item['avatar_url']))<img src="{{ $item['avatar_url'] }}" alt="avatar">
          @else<div style="width:60px;height:60px;background:#e8f0fe;border-radius:8px;border:2px solid #0f3460;line-height:60px;text-align:center;font-size:9px;color:#0f3460;">No Photo</div>@endif
        </div>
        <div class="gravatar-info-cell">
          @if(!empty($item['username']))<p><span style="font-weight:bold;color:#0f3460;">Username:</span> {{ $item['username'] }}</p>@endif
          @if(!empty($item['profile_url']))<p><span style="font-weight:bold;color:#0f3460;">Profile:</span> <a href="{{ $item['profile_url'] }}">{{ $item['profile_url'] }}</a></p>@endif
        </div>
      </div>
    </div>
    @endif
  @endforeach
@endif

{{-- ── Public Location Data ── --}}
@if(!empty($data['mapData']))
<div class="section">
  <div class="section-title">Public Location Data</div>
  @foreach($data['mapData'] as $place)
    <div class="card">
      <div class="card-head">{{ $s($place['name'] ?? '') ?: 'Location' }}</div>
      <div class="card-body">
        @if($s($place['address'] ?? ''))<div class="card-row"><span class="card-label">Address: </span>{{ $s($place['address']) }}</div>@endif
        @if($s($place['date'] ?? ''))<div class="card-row"><span class="card-label">Date: </span>{{ $s($place['date']) }}</div>@endif
        @if(!empty($place['mapImage']))
          <div style="margin-top:6px;"><img src="data:image/png;base64,{{ $place['mapImage'] }}" alt="map" style="width:100%;max-width:420px;border-radius:6px;border:1px solid #d0dce8;"></div>
        @endif
      </div>
    </div>
  @endforeach
</div>
@endif

{{-- ── Legal Disclaimer ── --}}
<div class="disclaimer">
  <div class="disclaimer-title">Legal Disclaimer</div>
  <p style="margin-bottom:6px;">This Email Intelligence report has been prepared by <strong>{{ config('app.name', 'DRASHTA') }}</strong>, acting solely as a technical intermediary, for the exclusive use of authorized personnel. Information is gathered from publicly accessible sources and legally verified digital tools.</p>
  <ol>
    <li><strong>Authorized Use:</strong> Intended solely for duly authorized personnel for legitimate investigative purposes.</li>
    <li><strong>Legal Compliance:</strong> Use must comply with the IT Act 2000, BNS, BNSS, BSA, Article 21, and the DPDP Act 2023.</li>
    <li><strong>Verification Required:</strong> Data must be independently verified before use in legal proceedings.</li>
    <li><strong>Confidentiality:</strong> This report is strictly confidential — do not share beyond the scope of the official investigation.</li>
    <li><strong>Limited Liability:</strong> {{ config('app.name', 'DRASHTA') }} assumes no liability for the use, misuse, or interpretation of information herein.</li>
  </ol>
</div>

@include('report.partials.footer')

</body>
</html>
