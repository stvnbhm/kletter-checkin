<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Startseite Kletterdom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 flex flex-col min-h-screen">
    
    <main class="flex-grow flex items-center justify-center p-6">
        <!-- max-w-xl statt max-w-md für angenehme Desktop-Breite -->
        <div class="w-full max-w-xl rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            
            <!-- Icon/Logo -->
            <div class="mx-auto mb-8 flex justify-center">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="ÖTK Langenlois Alpinsport Logo" 
                    class="h-24 w-auto object-contain"
                />
            </div>

            <h1 class="mb-2 text-2xl font-bold text-gray-900">Willkommen!</h1>
            <p class="mb-8 text-sm leading-relaxed text-gray-600">
                Bitte registriere dich für den Zutritt zur Kletterhalle. Wenn du noch kein Vereinsmitglied bist, kannst du hier direkt dem ÖTK beitreten.
            </p>

            <div class="space-y-4">
                <a
                    href="{{ url('halle-register') }}"
                    class="flex w-full items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-6 py-3.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                >
                    Registrierung Kletterdom
                </a>

                <a
                    href="https://beitritt.oetk.at"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3.5 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    ÖTK Mitglied werden
                    <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </main>

    <footer class="py-6 text-center">
        <a
            href="{{ route('login') }}"
            class="text-xs font-medium text-gray-400 transition hover:text-gray-600"
        >
            Hallendienst Login
        </a>
    </footer>

</body>
</html>