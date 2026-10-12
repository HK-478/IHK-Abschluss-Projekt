<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id(); // PK bigint NOT NULL

            // FK user_id (bigint NOT NULL) - Der Ersteller
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // FK watched_user_id (bigint NOT NULL) - Die beobachtete Person
            $table->foreignId('watched_user_id')->constrained('users')->onDelete('cascade');

            $table->boolean('active')->default(true); // boolean NOT NULL

            // nullable(), da diese Timestamps laut ERM erst später gesetzt werden
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->string('recipient_status', 30)->default('pending'); // varchar(30) NOT NULL

            $table->timestamps(); // Übernimmt das created_at aus dem ERM
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
