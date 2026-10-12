<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();

            // Der User, dem der Alarm gehört
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Der User, der "überwacht" wird
            $table->foreignId('watched_user_id')->constrained('users')->onDelete('cascade');

            // Ist der Alarm aktiv? (1 = ja, 0 = nein)
            $table->boolean('active')->default(true);

            // Status (z.B. pending, triggered)
            $table->string('recipient_status')->default('pending');

            // created_at und updated_at (von Laravel benötigt)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alarms');
    }
};
