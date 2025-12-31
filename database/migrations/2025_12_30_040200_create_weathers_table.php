<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up(): void
    {
        Schema::create('weathers', function (Blueprint $table) {
            $table->id();
            $table->string('location')->default('Tokyo');
            $table->date('date');
            $table->float('temp_max');
            $table->float('temp_min');
            $table->float('precipitation');
            $table->string('advice')->nullable();
            $table->timestamps();

            $table->unique(['location', 'date']); // 同一場所・同一日の重複防止
        });
    }

    // Reverse the migrations
    public function down(): void
    {
        Schema::dropIfExists('weathers');
    }
};
