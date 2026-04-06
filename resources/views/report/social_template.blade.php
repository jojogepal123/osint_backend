<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Social Intelligence Report</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

    .header { background: #0f3460; color: #fff; padding: 18px 24px 14px; }
    .header-top { display: table; width: 100%; }
    .header-brand { display: table-cell; vertical-align: middle; }
    .app-name { font-size: 22px; font-weight: bold; letter-spacing: 3px; color: #a8ff78; }
    .app-tagline { font-size: 9px; color: #b0c4de; letter-spacing: 1px; margin-top: 2px; }
    .header-meta { display: table-cell; vertical-align: middle; text-align: right; }
    .report-type { font-size: 13px; font-weight: bold; color: #a8ff78; }
    .report-date { font-size: 9px; color: #b0c4de; margin-top: 2px; }

    .accent-bar { height: 4px; background: #a8ff78; }

    .profile-hero { background: #eaf4fb; border-left: 5px solid #0f3460; padding: 14px 20px; margin: 16px 24px; display: table; width: calc(100% - 48px); }
    .hero-photo-cell { display: table-cell; vertical-align: top; width: 64px; }
    .hero-photo { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; border: 2px solid #0f3460; }
    .hero-initials { width: 56px; height: 56px; border-radius: 8px; background: #0f3460; color: #a8ff78; font-size: 20px; font-weight: bold; text-align: center; line-height: 56px; }
    .hero-info-cell { display: table-cell; vertical-align: top; padding-left: 14px; }
    .hero-name { font-size: 18px; font-weight: bold; color: #0f3460; }
    .hero-headline { font-size: 11px; color: #334; margin-top: 3px; }
    .hero-location { font-size: 10px; color: #777; margin-top: 2px; }
    .hero-gender { font-size: 10px; color: #999; margin-top: 1px; text-transform: capitalize; }
    .hero-exp { font-size: 10px; color: #0f3460; margin-top: 4px; font-weight: bold; }

    .section { margin: 0 24px 18px; }
    .section-title {
      font-size: 10px; font-weight: bold; text-transform: uppercase;
      letter-spacing: 1.5px; color: #0f3460;
      border-bottom: 2px solid #a8ff78; padding-bottom: 4px; margin-bottom: 8px;
    }

    table { width: 100%; border-collapse: collapse; }
    tr:nth-child(odd)  td { background: #f8faff; }
    tr:nth-child(even) td { background: #fff; }
    td { padding: 6px 12px; border-bottom: 1px solid #e8eaf0; vertical-align: top; }
    .td-key { width: 38%; font-weight: bold; color: #0f3460; font-size: 11px; }
    .td-val { color: #333; font-size: 11px; }

    .contact-type { display: inline-block; font-size: 8px; font-weight: bold; padding: 1px 5px; border-radius: 3px; margin-left: 5px; text-transform: uppercase; }
    .ct-corp { background: #dbeafe; color: #1d4ed8; }
    .ct-pers { background: #dcfce7; color: #166534; }

    .card { border: 1px solid #d0dce8; border-radius: 4px; margin-bottom: 10px; overflow: hidden; }
    .card-head { background: #0f3460; color: #a8ff78; font-weight: bold; font-size: 11px; padding: 6px 12px; }
    .card-sub  { font-size: 9px; color: #b0c4de; font-weight: normal; margin-top: 2px; }
    .card-body { padding: 8px 12px; font-size: 11px; color: #333; background: #f8faff; }
    .card-row  { margin-bottom: 4px; }
    .card-label { color: #0f3460; font-weight: bold; font-size: 10px; }

    .skills-wrap { padding: 4px 0; }
    .skill-pill { display: inline-block; background: #e8f0fe; color: #0f3460; font-size: 10px; padding: 3px 8px; border-radius: 12px; margin: 2px 3px 2px 0; border: 1px solid #c5d8fb; }
    .lang-pill  { display: inline-block; background: #f0fdf4; color: #166534; font-size: 10px; padding: 3px 8px; border-radius: 12px; margin: 2px 3px 2px 0; border: 1px solid #bbf7d0; }

    .summary-box { background: #f8faff; border-left: 4px solid #a8ff78; padding: 10px 14px; font-size: 11px; color: #333; line-height: 1.6; border-radius: 0 4px 4px 0; }

    .footer { margin-top: 24px; padding: 12px 24px; background: #0f3460; color: #b0c4de; font-size: 9px; }
    .footer-table { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-brand { color: #a8ff78; font-weight: bold; font-size: 11px; }
  </style>
</head>
<body>

@php
  // Safe scalar extractor — never passes an array to Blade's {{ }}
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

  $corpKeywords = ['work', 'corp', 'professional', 'business', 'office'];
  $isCorp = function($c) use ($corpKeywords) {
      $sub = strtolower($c['subType'] ?? '');
      foreach ($corpKeywords as $kw) {
          if (str_contains($sub, $kw)) return true;
      }
      return false;
  };

  $fmtDate = function($val) use ($s) {
      $str = $s($val);
      if (!$str) return '';
      if (is_numeric($str)) return $str;
      try { return date('M Y', strtotime($str)); } catch (\Throwable $e) { return $str; }
  };

  $contacts  = is_array($data['contacts']  ?? null) ? $data['contacts']  : [];
  $social    = is_array($data['social']    ?? null) ? $data['social']    : [];
  $skills    = is_array($data['skills']    ?? null) ? $data['skills']    : [];
  $experience= is_array($data['experience']?? null) ? $data['experience']: [];
  $education = is_array($data['education'] ?? null) ? $data['education'] : [];
  $languages = is_array($data['language']  ?? null) ? $data['language']  : [];
  $pubs      = is_array($data['publication']??null) ? $data['publication']: [];
  $certs     = is_array($data['certification']??null)?$data['certification']:[];

  $emails = array_filter($contacts, fn($c) => ($c['type'] ?? '') === 'email');
  $phones = array_filter($contacts, fn($c) => ($c['type'] ?? '') === 'phone');

  $locationStr = $s($data['locations'][0]['name'] ?? null)
              ?: $s($data['locations'][0]['display'] ?? null)
              ?: $s($data['addresses'][0]['display'] ?? null)
              ?: '';

  $initials = implode('', array_map(
      fn($w) => strtoupper($w[0] ?? ''),
      array_slice(explode(' ', $s($data['fullName'] ?? '', 'U')), 0, 2)
  ));

  $socialLabels = ['li'=>'LinkedIn','fb'=>'Facebook','tw'=>'Twitter / X','gh'=>'GitHub','ig'=>'Instagram'];
@endphp

  <div class="header">
    <div class="header-top">
      <div class="header-brand">
        <div class="app-name">{{ config('app.name', 'DRASHTA') }}</div>
        <div class="app-tagline">INTELLIGENCE PLATFORM</div>
      </div>
      <div class="header-meta">
        <div class="report-type">Social Intelligence Report</div>
        <div class="report-date">Generated: {{ now()->format('d M Y, H:i') }}</div>
      </div>
    </div>
  </div>
  <div class="accent-bar"></div>

  {{-- Profile hero --}}
  <div class="profile-hero">
    <div class="hero-photo-cell">
      @if (!empty($data['_photoBase64']))
        <img src="{{ $data['_photoBase64'] }}" class="hero-photo" alt="photo" />
      @else
        <div class="hero-initials">{{ $initials }}</div>
      @endif
    </div>
    <div class="hero-info-cell">
      <div class="hero-name">{{ $s($data['fullName'] ?? '', 'Unknown') }}</div>
      @if ($s($data['headLine'] ?? ''))
        <div class="hero-headline">{{ $s($data['headLine']) }}</div>
      @endif
      @if ($locationStr)
        <div class="hero-location">{{ $locationStr }}</div>
      @endif
      @if ($s($data['gender'] ?? ''))
        <div class="hero-gender">{{ $s($data['gender']) }}</div>
      @endif
      @if ($s($data['experienceYears'] ?? ''))
        <div class="hero-exp">{{ $s($data['experienceYears']) }} years of experience</div>
      @endif
    </div>
  </div>

  {{-- Contacts --}}
  @if (count($contacts) > 0)
  <div class="section">
    <div class="section-title">Contact Information</div>
    <table>
      @foreach ($emails as $c)
        @php $corp = $isCorp($c); $val = $s($c['value'] ?? ''); @endphp
        @if ($val)
        <tr>
          <td class="td-key">
            {{ $corp ? 'Corporate Email' : 'Personal Email' }}
            @if ($s($c['subType'] ?? ''))
              <span class="contact-type {{ $corp ? 'ct-corp' : 'ct-pers' }}">{{ $s($c['subType']) }}</span>
            @endif
          </td>
          <td class="td-val">{{ $val }}</td>
        </tr>
        @endif
      @endforeach
      @foreach ($phones as $c)
        @php $corp = $isCorp($c); $val = $s($c['value'] ?? ''); @endphp
        @if ($val)
        <tr>
          <td class="td-key">
            {{ $corp ? 'Corporate Phone' : 'Personal Phone' }}
            @if ($s($c['subType'] ?? ''))
              <span class="contact-type {{ $corp ? 'ct-corp' : 'ct-pers' }}">{{ $s($c['subType']) }}</span>
            @endif
          </td>
          <td class="td-val">{{ $val }}</td>
        </tr>
        @endif
      @endforeach
    </table>
  </div>
  @endif

  {{-- Social Links --}}
  @if (count($social) > 0)
  <div class="section">
    <div class="section-title">Social Profiles</div>
    <table>
      @foreach ($social as $sv)
        @php
          $typeKey = strtolower($s($sv['type'] ?? $sv['name'] ?? ''));
          $label   = $socialLabels[$typeKey] ?? ucfirst($typeKey) ?: 'Social';
          $url     = $s($sv['url'] ?? $sv['link'] ?? '');
        @endphp
        @if ($url)
        <tr>
          <td class="td-key">{{ $label }}</td>
          <td class="td-val">{{ $url }}</td>
        </tr>
        @endif
      @endforeach
    </table>
  </div>
  @endif

  {{-- Summary --}}
  @if ($s($data['summary'] ?? $data['bio'] ?? ''))
  <div class="section">
    <div class="section-title">Summary</div>
    <div class="summary-box">{{ $s($data['summary'] ?? $data['bio'] ?? '') }}</div>
  </div>
  @endif

  {{-- Skills --}}
  @if (count($skills) > 0)
  <div class="section">
    <div class="section-title">Skills</div>
    <div class="skills-wrap">
      @foreach ($skills as $skill)
        @php $sv = $s($skill); @endphp
        @if ($sv)<span class="skill-pill">{{ $sv }}</span>@endif
      @endforeach
    </div>
  </div>
  @endif

  {{-- Work Experience --}}
  @if (count($experience) > 0)
  <div class="section">
    <div class="section-title">Work Experience</div>
    @foreach ($experience as $job)
      @php
        $title   = $s($job['position'] ?? $job['title'] ?? '');
        $company = $s($job['company'] ?? '');
        $start   = $fmtDate($job['started'] ?? $job['startDate'] ?? null);
        $end     = $fmtDate($job['ended']   ?? $job['endDate']   ?? null);
        $curr    = !empty($job['current']);
        $loc     = $s($job['location'] ?? '');
        $desc    = $s($job['description'] ?? '');
      @endphp
      <div class="card">
        <div class="card-head">
          {{ $title ?: 'Role' }}
          @if ($company)<span class="card-sub">&nbsp;at {{ $company }}</span>@endif
        </div>
        <div class="card-body">
          @if ($start || $end || $curr)
            <div class="card-row"><span class="card-label">Period: </span>
              {{ $start ?: '?' }} &mdash; {{ $curr ? 'Present' : ($end ?: '?') }}
            </div>
          @endif
          @if ($loc)
            <div class="card-row"><span class="card-label">Location: </span>{{ $loc }}</div>
          @endif
          @if ($desc)
            <div class="card-row" style="margin-top:4px; color:#555; font-size:10px; line-height:1.5;">{{ $desc }}</div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- Education --}}
  @if (count($education) > 0)
  <div class="section">
    <div class="section-title">Education</div>
    @foreach ($education as $edu)
      @php
        $inst   = $s($edu['university'] ?? $edu['school'] ?? '');
        $field  = $s($edu['faculty']    ?? $edu['field']  ?? '');
        $degree = $s($edu['degree'] ?? '');
        $from   = $s($edu['startedYear'] ?? '');
        $to     = $s($edu['endedYear']   ?? '');
      @endphp
      <div class="card">
        <div class="card-head">
          {{ $inst ?: 'Institution' }}
          @if ($field)<span class="card-sub">&nbsp;— {{ $field }}</span>@endif
        </div>
        <div class="card-body">
          @if ($degree)
            <div class="card-row"><span class="card-label">Degree: </span>{{ $degree }}</div>
          @endif
          @if ($from || $to)
            <div class="card-row"><span class="card-label">Period: </span>
              {{ $from ?: '?' }} &mdash; {{ $to ?: 'Present' }}
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- Languages --}}
  @if (count($languages) > 0)
  <div class="section">
    <div class="section-title">Languages</div>
    <div class="skills-wrap">
      @foreach ($languages as $lang)
        @php
          $name  = $s(is_array($lang) ? ($lang['name'] ?? '') : $lang);
          $level = $s(is_array($lang) ? ($lang['level'] ?? '') : '');
        @endphp
        @if ($name)<span class="lang-pill">{{ $name }}{{ $level ? ' (' . $level . ')' : '' }}</span>@endif
      @endforeach
    </div>
  </div>
  @endif

  {{-- Publications --}}
  @if (count($pubs) > 0)
  <div class="section">
    <div class="section-title">Publications</div>
    @foreach ($pubs as $pub)
      @php
        $title = $s($pub['title'] ?? '');
        $publisher = $s($pub['issue'] ?? $pub['publisher'] ?? '');
        $date  = $s($pub['date'] ?? '');
        $desc  = $s($pub['description'] ?? '');
      @endphp
      <div class="card">
        <div class="card-head">{{ $title ?: 'Publication' }}</div>
        <div class="card-body">
          @if ($publisher)<div class="card-row"><span class="card-label">Publisher: </span>{{ $publisher }}</div>@endif
          @if ($date)<div class="card-row"><span class="card-label">Date: </span>{{ $date }}</div>@endif
          @if ($desc)<div class="card-row" style="color:#555; font-size:10px; line-height:1.5;">{{ $desc }}</div>@endif
        </div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- Certifications --}}
  @if (count($certs) > 0)
  <div class="section">
    <div class="section-title">Certifications</div>
    @foreach ($certs as $cert)
      @php
        $name = $s($cert['name'] ?? $cert['title'] ?? '');
        $auth = $s($cert['authority'] ?? '');
        $date = $s($cert['date'] ?? $cert['startDate'] ?? '');
      @endphp
      <div class="card">
        <div class="card-head">{{ $name ?: 'Certification' }}</div>
        <div class="card-body">
          @if ($auth)<div class="card-row"><span class="card-label">Authority: </span>{{ $auth }}</div>@endif
          @if ($date)<div class="card-row"><span class="card-label">Date: </span>{{ $date }}</div>@endif
        </div>
      </div>
    @endforeach
  </div>
  @endif

  <div class="footer">
    <div class="footer-table">
      <div class="footer-left">
        <span class="footer-brand">{{ config('app.name', 'DRASHTA') }}</span> &nbsp;&middot;&nbsp; Confidential Intelligence Report
      </div>
      <div class="footer-right">
        Downloaded by: {{ $userEmail }} &nbsp;&middot;&nbsp; {{ now()->format('d M Y H:i:s') }}
      </div>
    </div>
  </div>

</body>
</html>
