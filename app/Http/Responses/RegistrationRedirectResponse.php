<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class RegistrationRedirectResponse implements RegisterResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('profile.edit');
    }
}
