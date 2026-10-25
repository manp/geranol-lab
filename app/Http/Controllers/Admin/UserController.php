<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'regex:'.config('auth-identity.mobile_regex'), 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'employee'])],
        ], [
            'name.required' => 'نام الزامی است.',
            'name.max' => 'نام نباید بیش از ۱۰۰ کاراکتر باشد.',
            'mobile.required' => 'شمارهٔ موبایل الزامی است.',
            'mobile.regex' => 'شمارهٔ موبایل باید به شکل ۰۹۱۲۳۴۵۶۷۸۹ باشد.',
            'mobile.unique' => 'این شمارهٔ موبایل قبلاً ثبت شده است.',
            'password.required' => 'رمز اولیه الزامی است.',
            'password.min' => 'رمز اولیه باید حداقل ۸ کاراکتر باشد.',
            'role.required' => 'نقش الزامی است.',
            'role.in' => 'نقش انتخابی معتبر نیست.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'حساب جدید ساخته شد: '.$user->name);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'employee', 'customer'])],
            'is_active' => ['required', 'boolean'],
        ], [
            'role.required' => 'نقش الزامی است.',
            'role.in' => 'نقش انتخابی معتبر نیست.',
            'is_active.required' => 'وضعیت الزامی است.',
        ]);

        if ($request->user()->is($user) && ($validated['role'] !== 'admin' || ! $validated['is_active'])) {
            abort(403, 'تغییر نقش یا غیرفعال‌سازی حساب خودتان مجاز نیست.');
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'اطلاعات کاربر به‌روز شد: '.$user->name);
    }
}
