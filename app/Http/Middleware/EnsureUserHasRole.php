<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  list<string>  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user || empty($roles) || ! in_array($user->role?->value ?? $user->role, $roles, true)) {
            abort(403, 'Acesso restrito.');
        }

        return $next($request);
    }
}
