<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * کاربر واردشده به‌جای صفحات ورود/ثبت‌نام، به پنل شخصی هدایت می‌شود.
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(route('dashboard.placeholder'));
            }
        }

        return $next($request);
    }
}
