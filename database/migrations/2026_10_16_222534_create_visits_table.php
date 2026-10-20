<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('applicant_name');
            $table->string('institution');
            $table->string('phone');
            $table->string('email');
            $table->integer('participants_count');
            $table->text('purpose');
            $table->string('facility_target');
            $table->date('visit_date');
            $table->date('scheduled_date')->nullable();
            $table->time('visit_time')->nullable();
            $table->string('status')->default('MENUNGGU'); // MENUNGGU, DISETUJUI, DITOLAK
            $table->string('whatsapp_status')->default('BELUM'); // BELUM, TERKIRIM
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
