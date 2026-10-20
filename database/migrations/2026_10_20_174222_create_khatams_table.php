<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('khatams', function (Blueprint $table) {
            $table->id();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('khatam_ayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khatam_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_number');
            $table->unsignedTinyInteger('juz');
            $table->timestamps();

            $table->unique(['khatam_id', 'ayah_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khatam_ayahs');
        Schema::dropIfExists('khatams');
    }
};
