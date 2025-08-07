<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // Jangan redirect ke route login, langsung return null
        return $request->expectsJson() ? null : null;
    }
}
