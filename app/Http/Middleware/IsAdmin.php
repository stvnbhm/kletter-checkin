<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Prüfen, ob der Nutzer eingeloggt und ein Admin ist
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // Wenn kein Admin, leite zurück zum Staff-Bereich mit einer Fehlermeldung
        return redirect()->route('staff')->with('error', 'Du hast keine Berechtigung für den Admin-Bereich.');
    }
}