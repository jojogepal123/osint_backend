<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>AI OSINT Report</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            font-size: 14px;
            color: #2d3748;
            line-height: 1.6;
            margin: 40px;
            background-color: #fff;
        }

        h1 {
            font-size: 24px;
            color: #1a202c;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
            background-color: #f9fafb;
        }

        h2 {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 8px;
            margin-top: 30px;
            border-left: 4px solid #3182ce;
            padding-left: 10px;
            background-color: #f9fafb;
        }

        .section {
            margin-bottom: 30px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
            page-break-inside: avoid;
        }

        .highlight {
            background: #fefcbf;
            padding: 15px;
            border-left: 6px solid #ecc94b;
            border-radius: 8px;
            color: #4a5568;
        }

        .risk {
            background: #fed7d7;
            padding: 15px;
            border-left: 6px solid #e53e3e;
            border-radius: 8px;
            color: #4a5568;
        }

        .next-steps {
            background: #e6fffa;
            border-left: 6px solid #38b2ac;
            border-radius: 8px;
            padding: 0px;
            margin-top: 10px;
            margin-left: 0;
        }

        .next-steps li {
            margin-bottom: 6px;
            margin-left: 20px;
        }

        ul,
        ol {
            padding-left: 20px;
        }

        li::marker {
            color: #4299e1;
        }

        ul li {
            margin-bottom: 6px;
        }

        .footer {
            font-size: 12px;
            color: #718096;
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
        }

        p {
            margin-bottom: 10px;
        }

        .confidence-score {
            background-color: #ebf8ff;
            color: #2b6cb0;
            font-weight: bold;
            display: inline-block;
            padding: 8px 12px;
            border-left: 5px solid #4299e1;
            border-radius: 6px;
            font-size: 16px;
        }

        .social-summary,
        .data-freshness {
            background-color: #f7fafc;
            padding: 10px 15px;
            border-left: 5px solid #cbd5e0;
            border-radius: 6px;
            color: #4a5568;
            margin-top: 10px;
        }

        .anomalies ul {
            list-style-type: square;
            color: #e53e3e;
        }
    </style>
</head>

<body>
    <div class="section highlight" style="border-left: 6px solid #f59e0b;">
        <h2 style="color: #b45309;">CONFIDENTIAL – FOR AUTHORIZED LAW ENFORCEMENT PERSONNEL ONLY</h2>
        <p>This report is intended strictly for legitimate investigative use by authorized law enforcement officers, in
            accordance with applicable Indian laws and regulations. It contains intelligence derived solely from
            publicly accessible sources and licensed investigative tools. No unauthorized, leaked, or unlawfully
            obtained data is included.</p>
        <p>Distribution of this report through unauthorized channels—including but not limited to WhatsApp, Telegram,
            email groups, or other social media platforms—is strictly prohibited.</p>
        <p>All information contained herein must be handled with utmost confidentiality and used in full compliance with
            applicable legal frameworks, including the <strong>Information Technology Act, 2000</strong>, and the
            <strong>Digital Personal Data Protection Act, 2023</strong> (upon its enforcement).
        </p>
        <p>Law enforcement personnel are solely responsible for ensuring that any use of this information is supported
            by appropriate legal authorization or explicit consent from the data subject, as required.</p>
        <p><strong>OSINTWORK</strong> operates solely as a technical intermediary and does not store or retain any
            personal data. It assumes no liability for the unauthorized use, distribution, or interpretation of the
            information contained in this report.</p>
    </div>
    <h1>AI OSINT Report</h1>

    <div class="section">
        <p><strong>Input:</strong> {{ $userInput }}</p>
        <p><strong>Type:</strong> {{ ucfirst($type) }}</p>
        <p><strong>Generated At:</strong> {{ $generation_time }}</p>
    </div>

    @if($summary)
        <div class="section">
            <h2>[Summary] Intelligence Summary</h2>
            <div class="highlight">
                {{ $summary }}
            </div>
        </div>
    @endif

    @if($riskLevel)
        <div class="section">
            <h2>[Risk] Risk Analysis</h2>
            <div class="risk">
                {{ $riskLevel }}
            </div>
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
            <p>{{ $confidenceScore }}%</p>
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
    <div class="legal-disclaimer">
        <h2>LEGAL DISCLAIMER FOR OSINT REPORT</h2>
        <p>This Open Source Intelligence (OSINT) report has been prepared by <strong>OSINTWORK</strong>, a private
            entity
            acting solely as a technical intermediary, at the request of, and for the exclusive use of, authorized law
            enforcement agencies. The information contained in this report has been gathered strictly from publicly
            accessible sources and legally verified digital tools as of the date of its generation. No leaked,
            unauthorized,
            or unlawfully obtained data has been used in its preparation.</p>
        <p><strong>Use of this report is governed by the following conditions:</strong></p>
        <ol>
            <li><strong>Authorized Use:</strong> This report is intended solely for use by duly authorized law
                enforcement
                officers for legitimate investigative purposes, as defined under Indian law. The requesting agency
                assumes
                full responsibility for the lawful, ethical, and appropriate use of the information contained herein.
            </li>
            <li><strong>Legal Compliance:</strong> Use of this report must be in strict compliance with all applicable
                Indian laws and regulations, including but not limited to:
                <ul>
                    <li>The Information Technology Act, 2000</li>
                    <li>The Bharatiya Nyaya Sanhita (BNS)</li>
                    <li>The Bharatiya Nagarik Suraksha Sanhita (BNSS)</li>
                    <li>The Bharatiya Sakshya Adhiniyam (BSA)</li>
                    <li>Relevant constitutional protections, including Article 21 pertaining to the Right to Privacy
                    </li>
                    <li>The Digital Personal Data Protection Act, 2023 (upon its enforcement)</li>
                </ul>
            </li>
            <li><strong>Data Protection and Retention:</strong> OSINTWORK compiles this report using ethical and legally
                compliant OSINT methodologies. No personal data is stored post-transmission. The requesting law
                enforcement
                agency bears sole responsibility for ensuring compliance with relevant data protection regulations and
                internal data handling policies.</li>
            <li><strong>Verification Requirement:</strong> The information in this report is derived from OSINT
                techniques
                and should be treated as preliminary intelligence. It must be independently verified by the requesting
                agency through official and legally admissible channels prior to being used in legal proceedings or
                enforcement actions.</li>
            <li><strong>Confidentiality:</strong> This report is strictly confidential and may not be disclosed, shared,
                or
                disseminated beyond the scope of the official investigation for which it was requested. The agency is
                responsible for maintaining the confidentiality of this document and limiting access to authorized
                personnel
                only.</li>
            <li><strong>Limited Liability:</strong> OSINTWORK shall not be held liable for any direct or indirect
                consequences arising from the use, misuse, or interpretation of the information provided herein.</li>
            <li><strong>No Legal Advice:</strong> This report does not constitute legal advice. The recipient agency
                must
                consult its own legal counsel for lawful and appropriate use.</li>
            <li><strong>Ethical Use:</strong> The receiving agency is expected to ensure that the information is used in
                a
                manner consistent with ethical law enforcement practices.</li>
            <li><strong>Contractual Obligations:</strong> Use is subject to terms in the service agreement or MoU
                between
                OSINTWORK and the requesting agency.</li>
        </ol>
        <p>By accepting and utilizing this report, the law enforcement agency affirms its agreement with these terms and
            acknowledges that OSINTWORK acts strictly as an intermediary.</p>
    </div>


    <div class="footer">
        This report was automatically generated via {{ config('app.name') }} on
        {{ now()->format('Y-m-d') }}.
    </div>

</body>

</html>