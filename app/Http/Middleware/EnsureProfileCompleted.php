<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->profile_setup_required) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please complete your profile first.'], 403);
            }

            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}
