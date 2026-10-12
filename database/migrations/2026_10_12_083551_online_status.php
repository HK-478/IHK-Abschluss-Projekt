<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_status', function (Blueprint $table) {
            $table->id(); // PK bigint NOT NULL

            // FK user_id (bigint NOT NULL)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('status', 30); // varchar(30) NOT NULL
            $table->timestamp('last_seen_at')->nullable(); // timestamp

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_status');
    }
};
