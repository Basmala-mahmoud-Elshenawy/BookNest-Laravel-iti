<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend gate for every admin-only route AND the admin branch of the
 * chatbot endpoint. This is the single choke point that decides whether a
 * request may see admin-only data -- the AI layer never makes this call
 * itself, it only ever receives data that already passed through here (or
 * through the equivalent check inside AIContextBuilder).
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'You are not authorized to access this resource.');
        }

        return $next($request);
    }
}
