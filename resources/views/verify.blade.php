<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrierungsbestätigung | Kletterdom</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @includeWhen(Auth::check(), 'layouts.navigation')

    <main class="py-12">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">

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

            <div class="flex items-center justify-center mb-8">
                <h2 class="text-xl font-bold text-gray-800 text-center">
                    Registrierungsbestätigung
                </h2>
            </div>

            @php
    $currentCheckin = $registration->currentCheckin;

    $colors = [
        'green'  => ['bg' => 'bg-green-50',   'border' => 'border-green-500',  'text' => 'text-green-800',  'icon' => '✅', 'label' => 'Zutritt OK'],
        'blue'   => ['bg' => 'bg-blue-50',    'border' => 'border-blue-500',   'text' => 'text-blue-800',   'icon' => '🔵', 'label' => 'Schnupperklettern'],
        'orange' => ['bg' => 'bg-orange-50',  'border' => 'border-orange-500', 'text' => 'text-orange-800', 'icon' => '⚠️', 'label' => 'Bitte beim Hallendienst melden'],
        'red'    => ['bg' => 'bg-red-50',     'border' => 'border-red-500',    'text' => 'text-red-800',    'icon' => '🚫', 'label' => 'Kein Zutritt'],
    ];
    
    $c = $colors[$registration->access_status] ?? $colors['red'];

    // Prüfen ob Check-in blockiert werden muss (Status Rot oder Orange ohne aktive Kulanz)
    $hasActiveKulanz = $registration->manual_exception_until && $registration->manual_exception_until->isFuture();
    $isTrialUsed = $registration->access_status === 'blue'
       && ($registration->trial_visits_count ?? 0) >= 1
       && !$hasActiveKulanz;

    $isBlocked = !in_array($registration->access_status, ['green', 'blue']) || $isTrialUsed;
@endphp

{{-- Status-Kasten NUR anzeigen, wenn noch NICHT eingecheckt --}}
@if(!$currentCheckin)
<div class="bg-white rounded-xl border-2 {{ $c['border'] }} {{ $c['bg'] }} p-6 mb-6 text-center shadow-sm">
    <div class="text-5xl mb-3">{{ $c['icon'] }}</div>
    <div class="text-2xl font-bold {{ $c['text'] }}">{{ $c['label'] }}</div>
    
    @if($registration->access_reason)
        <div class="mt-2 text-sm {{ $c['text'] }} opacity-80 font-medium">
            {{ $registration->access_reason }}
        </div>
    @endif
</div>
@endif


            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Deine Registrierung</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-bold text-gray-900">{{ $registration->first_name }} {{ $registration->last_name }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Typ</dt>
                        <dd class="font-bold text-gray-900">
                            {{ $registration->member_type === 'member' ? 'Mitglied' : 'Schnuppergast' }}
                        </dd>
                    </div>
                    @if($registration->member_number)
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-gray-500">Mitgliedsnummer</dt>
                            <dd class="font-bold text-gray-900">{{ $registration->member_number }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between pt-1">
                        <dt class="text-gray-500">Haftungsausschluss</dt>
                        <dd class="font-bold text-green-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Akzeptiert
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Dein persönlicher QR-Code</h3>
                <div class="flex justify-center mb-5 bg-white p-4 rounded-lg border border-gray-100 inline-block mx-auto">
                    {!! QrCode::size(200)->margin(1)->generate(url('verify/' . $registration->qr_token)) !!}
                </div>
                <p class="text-sm text-gray-600 leading-relaxed max-w-[280px] mx-auto">
                    Checke mit diesem Code beim Hallendienst im Kletterdom ein.<br>
                    <span class="text-xs text-gray-400 mt-1 block">Tipp: Speichere diese Seite als Lesezeichen oder mache einen Screenshot.</span>
                </p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ url('verify/' . $registration->qr_token) }}"
                       class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline break-all font-medium">
                        {{ url('verify/' . $registration->qr_token) }}
                    </a>
                </div>
            </div>

            @auth
                <div class="mt-10 bg-gray-900 rounded-xl shadow-lg border border-gray-800 p-6 text-center">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5">Personal-Aktion</h3>

                    @if($currentCheckin)
                        <div class="bg-green-900/30 border border-green-800 rounded-lg p-4">
                            <div class="text-green-400 font-bold text-lg flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Bereits eingecheckt
                            </div>
                            <div class="text-green-500/80 text-sm mt-1">
                                am {{ $currentCheckin->checked_in_at->format('d.m.Y \u\m H:i') }} Uhr
                            </div>
                        </div>
                    @elseif($isBlocked)
                        <div class="bg-amber-900/30 border border-amber-800 rounded-lg p-4 mb-2">
                            <div class="text-amber-400 font-bold text-lg flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Check-in nicht möglich
                            </div>
                            <div class="text-amber-500/80 text-sm mt-2">
                                @if($registration->access_status === 'red')
                                    Kein Zutritt erlaubt. Bitte beim Hallendienst melden.
                                @elseif($isTrialUsed)
                                    Erstbesuch bereits absolviert. Zweiter Besuch nur mit Kulanz durch den Hallendienst.
                                @else
                                    Zutritt erfordert manuelle Freigabe. Bitte in der Staff-Übersicht prüfen.
                                @endif
                            </div>
                        </div>

                    @else
                        <form method="POST" action="{{ url('verify/' . $registration->qr_token . '/checkin') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-500 text-white px-6 py-4 rounded-lg font-bold text-lg shadow-[0_0_15px_rgba(22,163,74,0.3)] transition transform hover:-translate-y-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Check-in bestätigen
                            </button>
                        </form>
                    @endif

                    <div class="mt-6 pt-5 border-t border-gray-800">
                        <a href="{{ route('staff') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Zurück zur Staff-Übersicht
                        </a>
                    </div>
                </div>
            @endauth

        </div>
    </main>

</body>
</html>
