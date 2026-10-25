<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^0[0-9]{10}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'نام الزامی است.',
            'name.max' => 'نام نباید بیش از ۱۰۰ کاراکتر باشد.',
            'phone.required' => 'شمارهٔ تماس الزامی است.',
            'phone.regex' => 'شمارهٔ تماس باید به شکل ۰۹۱۲۳۴۵۶۷۸۹ باشد.',
            'email.email' => 'قالب ایمیل درست نیست.',
            'message.required' => 'متن پیام الزامی است.',
            'message.max' => 'متن پیام نباید بیش از ۲۰۰۰ کاراکتر باشد.',
        ]);

        ContactMessage::create($validated);

        return redirect()
            ->route('contact')
            ->with('success', 'پیام شما ثبت شد. در اسرع وقت با شما تماس می‌گیریم.');
    }
}
