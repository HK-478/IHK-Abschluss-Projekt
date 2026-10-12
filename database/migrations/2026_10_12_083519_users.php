<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // PK bigint NOT NULL

            // FK team_id (bigint NOT NULL)
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');

            $table->string('username', 100)->unique(); // AK username (varchar 100)
            $table->string('email', 255)->unique(); // AK email (varchar 255)

            // Heißt im ERM password_hash, Laravel erwartet standardmäßig "password",
            // wir bleiben aber bei deinem ERM:
            $table->string('password_hash', 255);

            $table->string('role', 50)->nullable(); // role (varchar 50)
            $table->timestamp('joined_at'); // joined_at (timestamp NOT NULL)

            $table->timestamps(); // Dies übernimmt das created_at (timestamp NOT NULL) aus dem ERM
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
