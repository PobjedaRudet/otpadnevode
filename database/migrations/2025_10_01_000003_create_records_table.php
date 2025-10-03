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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('instruments');
            $table->timestamp('vrijeme');
            $table->date('datum');
            $table->decimal('vrijednost', 10, 2);
            // Optional: you can later add created_at for auditing if needed
            // Not adding timestamps to keep original behaviour
            $table->index(['instrument_id','datum']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
