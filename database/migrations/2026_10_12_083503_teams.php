<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id(); // PK bigint NOT NULL
            $table->string('name', 150); // varchar(150) NOT NULL
            $table->timestamps(); // Erstellt automatisch created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
