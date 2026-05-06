<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in | Kletterdom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @include('layouts.navigation')

    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ── STATISTIK-KARTEN ──────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-teal-600">{{ $stats['checkedInToday'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Heute eingecheckt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-blue-500">{{ $stats['guestsToday'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Davon Gäste</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['membersToday'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Davon Mitglieder</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['totalRegistrations'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Registrierungen gesamt</div>
                </div>
            </div>

            {{-- ── Titelzeile + Aktions-Buttons ─────────────────────── --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Check-In Ansicht</h2>
                <div class="flex flex-wrap items-center gap-2">

                    {{-- Alle auschecken --}}
                    <form method="POST"
                          action="{{ route('staff.checkout-all') }}"
                          onsubmit="askConfirm('Alle aktuell eingecheckten Personen wirklich auschecken?', this); return false;">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700
                                   rounded-lg px-4 py-2 text-sm font-semibold
                                   hover:bg-red-50 hover:border-red-300 hover:text-red-700
                                   transition min-h-[44px] touch-manipulation">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                            </svg>
                            Alle auschecken
                        </button>
                    </form>

                    {{-- QR-Scanner --}}
                    <button id="qr-toggle-btn" onclick="toggleScanner()"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white rounded-lg
                               px-4 py-2 text-sm font-semibold hover:bg-indigo-700 transition min-h-[44px] touch-manipulation">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 4h6v6H4V4zm0 10h6v6H4v-6zm10-10h6v6h-6V4zm4 10h2v2h-2v-2zm-4 0h2v2h-2v-2zm0 4h2v2h-2v-2zm4-2h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                        </svg>
                        QR-Code scannen
                    </button>

                </div>
            </div>

            {{-- ── QR-SCANNER PANEL ─────────────────────────────────── --}}
            <div id="qr-scanner-panel"
                 class="hidden mb-6 bg-white border border-indigo-200 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-indigo-50 border-b border-indigo-100">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-indigo-800">Kamera-Scanner</span>
                    </div>
                    <button onclick="toggleScanner()" class="text-indigo-400 hover:text-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div class="mb-3 flex items-center gap-3">
                        <label for="camera-select" class="text-xs text-gray-500 whitespace-nowrap">Kamera:</label>
                        <select id="camera-select"
                            class="flex-1 border border-gray-300 rounded-md px-2 py-1.5 text-sm
                                   bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Wird geladen…</option>
                        </select>
                        <button onclick="startScanner()"
                            class="inline-flex items-center bg-indigo-600 text-white rounded-md
                                   px-3 py-1.5 text-xs font-semibold hover:bg-indigo-700 transition">
                            Starten
                        </button>
                        <button onclick="stopScanner()"
                            class="inline-flex items-center bg-white border border-gray-300 text-gray-700
                                   rounded-md px-3 py-1.5 text-xs font-semibold hover:bg-gray-50 transition">
                            Stopp
                        </button>
                    </div>
                    <div id="qr-reader"
                         class="rounded-lg overflow-hidden border border-gray-200 bg-gray-900"
                         style="width:100%; max-width:480px; min-height:240px; margin:0 auto;"></div>
                    <div id="qr-status" class="mt-3 hidden rounded-lg px-4 py-3 text-sm font-medium"></div>
                </div>
            </div>

            {{-- ── Flash Messages ───────────────────────────────────── --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5 m-0">
                        @foreach ($errors->all() as $error)
                            <li class="my-1">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Suche ────────────────────────────────────────────── --}}
            <div class="mb-6 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <form method="GET" action="{{ route('staff') }}"
                    class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                    <div class="flex-1 min-w-0 sm:min-w-[280px]">
                        <input type="text" name="q" value="{{ $query }}"
                            placeholder="Name oder Mitgliedsnummer suchen"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <button type="submit"
                            class="inline-flex items-center justify-center bg-white border border-gray-300
                                   rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Suchen
                        </button>
                        <a href="{{ route('staff') }}"
                            class="inline-flex items-center justify-center bg-white border border-gray-300
                                   rounded-lg px-4 py-2 text-sm font-semibold text-gray-700
                                   hover:bg-gray-50 transition no-underline">
                            Zurücksetzen
                        </a>
                    </div>
                </form>
            </div>

            {{-- ============================================================ --}}
            {{-- Mobile Cards                                                  --}}
            {{-- ============================================================ --}}
            <div class="space-y-4 md:hidden">
                @php $shownDividerMobile = false; @endphp

                @forelse ($registrations as $registration)
                    @php
                        $currentCheckin = $registration->currentCheckin;
                        $visits         = $registration->trial_visits_count ?? 0;
                        $lastCheckin    = $registration->checkins()->latest('checked_in_at')->first();
                    
                        $isTrialMaxReached         = $registration->member_type === 'guest' && $visits >= 3;
                        $isUnverifiedMemberBlocked = $registration->member_type === 'member'
                                                   && $registration->member === null
                                                   && $registration->access_status === 'red';
                    
                        // Hart gesperrt = keine Aktion möglich
                        $isHardBlocked = $registration->access_status === 'red'
                                      || $isTrialMaxReached
                                      || $isUnverifiedMemberBlocked;
                    
                        // Modal nötig = orange ODER Schnuppergast Besuch 2–3
                        $isTrialNeedsModal = $registration->member_type === 'guest' && $visits >= 1 && $visits < 3;
                        $requiresModal     = !$isHardBlocked
                                          && ($registration->access_status === 'orange' || $isTrialNeedsModal);
                    
                        // Warnung im Modal: nächster Check-in sperrt
                        $nextCheckinTriggersRed = $registration->member_type === 'guest' && $visits === 2;
                        
                        $accessStyle = match($registration->access_status) {
                        'green'  => 'bg-green-100 text-green-800',
                        'blue'   => 'bg-blue-100 text-blue-800',
                        'orange' => 'bg-amber-100 text-amber-800',
                        default  => 'bg-red-100 text-red-800',
                    };
                    $accessText = match($registration->access_status) {
                        'green'  => 'Zutritt ok',
                        'blue'   => 'Schnuppergast',
                        'orange' => 'Warnung',
                        default  => 'Gesperrt',
                    };
                    @endphp

                    @if (!$shownDividerMobile && !$currentCheckin)
                        @php $shownDividerMobile = true; @endphp
                        <div class="px-2 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Noch nicht eingecheckt
                        </div>
                    @endif

                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-3
                        {{ $currentCheckin ? 'border-l-2 border-l-indigo-300' : '' }}">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $registration->first_name }} {{ $registration->last_name }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $registration->birth_date?->format('d.m.Y') ?? '—' }}
                                    · {{ $registration->member_type === 'guest' ? 'Gast' : 'Mitglied' }}
                                    @if ($registration->member_number)
                                        · {{ $registration->member_number }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if ($currentCheckin)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold text-green-800">
                                        ✅ Eingecheckt
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $accessStyle }}">
                                        {{ $accessText }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Zusatzinfos --}}
                        @if ($registration->needs_parent_consent)
                            <div class="text-xs text-gray-600 space-y-1 border-t border-gray-100 pt-2">
                                <div>Klettert alleine? – dann Formular nötig
                                    (<a href="https://www.oetk-langenlois.at/fileadmin/Einverstaendniserklaerung-14-18.pdf"
                                        target="_blank" rel="noopener noreferrer"
                                        class="underline text-gray-500">PDF</a>)
                                    @if ($registration->parent_consent_received)
                                        <span class="text-gray-400">(geprüft)</span>
                                    @else
                                        <form method="POST"
                                              action="{{ route('staff.parent-consent', $registration) }}"
                                              class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="underline text-gray-600 bg-transparent border-none p-0 cursor-pointer text-xs">
                                                Formular abgegeben
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @elseif (!$currentCheckin && $registration->access_reason)
                            <div class="text-xs text-gray-500 border-t border-gray-100 pt-2">
                                {{ $registration->access_reason }}
                            </div>
                        @endif

                        {{-- ── Aktion (Mobile) ──────────────────────────────── --}}
                        <div class="border-t border-gray-100 pt-3">
                            @if ($currentCheckin)
                                <span>Eingecheckt {{ $currentCheckin->checked_in_at->format('H:i') }} Uhr</span>
                            
                            @elseif ($isHardBlocked)
                                <button disabled class="w-full inline-flex items-center justify-center border border-gray-200
                                                       bg-gray-100 text-gray-400 rounded-lg px-3 py-2 text-sm font-semibold
                                                       cursor-not-allowed min-h-[44px]">
                                   Check-in
                               </button>
                            
                            @elseif ($requiresModal)
                                {{-- Verstecktes Formular --}}
                                <form id="checkin-form-{{ $registration->id }}" method="POST"
                                      action="{{ route('staff.checkin', $registration) }}" class="hidden">
                                    @csrf
                                    <input type="text" name="reason" id="reason-{{ $registration->id }}">
                                </form>
                                <button type="button"
                                    onclick="openCheckinModal(
                                        document.getElementById('checkin-form-{{ $registration->id }}'),
                                        document.getElementById('reason-{{ $registration->id }}'),
                                        '{{ e($registration->first_name . ' ' . $registration->last_name) }}',
                                        '{{ e($registration->access_reason ?? '') }}',
                                        '{{ $registration->access_status }}',
                                        {{ $nextCheckinTriggersRed ? 'true' : 'false' }},
                                        {{ $visits }},
                                        '{{ $lastCheckin ? $lastCheckin->checked_in_at->format('d.m.Y H:i') : '' }}'
                                    )"
                                    class="w-full inline-flex items-center justify-center border border-transparent
                                           bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-semibold
                                           hover:bg-indigo-700 transition min-h-[44px] touch-manipulation">
                                    Check-in
                                </button>
                            
                            @else
                                <form method="POST" action="{{ route('staff.checkin', $registration) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center border border-transparent
                            	                                   bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-semibold
                            	                                   hover:bg-indigo-700 transition min-h-[44px] touch-manipulation">
                                       Check-in
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="bg-white border border-gray-200 rounded-xl p-6 text-center text-sm text-gray-500 shadow-sm">
                        Keine Registrierungen gefunden.
                    </div>
                @endforelse
            </div>

            {{-- ============================================================ --}}
            {{-- Desktop Table                                                 --}}
            {{-- ============================================================ --}}
            <div class="hidden md:block bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mitgliedsnr.</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Zutritt</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Zusatzinfos</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Check-in / Aktion</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @php $shownDivider = false; @endphp

                            @forelse ($registrations as $registration)
                                @php
                                    $currentCheckin = $registration->currentCheckin;
                                    $visits         = $registration->trial_visits_count ?? 0;
                                    $lastCheckin    = $registration->checkins()->latest('checked_in_at')->first();
                                
                                    $isTrialMaxReached         = $registration->member_type === 'guest' && $visits >= 3;
                                    $isUnverifiedMemberBlocked = $registration->member_type === 'member'
                                                               && $registration->member === null
                                                               && $registration->access_status === 'red';
                                
                                    // Hart gesperrt = keine Aktion möglich
                                    $isHardBlocked = $registration->access_status === 'red'
                                                  || $isTrialMaxReached
                                                  || $isUnverifiedMemberBlocked;
                                
                                    // Modal nötig = orange ODER Schnuppergast Besuch 2–3
                                    $isTrialNeedsModal = $registration->member_type === 'guest' && $visits >= 1 && $visits < 3;
                                    $requiresModal     = !$isHardBlocked
                                                      && ($registration->access_status === 'orange' || $isTrialNeedsModal);
                                
                                    // Warnung im Modal: nächster Check-in sperrt
                                    $nextCheckinTriggersRed = $registration->member_type === 'guest' && $visits === 2;
                                    
                                    $accessStyle = match($registration->access_status) {
                                        'green'  => 'bg-green-100 text-green-800',
                                        'blue'   => 'bg-blue-100 text-blue-800',
                                        'orange' => 'bg-amber-100 text-amber-800',
                                        default  => 'bg-red-100 text-red-800',
                                    };
                                    $accessText = match($registration->access_status) {
                                        'green'  => 'Zutritt ok',
                                        'blue'   => 'Schnuppergast',
                                        'orange' => 'Warnung',
                                        default  => 'Gesperrt',
                                    };
                                @endphp

                                @if (!$shownDivider && !$currentCheckin)
                                    @php $shownDivider = true; @endphp
                                    <tr>
                                        <td colspan="5"
                                            class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider
                                                   bg-gray-50 border-t border-b border-gray-100">
                                            Noch nicht eingecheckt
                                        </td>
                                    </tr>
                                @endif

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $registration->first_name }} {{ $registration->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $registration->birth_date?->format('d.m.Y') ?? '—' }}
                                            · {{ $registration->member_type === 'guest' ? 'Gast' : 'Mitglied' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-gray-600">
                                        {{ $registration->member_number ?? '—' }}
                                    </td>

                                    {{-- ZUTRITT: eingecheckt → immer grün --}}
                                    <td class="px-4 py-4 align-top">
                                        @if ($currentCheckin)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold text-green-800">
                                                ✅ Eingecheckt
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $accessStyle }}">
                                                {{ $accessText }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ZUSATZINFOS --}}
                                    <td class="px-4 py-4 align-top text-sm text-gray-600">
                                        @if ($registration->needs_parent_consent)
                                            <div>Klettert alleine? – dann Formular nötig
                                                (<a href="https://www.oetk-langenlois.at/fileadmin/Einverstaendniserklaerung-14-18.pdf"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="text-gray-500 underline">PDF</a>)
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                @if ($registration->parent_consent_received)
                                                    Formular geprüft
                                                @else
                                                    <form method="POST"
                                                          action="{{ route('staff.parent-consent', $registration) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-xs text-gray-600 underline bg-transparent
                                                                   border-none p-0 cursor-pointer hover:text-gray-900">
                                                            Formular abgegeben
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @elseif (!$currentCheckin && $registration->access_reason)
                                            <span class="text-gray-600">{{ $registration->access_reason }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>

                                    {{-- ── CHECK-IN AKTION (Desktop) ──────────────── --}}
                                    <td class="px-4 py-4 align-top">
                                        @if ($currentCheckin)
                                            <span>Eingecheckt {{ $currentCheckin->checked_in_at->format('H:i') }} Uhr</span>
                                        
                                        @elseif ($isHardBlocked)
                                            <button disabled class="w-full inline-flex items-center justify-center border border-gray-200
                                                                   bg-gray-100 text-gray-400 rounded-lg px-3 py-2 text-sm font-semibold
                                                                   cursor-not-allowed min-h-[44px]">
                                               Check-in
                                           </button>
                                        
                                        @elseif ($requiresModal)
                                            {{-- Verstecktes Formular --}}
                                            <form id="checkin-form-{{ $registration->id }}" method="POST"
                                                  action="{{ route('staff.checkin', $registration) }}" class="hidden">
                                                @csrf
                                                <input type="text" name="reason" id="reason-{{ $registration->id }}">
                                            </form>
                                            <button type="button"
                                                onclick="openCheckinModal(
                                                    document.getElementById('checkin-form-{{ $registration->id }}'),
                                                    document.getElementById('reason-{{ $registration->id }}'),
                                                    '{{ e($registration->first_name . ' ' . $registration->last_name) }}',
                                                    '{{ e($registration->access_reason ?? '') }}',
                                                    '{{ $registration->access_status }}',
                                                    {{ $nextCheckinTriggersRed ? 'true' : 'false' }},
                                                    {{ $visits }},
                                                    '{{ $lastCheckin ? $lastCheckin->checked_in_at->format('d.m.Y H:i') : '' }}'
                                                )"
                                                class="w-full inline-flex items-center justify-center border border-transparent
                                                       bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-semibold
                                                       hover:bg-indigo-700 transition min-h-[44px] touch-manipulation">
                                                Check-in
                                            </button>
                                        
                                        @else
                                            <form method="POST" action="{{ route('staff.checkin', $registration) }}">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex items-center justify-center border border-transparent
                                        	                                   bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-semibold
                                        	                                   hover:bg-indigo-700 transition min-h-[44px] touch-manipulation">
                                                   Check-in
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-4 py-8 text-center text-sm text-gray-500 border-t border-gray-100">
                                        Keine Registrierungen gefunden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    {{-- ── Confirm Modal ───────────────────────────────────────────────── --}}
    <div id="confirmModal"
         class="fixed inset-0 z-[100] hidden"
         aria-hidden="true">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/50" onclick="closeConfirmModal()"></div>

        {{-- Dialog --}}
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Bitte bestätigen</h3>
                </div>
                <div class="px-5 py-4 flex flex-col gap-3">
                    <p id="confirmModalText" class="text-sm text-gray-600 leading-relaxed"></p>

                    {{-- Orange-Hinweis (nur im Orange-Modus sichtbar) --}}
                    <div id="confirmOrangeHint" class="hidden rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                        <span class="font-semibold block mb-1">Grund für Status Orange:</span>
                        <span id="confirmOrangeReason" class="block text-amber-700"></span>
                    </div>

                    {{-- Kulanzfeld (nur im Orange-Modus sichtbar) --}}
                    <div id="confirmOrangeKulanz" class="hidden">
                        <label for="confirmKulanzInput" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Kulanzgrund <span id="confirmKulanzOptional" class="font-normal normal-case text-gray-400">(optional)</span>
                        </label>
                        <input type="text" id="confirmKulanzInput"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100
                            flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
                    <button type="button"
                            onclick="closeConfirmModal()"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300
                                   bg-white px-4 py-2 text-sm font-semibold text-gray-700
                                   hover:bg-gray-50 transition min-h-[44px]">
                        Abbrechen
                    </button>
                    <button type="button"
                            id="confirmOkBtn"
                            class="inline-flex items-center justify-center rounded-lg border border-transparent
                                   bg-red-600 px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-red-700 transition min-h-[44px]">
                        Ja, auschecken
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── JavaScript ──────────────────────────────────────────────────── --}}
    <script>
    // Confirm Modal (Alle auschecken + Orange-Check-in)
    let confirmForm = null;
    let orangeReasonInput = null;
    let isModalKulanzRequired = false;

    function askConfirm(message, form) {
        confirmForm = form;
        orangeReasonInput = null;
        isModalKulanzRequired = false;
        document.getElementById('confirmModalText').textContent = message;
        // Orange-Felder verstecken (normaler Modus)
        document.getElementById('confirmOrangeHint').classList.add('hidden');
        document.getElementById('confirmOrangeKulanz').classList.add('hidden');
        // Button: roter "Auschecken"-Stil
        const okBtn = document.getElementById('confirmOkBtn');
        okBtn.disabled = false;
        okBtn.textContent = 'Ja, auschecken';
        okBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        okBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function openOrangeCheckin(form, reasonInput, name, reason, isTrialLimit) {
        confirmForm = form;
        orangeReasonInput = reasonInput;
        isModalKulanzRequired = !!isTrialLimit;
        const label = isTrialLimit
            ? name + ' war bereits Schnuppern. Trotzdem einchecken?'
            : name + ' hat Status Orange. Trotzdem einchecken?';
        document.getElementById('confirmModalText').textContent = label;
        // Orange-Felder einblenden
        const reasonLabel = isTrialLimit ? 'Schnupperlimit-Grund:' : 'Grund für Status Orange:';
        document.querySelector('#confirmOrangeHint span.font-semibold').textContent = '⚠️ ' + reasonLabel;
        document.getElementById('confirmOrangeReason').textContent = reason || (isTrialLimit ? 'Schnuppergast hat bereits einen Besuch absolviert.' : 'Kein Grund angegeben');
        document.getElementById('confirmOrangeHint').classList.remove('hidden');
        document.getElementById('confirmOrangeKulanz').classList.remove('hidden');
        document.getElementById('confirmKulanzInput').value = '';
        document.getElementById('confirmKulanzInput').classList.remove('border-red-500');
        // Kulanzfeld: optional/pflicht je nach Modus
        document.getElementById('confirmKulanzOptional').textContent = isTrialLimit ? '(Pflicht)' : '(optional)';
        // Button: indigo "Check-in"-Stil
        const okBtn = document.getElementById('confirmOkBtn');
        okBtn.disabled = false;
        okBtn.textContent = 'Trotzdem Check-in';
        okBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        okBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => document.getElementById('confirmKulanzInput').focus(), 50);
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        confirmForm = null;
        orangeReasonInput = null;
        isModalKulanzRequired = false;
        // Felder zurücksetzen
        document.getElementById('confirmOrangeHint').classList.add('hidden');
        document.getElementById('confirmOrangeKulanz').classList.add('hidden');
        document.getElementById('confirmKulanzInput').value = '';
        // Button zurück auf rot
        const okBtn = document.getElementById('confirmOkBtn');
        okBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        okBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        okBtn.textContent = 'Ja, auschecken';
        okBtn.disabled = false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('confirmOkBtn')?.addEventListener('click', function () {
            if (!confirmForm) return;
            if (orangeReasonInput) {
                const val = document.getElementById('confirmKulanzInput').value.trim();
                if (isModalKulanzRequired && !val) {
                    const inp = document.getElementById('confirmKulanzInput');
                    inp.classList.add('border-red-500');
                    inp.focus();
                    return;
                }
                orangeReasonInput.value = val;
            }
            this.disabled = true;
            confirmForm.submit();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeConfirmModal();
        });

    // ── QR-Scanner ─────────────────────────────────────────────────────────
    let html5QrCode   = null;
    let scannerRunning = false;
    let lastScanned   = null;

    function toggleScanner() {
        const panel = document.getElementById('qr-scanner-panel');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            initCameraList();
        } else {
            stopScanner();
            panel.classList.add('hidden');
        }
    }

    async function initCameraList() {
        try {
            const cameras = await Html5Qrcode.getCameras();
            const select  = document.getElementById('camera-select');
            select.innerHTML = '';
            if (!cameras || cameras.length === 0) {
                select.innerHTML = '<option value="">Keine Kamera gefunden</option>';
                showStatus('Keine Kamera gefunden. Bitte Kamerazugriff erlauben.', 'error');
                return;
            }
            cameras.forEach((cam, i) => {
                const opt = document.createElement('option');
                opt.value = cam.id;
                opt.text  = cam.label || `Kamera ${i + 1}`;
                select.appendChild(opt);
            });
            const backCam = cameras.find(c => /back|rear|environment/i.test(c.label));
            if (backCam) select.value = backCam.id;
            startScanner();
        } catch (err) {
            showStatus('Kamerazugriff verweigert. Bitte in den Browser-Einstellungen erlauben.', 'error');
        }
    }

    async function startScanner() {
        const cameraId = document.getElementById('camera-select').value;
        if (!cameraId) { showStatus('Bitte zuerst eine Kamera auswählen.', 'error'); return; }
        if (scannerRunning) await stopScanner();
        html5QrCode = new Html5Qrcode('qr-reader');
        try {
            await html5QrCode.start(
                cameraId,
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                onScanSuccess,
                onScanError
            );
            scannerRunning = true;
            showStatus('Scanner aktiv – QR-Code vor die Kamera halten.', 'info');
        } catch (err) {
            showStatus('Kamera konnte nicht gestartet werden: ' + err, 'error');
        }
    }

    async function stopScanner() {
        if (html5QrCode && scannerRunning) {
            try { await html5QrCode.stop(); } catch (_) {}
            scannerRunning = false;
        }
        clearStatus();
    }

    async function onScanSuccess(decodedText) {
        if (decodedText === lastScanned) return;
        lastScanned = decodedText;
        setTimeout(() => { lastScanned = null; }, 3000);
        if (html5QrCode && scannerRunning) { try { html5QrCode.pause(); } catch (_) {} }
        showStatus('QR-Code erkannt – wird geprüft …', 'info');

        let token = decodedText.trim();
        const urlMatch = token.match(/\/verify\/([^/?#]+)/);
        if (urlMatch) token = urlMatch[1];

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        try {
            response = await fetch(`/verify/${token}/checkin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':  csrfToken ?? '',
                    'Accept':        'application/json',
                    'Content-Type':  'application/json',
                },
            });
        } catch (networkErr) {
            showStatus('Verbindungsfehler – ist der Server erreichbar? (' + networkErr.message + ')', 'error');
            setTimeout(() => { try { html5QrCode.resume(); } catch (_) {} }, 3000);
            return;
        }

        if (response.status === 419) {
            showStatus('Sitzung abgelaufen – Seite wird neu geladen …', 'info');
            setTimeout(() => window.location.reload(), 1500);
            return;
        }
        if (response.status === 404) {
            showStatus('⚠ QR-Code nicht erkannt – ungültiger oder abgelaufener Code.', 'error');
            setTimeout(() => { try { html5QrCode.resume(); } catch (_) {} }, 3000);
            return;
        }

        let data = {};
        try { data = await response.json(); } catch (_) {
            showStatus('Unerwartete Server-Antwort.', 'error');
            return;
        }

        if (response.ok && data.success) {
            showStatus('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1800);
        } else {
            showStatus('⚠ ' + (data.message ?? 'Unbekannter Fehler'), 'error');
            setTimeout(() => { try { html5QrCode.resume(); } catch (_) {} }, 3000);
        }
    }

    function onScanError() {}

    function showStatus(msg, type) {
        const el = document.getElementById('qr-status');
        el.textContent = msg;
        el.className   = 'mt-3 rounded-lg px-4 py-3 text-sm font-medium';
        const styles   = {
            info:    'bg-blue-50 border border-blue-200 text-blue-800',
            success: 'bg-green-50 border border-green-200 text-green-800',
            error:   'bg-red-50 border border-red-200 text-red-800',
        };
        el.classList.add(...(styles[type] ?? styles.info).split(' '));
        el.classList.remove('hidden');
    }

    function clearStatus() {
        const el = document.getElementById('qr-status');
        el.classList.add('hidden');
        el.textContent = '';
    }
    });
    </script>

</body>
</html>
