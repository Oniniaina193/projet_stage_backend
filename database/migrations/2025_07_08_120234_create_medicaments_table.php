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
        Schema::create('medicaments', function (Blueprint $table) {
            $table->id('id_medicament');
            $table->string('nom', 255);
            $table->decimal('prix', 10, 2);
            $table->integer('stock');
            $table->enum('status', ['avec', 'sans'])->default('sans');
            $table->timestamps();
            
            $table->index('nom');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicaments');
    }
};