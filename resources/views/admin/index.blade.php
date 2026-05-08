<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | Kletterdom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @include('layouts.navigation')

    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">🛠️ Admin-Bereich</h2>
                <span class="text-sm text-gray-500">Hallenverwaltung</span>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span>❌</span> {{ session('error') }}
                </div>
            @endif

            {{-- ── KPI-Karten ──────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-teal-600">{{ $stats['checked_in_today'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Heute eingecheckt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['total_registrations'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Registrierungen gesamt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-purple-500">{{ $stats['members'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Aktive Mitglieder</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-red-400">{{ $stats['inactive_members'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Inaktive Mitglieder</div>
                </div>
            </div>

            {{-- ── Hallenauslastung Chart ───────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">📊 Hallenauslastung – letzte 30 Tage</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="auslastungChart" style="pointer-events: none;"></canvas>
                </div>
            </div>

            {{-- ── Zwei-Spalten-Grid: Import + Export ──────────────── --}}
            <div class="grid md:grid-cols-2 gap-6">

                {{-- Mitglieder CSV-Import --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">📥 Mitglieder importieren</h3>
                        <p class="mt-1 text-xs text-gray-400 leading-relaxed">
                            CSV-Spalten:
                            <code class="font-mono bg-gray-100 px-1 py-0.5 rounded text-gray-600">
                                Mitgliedsnummer; Nachname; Vorname; Email; Status; Betrag offen; Geburtsdatum
                            </code>
                        </p>
                    </div>

                    <form id="importForm"
                          action="{{ route('admin.importMembers') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="flex flex-col gap-4">
                        @csrf

                        {{-- Datei-Picker --}}
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                CSV-Datei wählen
                            </label>
                            <input type="file" name="members_csv" accept=".csv,.txt"
                                class="block w-full text-sm text-gray-500
                                       file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0
                                       file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700
                                       hover:file:bg-teal-100">
                            @error('members_csv')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bestätigungs-Block (nur sichtbar wenn nötig) --}}
                        @if (session('confirm_missing_count_required'))
                            <div class="rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                                ⚠️ <strong>{{ session('confirm_missing_count_required') }} Mitglieder</strong>
                                fehlen in der CSV und würden auf „inaktiv" gesetzt.
                                Bitte die Anzahl unten bestätigen und erneut importieren.
                            </div>
                        @endif

                        <div class="flex flex-col gap-1">
                            <label for="confirm_missing_count"
                                   class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Fehlende Mitglieder bestätigen
                            </label>
                            <input type="number"
                                   name="confirm_missing_count"
                                   id="confirm_missing_count"
                                   value="{{ old('confirm_missing_count') }}"
                                   placeholder="z. B. 12"
                                   class="w-36 border border-gray-300 rounded-md px-3 py-1.5 text-sm shadow-sm focus:ring-teal-400 focus:border-teal-400">
                            <input type="hidden" name="stored_csv_path"
                                   value="{{ old('stored_csv_path', session('stored_csv_path')) }}">
                        </div>

                        {{-- Submit-Button mit Doppelklick-Schutz --}}
                        <button id="importBtn" type="submit"
                            class="w-full bg-teal-600 hover:bg-teal-700 disabled:bg-teal-300 disabled:cursor-not-allowed
                                   text-white text-sm font-semibold py-2 px-4 rounded-lg transition
                                   flex items-center justify-center gap-2 min-h-[44px] touch-manipulation">
                            <span id="importBtnText">Importieren</span>
                            <svg id="importSpinner" class="hidden animate-spin h-4 w-4 text-white"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Check-ins CSV-Export --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">📤 Check-ins exportieren</h3>
                        <p class="text-sm text-gray-400">
                            Exportiert alle Check-ins im gewählten Zeitraum als CSV
                            (Excel-kompatibel, UTF-8 BOM, Semikolon-getrennt).
                        </p>
                    </div>
                    <form action="{{ route('admin.exportCheckins') }}" method="GET" class="flex flex-col gap-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Von</label>
                                <input type="date" name="from" value="{{ now()->subDays(30)->toDateString() }}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:ring-2 focus:ring-teal-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Bis</label>
                                <input type="date" name="to" value="{{ now()->toDateString() }}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:ring-2 focus:ring-teal-400 focus:outline-none">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold
                                   py-2 px-4 rounded-lg transition min-h-[44px] touch-manipulation">
                            CSV herunterladen
                        </button>
                    </form>

                    {{-- ── Inaktive Mitglieder löschen ── --}}
                    <div class="border-t border-gray-100 pt-4 mt-auto">
                        <h4 class="text-sm font-semibold text-gray-600 mb-1">🗑️ Inaktive Mitglieder entfernen</h4>
                        <p class="text-xs text-gray-400 mb-3">
                            Löscht alle Registrierungen mit
                            <code class="font-mono bg-gray-100 px-1 rounded">membership_status = inactive</code>
                            dauerhaft aus der Datenbank inkl. ihrer Check-ins.
                        </p>

                        @php
                            $inactiveCount  = $stats['inactive_members'];
                            $inactiveMsg    = 'Alle ' . $inactiveCount . ' inaktiven Mitglieder wirklich dauerhaft löschen? Diese Aktion kann nicht rückgängig gemacht werden.';
                        @endphp

                        <form action="{{ route('admin.deleteInactiveMembers') }}" method="POST"
                              data-confirm="{{ $inactiveMsg }}"
                              onsubmit="handleConfirmForm(event, this)">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full bg-red-50 hover:bg-red-100 active:bg-red-200
                                       border border-red-200 text-red-700 text-sm font-semibold
                                       py-2 px-4 rounded-lg transition min-h-[44px] touch-manipulation cursor-pointer"
                                style="pointer-events: auto; position: relative; z-index: 1;">
                                {{ $inactiveCount }} inaktive Mitglieder löschen
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Registrierungen Tabelle ─────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div>
                      <h3 class="text-lg font-semibold text-gray-700">Alle Registrierungen</h3>
                      <span class="text-sm text-gray-400">{{ $registrations->total() }} gesamt</span>
                  </div>
                  <form method="GET" action="{{ route('admin.index') }}" class="flex flex-wrap gap-2 items-center">
                      <input
                          type="text"
                          name="q"
                          value="{{ $query ?? '' }}"
                          placeholder="Name oder Mitgliedsnr. suchen…"
                          class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 w-52"
                      >
                      <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                          <option value="">Alle Status</option>
                          <option value="green"  {{ ($statusFilter ?? '') === 'green'  ? 'selected' : '' }}>Zutritt OK</option>
                          <option value="blue"   {{ ($statusFilter ?? '') === 'blue'   ? 'selected' : '' }}>Schnuppergast</option>
                          <option value="orange" {{ ($statusFilter ?? '') === 'orange' ? 'selected' : '' }}>Freigabe nötig</option>
                          <option value="red"    {{ ($statusFilter ?? '') === 'red'    ? 'selected' : '' }}>Gesperrt</option>
                      </select>
                      <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1.5 rounded-lg transition-colors">
                          Suchen
                      </button>
                      @if($query || $statusFilter)
                          <a href="{{ route('admin.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                              ✕ Zurücksetzen
                          </a>
                      @endif
                  </form>
                </div>

                {{-- VOR der Desktop-Tabelle, außerhalb beider @forelse --}}
                @php
                    $statusLabels = [
                        'green'  => 'Zutritt OK',
                        'blue'   => 'Schnuppergast',
                        'orange' => 'Freigabe nötig',
                        'red'    => 'Gesperrt',
                    ];
                @endphp

                {{-- Desktop-Tabelle --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Typ</th>
                                <th class="px-4 py-3 text-left">Mitgliedsnr.</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">QR-Link</th>
                                <th class="px-4 py-3 text-left">Check-ins</th>
                                <th class="px-4 py-3 text-left">Registriert am</th>
                                <th class="px-4 py-3 text-left">Aktion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($registrations as $reg)
                                @php
                                  $regName   = $reg->first_name . ' ' . $reg->last_name;
                                  $deleteMsg = 'Registrierung von ' . $regName . ' wirklich löschen? Alle Check-ins werden mitgelöscht.';

                                  $statusColors = [
                                      'green'  => 'bg-green-100 text-green-700',
                                      'blue'   => 'bg-blue-100 text-blue-700',
                                      'orange' => 'bg-orange-100 text-orange-700',
                                      'red'    => 'bg-red-100 text-red-700',
                                  ];
                                  $statusCls   = $statusColors[$reg->access_status]  ?? 'bg-gray-100 text-gray-600';
                                  $statusLabel = $statusLabels[$reg->access_status]  ?? $reg->access_status;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        {{ $regName }}
                                        @if ($reg->birth_date)
                                            <div class="text-xs text-gray-400">{{ $reg->birth_date->format('d.m.Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $reg->member_type === 'member' ? 'Mitglied' : 'Gast' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $reg->member_number ?? '–' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusCls }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($reg->qrtoken)
                                            <a href="{{ route('verify.checkin', $reg->qrtoken) }}"
                                               target="_blank"
                                               class="font-mono text-xs text-indigo-600 hover:text-indigo-800 hover:underline break-all"
                                               title="{{ route('verify.checkin', $reg->qrtoken) }}">
                                                {{ $reg->qrtoken }}
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs">–</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 tabular-nums">
                                        {{ $reg->checkins_count ?? $reg->checkins->count() }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 text-xs">
                                        {{ $reg->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('admin.registrations.destroy', $reg) }}"
                                              method="POST"
                                              data-confirm="{{ $deleteMsg }}"
                                              onsubmit="handleConfirmForm(event, this)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 hover:underline
                                                       transition touch-manipulation min-h-[44px] px-1">
                                                Löschen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                        Noch keine Registrierungen vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse ($registrations as $reg)
                        @php
                            $deleteMsgMobile = 'Registrierung von ' . $reg->first_name . ' ' . $reg->last_name . ' wirklich löschen?';

                            $mobileStatusColors = [
                                'green'  => 'text-green-600',
                                'blue'   => 'text-blue-500',
                                'orange' => 'text-orange-500',
                                'red'    => 'text-red-500',
                            ];
                            $mobileStatusCls   = $mobileStatusColors[$reg->access_status] ?? 'text-gray-500';
                            $mobileStatusLabel = $statusLabels[$reg->access_status]       ?? $reg->access_status;
                            $deleteMsgMobile = 'Registrierung von ' . $reg->firstname . ' ' . $reg->lastname . ' wirklich löschen? Alle Check-ins werden mitgelöscht.';
                        @endphp
                        <div class="px-4 py-4 flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 truncate">
                                    {{ $reg->first_name }} {{ $reg->last_name }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $reg->member_type === 'member' ? 'Mitglied' : 'Gast' }}
                                    @if ($reg->member_number) · {{ $reg->member_number }} @endif
                                    · {{ $reg->checkins_count ?? $reg->checkins->count() }} Check-ins
                                </div>
                                <div class="text-xs font-medium mt-1 {{ $mobileStatusCls }}">{{ $mobileStatusLabel
                                }}</div>
                            </div>
                            <form action="{{ route('admin.registrations.destroy', $reg) }}"
                                  method="POST"
                                  data-confirm="{{ $deleteMsgMobile }}"
                                  onsubmit="handleConfirmForm(event, this)">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-400 hover:text-red-600 shrink-0
                                           min-h-[44px] min-w-[44px] flex items-center justify-center
                                           touch-manipulation">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center text-gray-400">Keine Registrierungen.</div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($registrations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>

        </div>
    </main>

    {{-- ── Bestätigungs-Modal ───────────────────────────────────── --}}
    <div id="confirmModal"
         class="fixed inset-0 z-[100] hidden"
         aria-hidden="true">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/50" onclick="closeConfirm()"></div>

        {{-- Dialog --}}
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Bitte bestätigen</h3>
                </div>
                <div class="px-5 py-4">
                    <p id="confirmMessage"
                       class="text-sm text-gray-600 leading-relaxed"></p>
                </div>
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100
                            flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
                    <button type="button"
                            onclick="closeConfirm()"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300
                                   bg-white px-4 py-2 text-sm font-semibold text-gray-700
                                   hover:bg-gray-50 transition min-h-[44px] touch-manipulation">
                        Abbrechen
                    </button>
                    <button type="button"
                            id="confirmOkBtn"
                            class="inline-flex items-center justify-center rounded-lg border border-transparent
                                   bg-red-600 px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-red-700 transition min-h-[44px] touch-manipulation">
                        Ja, löschen
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    // ── Bestätigungs-Modal ────────────────────────────────────────────────
    let _pendingForm = null;

    /**
     * Wird vom onsubmit-Handler jedes Formulars aufgerufen.
     * Liest den Bestätigungstext aus data-confirm des Formulars.
     */
    function handleConfirmForm(event, formEl) {
        event.preventDefault();
        const msg = formEl.dataset.confirm || 'Wirklich fortfahren?';
        _pendingForm = formEl;
        document.getElementById('confirmMessage').textContent = msg;
        const okBtn  = document.getElementById('confirmOkBtn');
        okBtn.disabled    = false;
        okBtn.textContent = 'Ja, löschen';
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function closeConfirm() {
        _pendingForm = null;
        const modal = document.getElementById('confirmModal');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('confirmOkBtn').addEventListener('click', function () {
        if (!_pendingForm) return;
        this.disabled    = true;
        this.textContent = '…';
        _pendingForm.submit();
        closeConfirm();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeConfirm();
    });

    // ── CSV Import: Doppelklick-Schutz ────────────────────────────────────
    document.getElementById('importForm').addEventListener('submit', function () {
        const btn     = document.getElementById('importBtn');
        const text    = document.getElementById('importBtnText');
        const spinner = document.getElementById('importSpinner');
        btn.disabled     = true;
        text.textContent = 'Wird importiert…';
        spinner.classList.remove('hidden');
    });

    // ── Auslastungs-Chart ─────────────────────────────────────────────────
    const labels = @json($labels);
    const values = @json($values);

    new Chart(document.getElementById('auslastungChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Check-ins',
                data: values,
                backgroundColor: 'rgba(13, 148, 136, 0.7)',
                borderColor:     'rgba(13, 148, 136, 1)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} Check-in${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11 },
                        maxRotation: 45,
                        callback: function(val, index) {
                            return index % 3 === 0 ? this.getLabelForValue(val) : '';
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });
    </script>
</body>
</html>
