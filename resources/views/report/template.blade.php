<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - {{ $data['userInput'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #1A1F30;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #313544;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card {
            background: #313544;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        .card-header img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
        }

        .card-title {
            font-size: 1.5em;
            margin: 0;
            color: white;
        }

        .card-inner {
            background: #2A2F3D;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .data-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .label {
            font-weight: bold;
            color: #8B8B8B;
        }

        .value {
            color: white;
        }

        .profile-image {
            max-width: 200px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .section-title {
            color: #ABDE64;
            font-size: 1.2em;
            margin: 20px 0 10px 0;
        }

        .social-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .social-link {
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
        }

        .social-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Search Results for: {{ $data['userInput'] }}</h1>
            <p>Search Type: {{ $data['type'] }}</p>
            <p>Generated on: {{ \Carbon\Carbon::now()->toDayDateTimeString() }}</p>
        </div>

        @if($data['type'] == 'tel')
            @if(isset($data['results']['whatsappData']))
                <div class="card">
                    <div class="card-header">
                        <img src="https://static.whatsapp.net/rsrc.php/v3/yP/r/rYZqPCBaG70.png" alt="WhatsApp">
                        <h2 class="card-title">WhatsApp Data</h2>
                    </div>
                    <div>
                        @if(!empty($data['results']['whatsappData']['profilePic']))
                            <img class="profile-image" src="{{ $data['results']['whatsappData']['profilePic'] }}"
                                alt="Profile Picture">
                        @endif
                    </div>
                    <div class="data-grid">
                        <div class="label">Number:</div>
                        <div class="value">{{ $data['results']['whatsappData']['number'] ?? 'N/A' }}</div>
                        <div class="label">Country Code:</div>
                        <div class="value">{{ $data['results']['whatsappData']['countryCode'] ?? 'N/A' }}</div>
                        <div class="label">Business Account:</div>
                        <div class="value">{{ !empty($data['results']['whatsappData']['isBusiness']) ? 'Yes' : 'No' }}</div>
                        <div class="label">Username:</div>
                        <div class="value">{{ $data['results']['whatsappData']['pushname'] ?? 'N/A' }}</div>
                        <div class="label">Is User:</div>
                        <div class="value">{{ !empty($data['results']['whatsappData']['isUser']) ? 'Yes' : 'No' }}</div>
                    </div>
                </div>
            @endif
            @if(isset($data['results']['hlrData']))
                <div class="card">
                    <div class="card-header">
                        <img src="https://cdn-icons-png.flaticon.com/512/1055/1055329.png" alt="ISP">
                        <h2 class="card-title">ISP Lookup</h2>
                    </div>
                    <div class="data-grid">
                        <div class="label">ID:</div>
                        <div class="value">{{ $data['results']['hlrData']['id'] ?? 'N/A' }}</div>

                        <div class="label">MSISDN:</div>
                        <div class="value">{{ $data['results']['hlrData']['msisdn'] ?? 'N/A' }}</div>

                        <div class="label">MCCMNC:</div>
                        <div class="value">{{ $data['results']['hlrData']['mccmnc'] ?? 'N/A' }}</div>

                        <div class="label">Connectivity Status:</div>
                        <div class="value">{{ $data['results']['hlrData']['connectivity_status'] ?? 'N/A' }}</div>

                        <div class="label">MCC:</div>
                        <div class="value">{{ $data['results']['hlrData']['mcc'] ?? 'N/A' }}</div>

                        <div class="label">MNC:</div>
                        <div class="value">{{ $data['results']['hlrData']['mnc'] ?? 'N/A' }}</div>

                        <div class="label">IMSI:</div>
                        <div class="value">{{ $data['results']['hlrData']['imsi'] ?? 'N/A' }}</div>

                        <div class="label">MSIN:</div>
                        <div class="value">{{ $data['results']['hlrData']['msin'] ?? 'N/A' }}</div>

                        <div class="label">MSC:</div>
                        <div class="value">{{ $data['results']['hlrData']['msc'] ?? 'N/A' }}</div>

                        <div class="label">Original Network Name:</div>
                        <div class="value">{{ $data['results']['hlrData']['original_network_name'] ?? 'N/A' }}</div>

                        <div class="label">Original Country Name:</div>
                        <div class="value">{{ $data['results']['hlrData']['original_country_name'] ?? 'N/A' }}</div>

                        <div class="label">Original Country Code:</div>
                        <div class="value">{{ $data['results']['hlrData']['original_country_code'] ?? 'N/A' }}</div>

                        <div class="label">Original Country Prefix:</div>
                        <div class="value">{{ $data['results']['hlrData']['original_country_prefix'] ?? 'N/A' }}</div>

                        <div class="label">Is Ported:</div>
                        <div class="value">{{ !empty($data['results']['hlrData']['is_ported']) ? 'Yes' : 'No' }}</div>

                        <div class="label">Cost:</div>
                        <div class="value">{{ $data['results']['hlrData']['cost'] ?? 'N/A' }}</div>

                        <div class="label">Timestamp:</div>
                        <div class="value">{{ $data['results']['hlrData']['timestamp'] ?? 'N/A' }}</div>

                        <div class="label">Storage:</div>
                        <div class="value">{{ $data['results']['hlrData']['storage'] ?? 'N/A' }}</div>

                        <div class="label">Route:</div>
                        <div class="value">{{ $data['results']['hlrData']['route'] ?? 'N/A' }}</div>

                        <div class="label">Processing Status:</div>
                        <div class="value">{{ $data['results']['hlrData']['processing_status'] ?? 'N/A' }}</div>

                        <div class="label">Source:</div>
                        <div class="value">{{ $data['results']['hlrData']['data_source'] ?? 'N/A' }}</div>
                    </div>
                </div>
            @endif
            @if(isset($data['results']['truecallerData']))
                <div class="card">
                    <div class="card-header">
                        <img src="https://www.truecaller.com/pwa-192x192.png" alt="Truecaller">
                        <h2 class="card-title">Truecaller Data</h2>
                    </div>

                    @if (!empty($data['results']['truecallerData']['data']['addressInfo']))
                        <div class="section-title">Address Information</div>
                        <div class="data-grid">
                            <div class="label">Street:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['addressInfo']['street'] ?? 'N/A' }}
                            </div>

                            <div class="label">Address:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['addressInfo']['address'] ?? 'N/A' }}
                            </div>

                            <div class="label">City:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['addressInfo']['city'] ?? 'N/A' }}
                            </div>

                            <div class="label">Country Code:</div>
                            <div class="value">
                                {{ $data['results']['truecallerData']['data']['addressInfo']['countryCode'] ?? 'N/A' }}
                            </div>

                            <div class="label">Time Zone:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['addressInfo']['timeZone'] ?? 'N/A' }}
                            </div>
                        </div>
                    @endif

                    @if (!empty($data['results']['truecallerData']['data']['phoneInfo']))
                        <div class="section-title">Phone Information</div>
                        <div class="data-grid">
                            <div class="label">E.164 Format:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['phoneInfo']['e164Format'] ?? 'N/A' }}
                            </div>

                            <div class="label">National Format:</div>
                            <div class="value">
                                {{ $data['results']['truecallerData']['data']['phoneInfo']['nationalFormat'] ?? 'N/A' }}
                            </div>

                            <div class="label">Number Type:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['phoneInfo']['numberType'] ?? 'N/A' }}
                            </div>

                            <div class="label">Carrier:</div>
                            <div class="value">{{ $data['results']['truecallerData']['data']['phoneInfo']['carrier'] ?? 'N/A' }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if (!empty($data['results']['socialMediaData']))
                <div class="card">
                    <div class="card-header">
                        <img src="https://www.facebook.com/favicon.ico" alt="Facebook">
                        <h2 class="card-title">Social Media Data</h2>
                    </div>
                    @if (!empty($data['results']['socialMediaData']['response']['fb']['profile_picture_url']))
                        <div>
                            <img class="profile-image" style="width: 200px; height: 200px;"
                                src="{{ $data['results']['socialMediaData']['response']['fb']['profile_picture_url'] }}"
                                alt="Profile Picture">
                        </div>
                    @endif
                    <div class="data-grid">
                        <div class="label">Name:</div>
                        <div class="value">{{ $data['results']['socialMediaData']['response']['name'] ?? 'N/A' }}</div>
                        <div class="label">Facebook ID:</div>
                        <div class="value">{{ $data['results']['socialMediaData']['response']['fb']['id'] ?? 'N/A' }}</div>
                        @if (!empty($data['results']['socialMediaData']['response']['fb']['id']))
                            <div class="label">Profile URL:</div>
                            <div class="value">
                                <a href="{{ $data['results']['socialMediaData']['response']['fb']['profile_url'] }}"
                                    class="social-link" target="_blank">
                                    View Profile
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($data['results']['osintData']))
                <div class="card">
                    <div class="card-header">
                        <img src="https://cdn-icons-png.flaticon.com/512/1055/1055329.png" alt="OSINT" width="40" height="40" />
                        <h2 class="card-title">OSINT Results</h2>
                    </div>

                    @if (!empty($data['results']['osintData']))
                        @foreach ($data['results']['osintData'] as $item)
                            <div class="card-inner">
                                <div class="data-grid">
                                    @foreach ($item as $key => $value)
                                        @if (!is_array($value) && !is_object($value))
                                            <div class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                                            <div class="value">{{ $value ?: 'N/A' }}</div>
                                        @elseif (is_array($value))
                                            <div class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                                            <div class="value">
                                                @foreach ($value as $v)
                                                    @if (is_array($v))
                                                        @foreach ($v as $subKey => $subVal)
                                                            <span style="display:block;"><strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong>
                                                                {{ $subVal }}</span>
                                                        @endforeach
                                                    @else
                                                        <span style="display:block;">{{ $v }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif (is_object($value))
                                            <div class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                                            <div class="value">
                                                @foreach (get_object_vars($value) as $subKey => $subVal)
                                                    <span style="display:block;"><strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong>
                                                        {{ $subVal }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif

            @if (!empty($data['results']['allMobileData']))
                <div class="card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 5v14"></path>
                            <path d="M5 21h14"></path>
                            <path d="M5 21a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2"></path>
                            <path d="M9 7h1"></path>
                            <path d="M9 11h1"></path>
                            <path d="M9 15h1"></path>
                        </svg>
                        <h2 class="card-title">Alias</h2>
                    </div>

                    @if (!empty($data['results']['allMobileData']['truecaller']))
                        <div class="section-title">Truecaller Data</div>
                        <div class="data-grid">
                            <div class="label">Country:</div>
                            <div class="value">{{ $data['results']['allMobileData']['truecaller']['country'] ?? 'N/A' }}</div>

                            <div class="label">Number:</div>
                            <div class="value">{{ $data['results']['allMobileData']['truecaller']['number'] ?? 'N/A' }}</div>

                            <div class="label">Provider:</div>
                            <div class="value">{{ $data['results']['allMobileData']['truecaller']['provider'] ?? 'N/A' }}</div>

                            <div class="label">Number Type:</div>
                            <div class="value">{{ $data['results']['allMobileData']['truecaller']['number_type_label'] ?? 'N/A' }}
                            </div>

                            <div class="label">Country Code:</div>
                            <div class="value">{{ $data['results']['allMobileData']['truecaller']['country_code'] ?? 'N/A' }}</div>

                            @if (!empty($data['results']['allMobileData']['truecaller']['time_zones']))
                                <div class="label">Time Zone:</div>
                                <div class="value">{{ $data['results']['allMobileData']['truecaller']['time_zones'][0] ?? 'N/A' }}</div>
                            @endif
                        </div>
                    @endif

                    @if (!empty($data['results']['allMobileData']['callapp']))
                        <div class="section-title">CallApp Data</div>
                        <div class="data-grid">
                            <div class="label">Name:</div>
                            <div class="value">{{ $data['results']['allMobileData']['callapp']['name'] ?? 'N/A' }}</div>
                        </div>
                    @endif

                    @if (!empty($data['results']['allMobileData']['viewcaller'][0]))
                        <div class="section-title">ViewCaller Data</div>
                        <div class="data-grid">
                            <div class="label">Name:</div>
                            <div class="value">{{ $data['results']['allMobileData']['viewcaller'][0]['name'] ?? 'N/A' }}</div>
                        </div>
                    @endif

                    @if (!empty($data['results']['allMobileData']['eyecon']))
                        <div class="section-title">Eyecon Data</div>
                        <div class="data-grid">
                            <div class="label">Name:</div>
                            <div class="value">{{ $data['results']['allMobileData']['eyecon'] ?? 'N/A' }}</div>
                        </div>
                    @endif
                </div>
            @endif


            @if (!empty($data['results']['surepassKyc']['data']))
                <div class="card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="2" x2="22" y1="12" y2="12" />
                            <path
                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                        </svg>
                        <h2 class="card-title">KYC Data</h2>
                    </div>

                    <div class="data-grid">
                        <div class="label">Mobile:</div>
                        <div class="value">{{ $data['results']['surepassKyc']['data']['mobile'] ?? 'N/A' }}</div>
                    </div>

                    <div class="data-grid">
                        @php
                            $details = $data['results']['surepassKyc']['data']['details'] ?? [];
                        @endphp

                        @foreach ($details as $key => $value)
                            @php
                                $isValid = !is_null($value) && $value !== '' &&
                                    !(is_array($value) && count($value) === 0) &&
                                    !(is_object($value) && empty((array) $value));
                            @endphp

                            @if ($isValid)
                                <div class="label">{{ str_replace('_', ' ', $key) }}:</div>
                                <div class="value">
                                    @if (is_array($value))
                                        @foreach ($value as $item)
                                            @if (is_array($item) || is_object($item))
                                                @foreach ((array) $item as $subKey => $subVal)
                                                    @if (!is_null($subVal) && $subVal !== '')
                                                        <span style="display:block;"><strong>{{ str_replace('_', ' ', $subKey) }}:</strong>
                                                            {{ $subVal }}</span>
                                                    @endif
                                                @endforeach
                                                <hr style="border:0;border-top:1px solid #444;margin:4px 0;">
                                            @else
                                                {{ $item }}
                                            @endif
                                        @endforeach
                                    @elseif (is_object($value))
                                        @foreach ((array) $value as $subKey => $subVal)
                                            @if (!is_null($subVal) && $subVal !== '')
                                                <span style="display:block;"><strong>{{ str_replace('_', ' ', $subKey) }}:</strong>
                                                    {{ $subVal }}</span>
                                            @endif
                                        @endforeach
                                    @else
                                        {{ $value }}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif


            @if (!empty($data['results']['surepassUpi']['data']))
                <div class="card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="2" x2="22" y1="12" y2="12" />
                            <path
                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                        </svg>
                        <h2 class="card-title">UPI Data</h2>
                    </div>

                    <div class="data-grid">
                        @php
                            $upiData = $data['results']['surepassUpi']['data'];
                        @endphp

                        @foreach ($upiData as $key => $value)
                            @php
                                $isValid = !is_null($value) && $value !== '' &&
                                    !(is_array($value) && count($value) === 0) &&
                                    !(is_object($value) && empty((array) $value));
                            @endphp

                            @if ($isValid)
                                <div class="label">{{ str_replace('_', ' ', $key) }}:</div>
                                <div class="value">
                                    @if (is_array($value))
                                        @foreach ($value as $item)
                                            @if (is_array($item) || is_object($item))
                                                @foreach ((array) $item as $subKey => $subVal)
                                                    @if (!is_null($subVal) && $subVal !== '')
                                                        <span style="display:block;">
                                                            <strong>{{ str_replace('_', ' ', $subKey) }}:</strong> {{ $subVal }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                                <hr style="border:0;border-top:1px solid #444;margin:4px 0;">
                                            @else
                                                {{ $item }}
                                            @endif
                                        @endforeach
                                    @elseif (is_object($value))
                                        @foreach ((array) $value as $subKey => $subVal)
                                            @if (!is_null($subVal) && $subVal !== '')
                                                <span style="display:block;">
                                                    <strong>{{ str_replace('_', ' ', $subKey) }}:</strong> {{ $subVal }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @else
                                        {{ $value }}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($data['results']['surepassBank']['data']))
                <div class="card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="2" x2="22" y1="12" y2="12" />
                            <path
                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                        </svg>
                        <h2 class="card-title">Bank Data</h2>
                    </div>

                    <div class="data-grid">
                        @php
                            $bankData = $data['results']['surepassBank']['data'];
                            $excludedKeys = ['message', 'message_code', 'status_code', 'success'];
                        @endphp

                        @foreach ($bankData as $key => $value)
                            @php
                                $isValid = !in_array($key, $excludedKeys) &&
                                    !is_null($value) && $value !== '' &&
                                    !(is_array($value) && count($value) === 0) &&
                                    !(is_object($value) && empty((array) $value));
                            @endphp

                            @if ($isValid)
                                <div class="label">{{ str_replace('_', ' ', $key) }}:</div>
                                <div class="value">
                                    @if (is_array($value))
                                        @foreach ($value as $item)
                                            @if (is_array($item) || is_object($item))
                                                @foreach ((array) $item as $subKey => $subVal)
                                                    @if (!is_null($subVal) && $subVal !== '')
                                                        <span style="display:block;">
                                                            <strong>{{ str_replace('_', ' ', $subKey) }}:</strong> {{ $subVal }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                                <hr style="border:0;border-top:1px solid #444;margin:4px 0;">
                                            @else
                                                {{ $item }}
                                            @endif
                                        @endforeach
                                    @elseif (is_object($value))
                                        @foreach ((array) $value as $subKey => $subVal)
                                            @if (!is_null($subVal) && $subVal !== '')
                                                <span style="display:block;">
                                                    <strong>{{ str_replace('_', ' ', $subKey) }}:</strong> {{ $subVal }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @else
                                        {{ $value }}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif


        @if($data['type'] == 'email')
            @if (!empty($data['profile']['PROFILE_CONTAINER']['profile']))
                @php
                    $profile = $data['profile']['PROFILE_CONTAINER']['profile'];
                @endphp

                <div class="card">
                    <div class="card-header">
                        <img src="https://www.google.com/favicon.ico" alt="Google">
                        <h2 class="card-title">Google Profile</h2>
                    </div>

                    <div class="data-grid">
                        {{-- Profile Photo --}}
                        @if (!empty($profile['profilePhotos']['PROFILE']['url']))
                            <img class="profile-image" src="{{ $profile['profilePhotos']['PROFILE']['url'] }}"
                                alt="Profile Picture">
                        @endif
                        <br>
                        {{-- Full Name --}}
                        <div class="label">Full Name:</div>
                        <div class="value">{{ $profile['names']['PROFILE']['fullname'] ?? 'N/A' }}</div>

                        {{-- Email --}}
                        <div class="label">Email:</div>
                        <div class="value">{{ $profile['emails']['PROFILE']['value'] ?? 'N/A' }}</div>

                        {{-- Person ID --}}
                        <div class="label">Person ID:</div>
                        <div class="value">{{ $profile['personId'] ?? 'N/A' }}</div>

                        {{-- Google Maps Link --}}
                        @if (!empty($profile['personId']))
                            <div class="label">Google Maps:</div>
                            <div class="value">
                                <a href="https://www.google.com/maps/contrib/{{ $profile['personId'] }}" class="social-link"
                                    target="_blank">
                                    View Maps
                                </a>
                            </div>
                        @endif

                        {{-- Source ID Last Updated (optional field) --}}
                        @if (!empty($profile['sourceIds']['PROFILE']['lastUpdated']))
                            <div class="label">Last Updated At:</div>
                            <div class="value">{{ $profile['sourceIds']['PROFILE']['lastUpdated'] }}</div>
                        @endif
                    </div>
                </div>
            @endif


            @if (!empty($data['hibpResults']) && is_array($data['hibpResults']) && count($data['hibpResults']) > 0)
                <div class="card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
                            stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-blocks w-12 h-12">
                            <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                            <path
                                d="M10 21V8a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H3">
                            </path>
                        </svg>
                        <h2 class="card-title">Data Breaches</h2>
                    </div>

                    <div class="data-grid">
                        @foreach ($data['hibpResults'] as $breach)
                            <div class="label">{{ $breach['Name'] ?? 'Unknown Breach' }}:</div>
                            <div class="value">
                                @if (!empty($breach['LogoPath']))
                                    <img src="{{ $breach['LogoPath'] }}" alt="{{ $breach['Name'] ?? '' }}"
                                        style="width: 20px; height: 20px; margin-right: 10px;">
                                @endif
                                Breached on {{ \Carbon\Carbon::parse($breach['BreachDate'] ?? now())->toFormattedDateString() }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($data['osintDataResults']) && is_array($data['osintDataResults']) && count($data['osintDataResults']) > 0)
                <div class="card">
                    <div class="card-header">
                        <img src="https://cdn-icons-png.flaticon.com/512/1055/1055329.png" alt="OSINT" width="40" height="40" />
                        <h2 class="card-title">Data leaks</h2>
                    </div>

                    @foreach ($data['osintDataResults'] as $item)
                        <div class="card-inner">
                            <div class="data-grid">
                                @foreach ($item as $key => $value)
                                    @php
                                        $isValid = !is_null($value) && $value !== '' &&
                                            !(is_array($value) && count($value) === 0) &&
                                            !(is_object($value) && empty((array) $value));
                                    @endphp

                                    @if ($isValid)
                                        <div class="label">{{ str_replace('_', ' ', ucfirst($key)) }}:</div>
                                        <div class="value">
                                            @if (is_array($value) || is_object($value))
                                                @foreach ((array) $value as $subKey => $subVal)
                                                    @if (!is_null($subVal) && $subVal !== '')
                                                        <span style="display:block;">
                                                            <strong>{{ str_replace('_', ' ', ucfirst($subKey)) }}:</strong> {{ $subVal }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ $value }}
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (!empty($data['zehefResults']) && is_array($data['zehefResults']))
                @php
                    $gravatarResults = collect($data['zehefResults'])->filter(function ($item) {
                        return $item['source'] === 'Gravatar' && $item['status'] === 'found';
                    });
                @endphp

                @if ($gravatarResults->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <img src="https://gravatar.com/images/favicon-192x192.png" alt="Gravatar">
                            <h2 class="card-title">Gravatar Profile</h2>
                        </div>

                        @foreach ($gravatarResults as $result)
                            @if (!empty($result['avatar_url']))
                                <div>
                                    <img class="profile-image" src="{{ $result['avatar_url'] }}" alt="Gravatar Profile">
                                </div>
                            @endif

                            <div class="data-grid">
                                <div class="label">Username:</div>
                                <div class="value">{{ $result['username'] ?? 'N/A' }}</div>

                                @if (!empty($result['profile_url']))
                                    <div class="label">Profile URL:</div>
                                    <div class="value">
                                        <a href="{{ $result['profile_url'] }}" class="social-link" target="_blank">
                                            View Profile
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            @if (!empty($data['zehefResults']) && is_array($data['zehefResults']))
                @php
                    $socialResults = collect($data['zehefResults'])->filter(function ($item) {
                        return $item['status'] === 'found' && $item['source'] !== 'Gravatar';
                    });
                @endphp

                @if ($socialResults->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Social Media Presence</h2>
                        </div>
                        <div class="social-links">
                            @foreach ($socialResults as $item)
                                @if (!empty($item['profile_url']))
                                    <a href="{{ $item['profile_url'] }}" class="social-link" target="_blank">
                                        {{ $item['source'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

        @endif
    </div>
</body>

</html>