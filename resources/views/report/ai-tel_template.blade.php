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


    <div class="footer">
        This report was automatically generated using AI (Gemini) via {{ config('app.name') }} on
        {{ now()->format('Y-m-d') }}.
    </div>

</body>

</html>