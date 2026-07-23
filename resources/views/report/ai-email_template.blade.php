<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>AI Email Intelligence Report</title>
  @include('report.partials.theme')
  <style>
    body { color: #2d3748; line-height: 1.6; }
    .content { padding: 0 24px; }
    .section { margin-bottom: 20px; page-break-inside: avoid; }
    .section-title { margin-bottom: 10px; margin-top: 20px; }
    h2 { font-size: 14px; font-weight: bold; color: #0f3460; margin-bottom: 8px; margin-top: 16px; border-left: 4px solid #a8ff78; padding-left: 10px; }
    .highlight { background: #f0fdf4; padding: 12px 15px; border-left: 6px solid #a8ff78; border-radius: 6px; color: #1a2e1a; margin-top: 8px; }
    .risk { background: #fff1f2; padding: 12px 15px; border-left: 6px solid #e53e3e; border-radius: 6px; color: #4a5568; margin-top: 8px; }
    .next-steps { background: #eaf4fb; border-left: 6px solid #0f3460; border-radius: 6px; padding: 4px 0; margin-top: 8px; margin-left: 0; }
    .next-steps li { margin-bottom: 6px; margin-left: 20px; }
    ul, ol { padding-left: 20px; }
    li::marker { color: #0f3460; }
    ul li { margin-bottom: 6px; }
    p { margin-bottom: 8px; }
    .confidence-score { background-color: #eaf4fb; color: #0f3460; font-weight: bold; display: inline-block; padding: 8px 12px; border-left: 5px solid #a8ff78; border-radius: 6px; font-size: 14px; }
    .social-summary, .data-freshness { background-color: #f8faff; padding: 10px 15px; border-left: 5px solid #0f3460; border-radius: 6px; color: #333; margin-top: 8px; }
    .anomalies ul { list-style-type: square; color: #e53e3e; }
    .disclaimer-box { background: #eaf4fb; border-left: 4px solid #a8ff78; padding: 10px 14px; font-size: 10px; color: #333; line-height: 1.6; border-radius: 0 4px 4px 0; margin-top: 20px; margin-bottom: 20px; }
    .disclaimer-title { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; color: #0f3460; border-bottom: 2px solid #a8ff78; padding-bottom: 4px; margin-bottom: 8px; }
    .disclaimer-box ol { padding-left: 16px; }
    .disclaimer-box li { margin-bottom: 4px; }
  </style>
</head>
<body>

@include('report.partials.header', ['reportType' => 'AI Email Intelligence Report'])

<div class="content">

  {{-- ── Confidential Disclaimer ── --}}
  <div class="section" style="margin-top:16px;">
    <div class="section-title">CONFIDENTIAL – FOR AUTHORIZED LAW ENFORCEMENT PERSONNEL ONLY</div>
    <p style="font-size:11px;">This report is intended strictly for legitimate investigative use by authorized law enforcement officers, in accordance with applicable Indian laws and regulations. It contains intelligence derived solely from publicly accessible sources and licensed investigative tools. No unauthorized, leaked, or unlawfully obtained data is included.</p>
    <p style="font-size:11px;">Distribution of this report through unauthorized channels—including but not limited to WhatsApp, Telegram, email groups, or other social media platforms—is strictly prohibited.</p>
    <p style="font-size:11px;">All information contained herein must be handled with utmost confidentiality and used in full compliance with applicable legal frameworks, including the <strong>Information Technology Act, 2000</strong>, and the <strong>Digital Personal Data Protection Act, 2023</strong> (upon its enforcement).</p>
    <p style="font-size:11px;">Law enforcement personnel are solely responsible for ensuring that any use of this information is supported by appropriate legal authorization or explicit consent from the data subject, as required.</p>
    <p style="font-size:11px;"><strong>{{ config('app.name', 'DRASHTA') }}</strong> operates solely as a technical intermediary and does not store or retain any personal data. It assumes no liability for the unauthorized use, distribution, or interpretation of the information contained in this report.</p>
  </div>

  {{-- ── Report Meta ── --}}
  <div class="section">
    <p><strong>Input:</strong> {{ $userInput }}</p>
    <p><strong>Type:</strong> {{ ucfirst($type) }}</p>
    <p><strong>Generated At:</strong> {{ $generation_time }}</p>
  </div>

  @if($summary)
    <div class="section">
      <h2>[Summary] Intelligence Summary</h2>
      <div class="highlight">{{ $summary }}</div>
    </div>
  @endif

  @if($riskLevel)
    <div class="section">
      <h2>[Risk] Risk Analysis</h2>
      <div class="risk">{{ $riskLevel }}</div>
    </div>
  @endif

  @if(!empty($nextSteps))
    <div class="section">
      <h2>[Next Steps] Recommended Next Steps</h2>
      <ol class="next-steps">
        @foreach($nextSteps as $step)
          <li>{{ $step }}</li>
        @endforeach
      </ol>
    </div>
  @endif

  @if(!empty($profileHighlights))
    <div class="section">
      <h2>[Highlights] Profile Highlights</h2>
      <ul>
        @foreach($profileHighlights as $item)
          <li>{{ $item }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if($confidenceScore !== null)
    <div class="section">
      <h2>[Score] Confidence Score</h2>
      <p><span class="confidence-score">{{ $confidenceScore }}%</span></p>
    </div>
  @endif

  @if(!empty($anomalies))
    <div class="section anomalies">
      <h2>[Anomalies] Anomalies</h2>
      <ul>
        @foreach($anomalies as $a)
          <li>{{ $a }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if($socialPresenceSummary)
    <div class="section social-summary">
      <h2>[Social Summary] Social Media Summary</h2>
      <p>{{ $socialPresenceSummary }}</p>
    </div>
  @endif

  @if($dataFreshness)
    <div class="section data-freshness">
      <h2>[Data Freshness] Data Freshness</h2>
      <p>{{ $dataFreshness }}</p>
    </div>
  @endif

  {{-- ── Legal Disclaimer ── --}}
  <div class="disclaimer-box">
    <div class="disclaimer-title">Legal Disclaimer</div>
    <p style="margin-bottom:6px;">This AI Intelligence report has been prepared by <strong>{{ config('app.name', 'DRASHTA') }}</strong>, acting solely as a technical intermediary, for the exclusive use of authorized personnel. Information is gathered from publicly accessible sources and legally verified digital tools.</p>
    <ol>
      <li><strong>Authorized Use:</strong> Intended solely for duly authorized personnel for legitimate investigative purposes under Indian law.</li>
      <li><strong>Legal Compliance:</strong> Must comply with the IT Act 2000, BNS, BNSS, BSA, Article 21, and the DPDP Act 2023.</li>
      <li><strong>Verification Required:</strong> Data must be independently verified before use in legal proceedings.</li>
      <li><strong>Confidentiality:</strong> Strictly confidential — do not share beyond the scope of the official investigation.</li>
      <li><strong>Limited Liability:</strong> {{ config('app.name', 'DRASHTA') }} assumes no liability for use, misuse, or interpretation of information herein.</li>
      <li><strong>No Legal Advice:</strong> This report does not constitute legal advice. Consult your legal counsel before use in proceedings.</li>
    </ol>
  </div>

</div>

@include('report.partials.footer')

</body>
</html>
