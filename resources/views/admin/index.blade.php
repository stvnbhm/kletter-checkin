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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🛠️ Admin-Bereich
                </h2>
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
                    <div class="text-3xl font-bold text-blue-500">{{ $stats['guests_today'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Davon Gäste heute</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['total_registrations'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Registrierungen gesamt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-purple-500">{{ $stats['members'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Mitglieder (CSV)</div>
                </div>
            </div>

            {{-- ── Hallenauslastung Chart ───────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">📊 Hallenauslastung – letzte 30 Tage</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="auslastungChart"></canvas>
                </div>
            </div>

            {{-- ── Zwei-Spalten-Grid: Import + Export ──────────────── --}}
            <div class="grid md:grid-cols-2 gap-6">

                {{-- Mitglieder CSV-Import --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">📥 Mitglieder importieren</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        CSV-Datei mit Spalten:
                        <code class="font-mono">Mitgliedsnummer; Nachname; Vorname; Email; Status; Betrag offen; Geburtsdatum</code>
                    </p>
                    <form action="{{ route('admin.importMembers') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">CSV-Datei wählen</label>
                            <input type="file" name="members_csv" accept=".csv,.txt"
                                class="block w-full text-sm text-gray-500
                                       file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                       file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700
                                       hover:file:bg-teal-100 border border-gray-200 rounded-lg p-1">
                            @error('members_csv')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="w-full bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                            Importieren
                        </button>
                    </form>
                </div>

                {{-- Check-ins CSV-Export --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">📤 Check-ins exportieren</h3>
                    <p class="text-sm text-gray-400 mb-4">
                        Exportiert alle Check-ins im gewählten Zeitraum als CSV (Excel-kompatibel, UTF-8 BOM, Semikolon-getrennt).
                    </p>
                    <form action="{{ route('admin.exportCheckins') }}" method="GET" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Von</label>
                                <input type="date" name="from" value="{{ now()->subDays(30)->toDateString() }}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Bis</label>
                                <input type="date" name="to" value="{{ now()->toDateString() }}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                            CSV herunterladen
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Registrierungen Tabelle ─────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-700">👥 Alle Registrierungen</h3>
                    <span class="text-sm text-gray-400">{{ $registrations->total() }} gesamt</span>
                </div>

                {{-- Desktop-Tabelle --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Typ</th>
                                <th class="px-4 py-3 text-left">Mitgliedsnr.</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Check-ins</th>
                                <th class="px-4 py-3 text-left">Registriert am</th>
                                <th class="px-4 py-3 text-left">Aktion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($registrations as $reg)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        {{ $reg->first_name }} {{ $reg->last_name }}
                                        @if ($reg->birth_date)
                                            <div class="text-xs text-gray-400">{{ $reg->birth_date?->format('d.m.Y') ?? '—' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($reg->member_type === 'member')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Mitglied</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Gast</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $reg->member_number ?? '–' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $colors = [
                                                'green'  => 'bg-green-100 text-green-700',
                                                'blue'   => 'bg-blue-100 text-blue-700',
                                                'orange' => 'bg-orange-100 text-orange-700',
                                                'red'    => 'bg-red-100 text-red-700',
                                            ];
                                            $cls = $colors[$reg->access_status] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">
                                            {{ $reg->access_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 tabular-nums">{{ $reg->checkins_count ?? $reg->checkins->count() }}</td>
                                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $reg->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('admin.registrations.destroy', $reg) }}" method="POST"
                                              onsubmit="return confirm('Registrierung von {{ addslashes($reg->first_name . ' ' . $reg->last_name) }} wirklich löschen? Alle Check-ins werden mitgelöscht.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 hover:underline transition">
                                                Löschen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
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
                        <div class="px-4 py-4 flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 truncate">{{ $reg->first_name }} {{ $reg->last_name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $reg->member_type === 'member' ? 'Mitglied' : 'Gast' }}
                                    @if ($reg->member_number) · {{ $reg->member_number }} @endif
                                    · {{ $reg->checkins->count() }} Check-ins
                                </div>
                                @php $colors = ['green'=>'text-green-600','blue'=>'text-blue-500','orange'=>'text-orange-500','red'=>'text-red-500']; @endphp
                                <div class="text-xs font-medium mt-1 {{ $colors[$reg->access_status] ?? 'text-gray-500' }}">
                                    ● {{ $reg->access_status }}
                                </div>
                            </div>
                            <form action="{{ route('admin.registrations.destroy', $reg) }}" method="POST"
                                  onsubmit="return confirm('Wirklich löschen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 shrink-0">🗑️</button>
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

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const labels = @json($labels);
        const values = @json($values);

        new Chart(document.getElementById('auslastungChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Check-ins',
                    data: values,
                    backgroundColor: 'rgba(13, 148, 136, 0.7)',   // teal-600
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