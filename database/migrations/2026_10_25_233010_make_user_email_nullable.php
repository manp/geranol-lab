<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GR-201: ثبت‌نام placeholder فقط با موبایل است؛ ایمیل اختیاری می‌شود
     * تا پس از پاسخ پرسشنامه (بخش ۴) روش نهایی تعیین شود.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->change();
        });
    }
};
