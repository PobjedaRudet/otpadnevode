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
            $table->id(); // Id
            $table->string('firma'); // Firma (varchar)
            $table->string('mjrni_instrument'); // MjerniInstrument (varchar) - stored as snake-case/shortened
            $table->timestamp('vrijeme'); // Vrijeme (timestamp)
            $table->date('datum'); // Datum (date)
            $table->decimal('vrijednost', 10, 2); // Vrijednost (decimal with two decimals)
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
