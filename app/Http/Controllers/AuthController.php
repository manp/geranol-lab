<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'regex:'.config('auth-identity.mobile_regex'), 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'نام الزامی است.',
            'name.max' => 'نام نباید بیش از ۱۰۰ کاراکتر باشد.',
            'mobile.required' => 'شمارهٔ موبایل الزامی است.',
            'mobile.regex' => 'شمارهٔ موبایل باید به شکل ۰۹۱۲۳۴۵۶۷۸۹ باشد.',
            'mobile.unique' => 'این شمارهٔ موبایل قبلاً ثبت شده است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard.placeholder')
            ->with('success', 'ثبت‌نام انجام شد؛ به پنل شخصی خوش آمدید.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mobile' => ['required', 'string', 'regex:'.config('auth-identity.mobile_regex')],
            'password' => ['required', 'string'],
        ], [
            'mobile.required' => 'شمارهٔ موبایل الزامی است.',
            'mobile.regex' => 'شمارهٔ موبایل باید به شکل ۰۹۱۲۳۴۵۶۷۸۹ باشد.',
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $user = User::where('mobile', $credentials['mobile'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['mobile' => 'شمارهٔ موبایل یا رمز عبور درست نیست.'])
                ->onlyInput('mobile');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard.placeholder'))
            ->with('success', 'خوش آمدید.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'از حساب خود خارج شدید.');
    }

    public function dashboardPlaceholder(Request $request)
    {
        return view('auth.dashboard-placeholder');
    }
}
