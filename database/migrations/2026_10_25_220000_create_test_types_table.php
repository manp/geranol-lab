<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description_short');
            $table->text('description_full')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('turnaround_text')->nullable();
            $table->unsignedBigInteger('price')->nullable()->comment('به تومان؛ null یعنی تماس بگیرید');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_types');
    }
};
