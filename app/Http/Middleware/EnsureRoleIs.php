<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleIs
{
    /**
     * GR-202: محدودسازی مسیر به نقش‌های مجاز.
     * استفاده: ->middleware('role:admin,employee')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'دسترسی به این بخش برای نقش شما مجاز نیست.');
        }

        if ($user->is_active === false) {
            abort(403, 'حساب شما غیرفعال شده است؛ با مدیر آزمایشگاه تماس بگیرید.');
        }

        return $next($request);
    }
}
