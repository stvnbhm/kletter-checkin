<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrierung | Kletterhalle</title>

    <!-- Direkte Vite-Einbindung für sauberes Tailwind und JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Verhindert den Standard-Pfeil im Safari/Chrome bei details/summary */
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <div class="min-h-screen py-12 flex flex-col justify-center sm:px-6 lg:px-8">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-3xl text-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Registrierung Kletterhalle
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Bitte fülle das Formular aus, um dich für den Hallenbesuch zu registrieren.
            </p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-3xl">
            <div class="bg-white py-8 px-4 shadow-xl sm:rounded-xl sm:px-10 border border-gray-100">
                
                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/halle-register') }}" class="space-y-6">
                    @csrf
                    
                    @php
                        $hpTimestamp = now()->timestamp;
                    @endphp
                    
                    <input type="hidden" name="hp_time" value="{{ $hpTimestamp }}">
                    
                    <div class="sr-only" aria-hidden="true">
                        <label for="website">Website</label>
                        <input
                            type="text"
                            id="website"
                            name="website"
                            value="{{ old('website') }}"
                            tabindex="-1"
                            autocomplete="off"
                        >
                    
                        <label for="fax_number">Faxnummer</label>
                        <input
                            type="text"
                            id="fax_number"
                            name="fax_number"
                            value="{{ old('fax_number') }}"
                            tabindex="-1"
                            autocomplete="off"
                        >
                    </div>

                    <!-- Persönliche Daten -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">Vorname</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Nachname</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700">Geburtsdatum</label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" min="1900-01-01" max="{{ date('Y-m-d') }}" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('birth_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                            <!-- Hinweis 14-17 -->
                            <div id="minor-note" class="hidden mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                <p class="font-semibold mb-1">Hinweis für Minderjährige</p>
                                <p id="minor-note-text">Für Kinder von 14 bis 18 Jahren muss beim Hallenbesuch ohne Aufsichtsperson eine unterschriebene Einverständniserklärung der Eltern mitgebracht werden.</p>
                                <a id="minor-pdf-link" href="https://www.oetk-langenlois.at/fileadmin/Einverstaendniserklaerung-14-18.pdf" target="_blank" rel="noopener noreferrer" class="hidden mt-2 inline-flex font-medium text-amber-900 underline hover:text-amber-700">Einverständniserklärung herunterladen</a>
                            </div>

                            <!-- Checkbox nur unter 14 -->
                            <div id="supervision-wrapper" class="hidden mt-3">
                                <label class="inline-flex items-start bg-amber-50 border border-amber-200 rounded-lg p-3 cursor-pointer">
                                    <input type="checkbox" id="supervision_confirmed" name="supervision_confirmed" value="1" class="mt-0.5 rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" {{ old('supervision_confirmed') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-amber-900 leading-snug">Ich bestätige, dass Kinder unter 14 Jahren nur unter Aufsicht klettern dürfen und beim Besuch eine erziehungsberechtigte oder aufsichtsführende Person dabei ist.</span>
                                </label>
                                @error('supervision_confirmed') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">E-Mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700 mb-2">Typ</legend>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="member_type" value="member" class="text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('member_type', 'member') === 'member' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 text-sm">Mitglied</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="member_type" value="guest" class="text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('member_type') === 'guest' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 text-sm">Gast / Schnuppern</span>
                            </label>
                        </div>
                        @error('member_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div id="member-number-wrapper">
                        <label for="member_number" class="block text-sm font-medium text-gray-700">Mitgliedsnummer</label>
                        <input type="text" id="member_number" name="member_number" value="{{ old('member_number') }}" placeholder="z. B. 23-10992" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">Bitte die Vereins-Mitgliedsnummer eingeben.</p>
                        @error('member_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- NEU: Accordions für Regeln & Haftungsausschluss -->
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Wichtige Informationen</h3>
                        
                        <!-- Accordion 1: Haftungsausschluss -->
                        <details class="group border border-gray-200 rounded-lg bg-gray-50 mb-3 overflow-hidden">
                            <summary class="flex justify-between items-center font-semibold cursor-pointer p-4 text-gray-800 hover:bg-gray-100 transition-colors">
                                <span class="flex items-center gap-2">🛡️ Haftungsausschluss & Sicherheit</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="20" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20"><path d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </summary>
                            <div class="p-4 border-t border-gray-200 text-sm text-gray-700 bg-white space-y-2">
                                <p>Klettern und Bouldern sind mit Sturz- und Verletzungsrisiken verbunden. Ich nutze die Anlage eigenverantwortlich.</p>
                                <ul class="list-disc pl-5 space-y-1 mt-2">
                                    <li><strong>Bouldern:</strong> Ich bouldere nur über Matten, halte Sturzräume frei und übersteige keine Maximalhöhen.</li>
                                    <li><strong>Toprope:</strong> Nur wenn ich Gurt, Anseilen und Sicherungsgerät sicher beherrsche.</li>
                                    <li><strong>Vorstieg:</strong> Nur wenn ich Gurt, Einbinden, Clippen und das Halten von Stürzen sicher beherrsche.</li>
                                </ul>
                                <p class="text-amber-700 font-medium mt-2">Falls ich eine dieser Voraussetzungen nicht erfülle, klettere ich nur unter Aufsicht einer geschulten Person.</p>
                            </div>
                        </details>

                        <!-- Accordion 2: Hallenordnung -->
                        <details class="group border border-gray-200 rounded-lg bg-gray-50 mb-6 overflow-hidden">
                            <summary class="flex justify-between items-center font-semibold cursor-pointer p-4 text-gray-800 hover:bg-gray-100 transition-colors">
                                <span class="flex items-center gap-2">📋 Hallenordnung ÖTK-Langenlois</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="20" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20"><path d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </summary>
                            <div class="p-4 border-t border-gray-200 text-sm text-gray-700 bg-white grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">🧹 Verhalten & Sauberkeit</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Dom sauber hinterlassen (Gurte & Schuhe aufräumen)</li>
                                        <li>Kein Essen im Dom; Getränke nur verschlossen</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">🧗 Kletterregeln</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Bouldern: Rote Linie beachten, nicht übereinander</li>
                                        <li>Chalk-Balls erst ab VI+; nur mit Kletterschuhen</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">🪝 Material</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Leih-Gurte & Karabiner an vorgesehene Haken</li>
                                        <li>Bälle/Stäbe/Tiere nur für Kursbetrieb</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">📅 Termine & Beiträge</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Klettertreff & Kurse nur für ÖTK-Mitglieder</li>
                                        <li>Benützungsbeitrag: <strong>4 €</strong> Erwachsene · <strong>2 €</strong> unter 18 J. · <strong>10 €</strong> Familie</li>
                                    </ul>
                                </div>
                            </div>
                        </details>

                        <!-- Die Zustimmungs-Checkboxen -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="rules_accepted" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('rules_accepted') ? 'checked' : '' }} required>
                                <span class="ml-3 text-sm text-gray-800">
                                    Ich habe die <strong>Hallenordnung</strong> gelesen und verpflichte mich, diese sowie die Anweisungen des Personals einzuhalten.
                                </span>
                            </label>

                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="waiver_accepted" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('waiver_accepted') ? 'checked' : '' }} required>
                                <span class="ml-3 text-sm text-gray-800">
                                    Ich bestätige, dass ich die <strong>Risiken des Kletterns</strong> kenne und nur jene Bereiche selbständig nutze, für die ich ausreichend geschult bin (andernfalls nur unter Aufsicht).
                                </span>
                            </label>
                        </div>
                        
                        @error('rules_accepted') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('waiver_accepted') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Datenschutzhinweis --}}
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
                        🔒 <strong>Datenschutz:</strong> Wir speichern deine Daten gemäß DSGVO. Sie werden ausschließlich für den Hallenbetrieb verwendet, nicht an Dritte weitergegeben und auf Wunsch sofort gelöscht.
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Registrierung absenden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Radio Buttons für Mitglied/Gast
            const memberRadios = document.querySelectorAll('input[name="member_type"]');
            const memberNumberWrapper = document.getElementById('member-number-wrapper');
            const memberNumberInput = document.getElementById('member_number');
    
            function updateMemberNumberField() {
                const selected = document.querySelector('input[name="member_type"]:checked')?.value;
    
                if (selected === 'guest') {
                    memberNumberWrapper.classList.add('hidden');
                    memberNumberInput.required = false;
                    memberNumberInput.value = '';
                } else {
                    memberNumberWrapper.classList.remove('hidden');
                    memberNumberInput.required = true;
                }
            }
    
            memberRadios.forEach(function (radio) {
                radio.addEventListener('change', updateMemberNumberField);
            });
    
            updateMemberNumberField();
    
            // Alterslogik für Aufsicht und PDF
            const birthDateInput = document.getElementById('birth_date');
            const minorNote = document.getElementById('minor-note');
            const pdfLink = document.getElementById('minor-pdf-link');
            const supervisionWrapper = document.getElementById('supervision-wrapper');
            const supervisionCheckbox = document.getElementById('supervision_confirmed');
    
            function updateAgeRules() {
                if (!birthDateInput || !birthDateInput.value) {
                    minorNote.classList.add('hidden');
                    pdfLink.classList.add('hidden');
                    supervisionWrapper.classList.add('hidden');
                    supervisionCheckbox.required = false;
                    supervisionCheckbox.checked = false;
                    return;
                }
    
                const birthDate = new Date(birthDateInput.value);
                const today = new Date();
    
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
    
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
    
                if (age < 14) {
                    minorNote.classList.add('hidden');
                    pdfLink.classList.add('hidden');
                    supervisionWrapper.classList.remove('hidden');
                    supervisionCheckbox.required = true;
                } else if (age < 18) {
                    minorNote.classList.remove('hidden');
                    pdfLink.classList.remove('hidden');
                    supervisionWrapper.classList.add('hidden');
                    supervisionCheckbox.required = false;
                    supervisionCheckbox.checked = false;
                } else {
                    minorNote.classList.add('hidden');
                    pdfLink.classList.add('hidden');
                    supervisionWrapper.classList.add('hidden');
                    supervisionCheckbox.required = false;
                    supervisionCheckbox.checked = false;
                }
            }
    
            birthDateInput?.addEventListener('change', updateAgeRules);
            updateAgeRules();
    
            // Fix: pageshow feuert nach bfcache-Wiederherstellung, wenn der Browser
            // den Formularstatus bereits zurückgesetzt hat → UI-Zustand neu anwenden
            window.addEventListener('pageshow', function () { // ← NEU
                updateMemberNumberField();                    // ← NEU
                updateAgeRules();                             // ← NEU
            });                                               // ← NEU
        });
    </script>

</body>
</html>