<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

final class SwitchActiveRoleController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $role = UserRole::from($data['role']);
        abort_unless($user->hasRole($role), 403, 'This role is not assigned to your account.');

        $from = $user->activeRole();

        $request->session()->put('access.active_role_user_id', $user->id);
        $request->session()->put('access.active_role', $role->value);
        $request->session()->regenerate();

        Log::info('ICHES active workspace role changed', [
            'actor_id' => $user->id,
            'from' => $from->value,
            'to' => $role->value,
        ]);

        return redirect()->route('dashboard')->with('status', 'Active workspace: '.$role->label().'.');
    }
}
