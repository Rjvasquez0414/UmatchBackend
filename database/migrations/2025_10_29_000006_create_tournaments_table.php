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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->onDelete('cascade');
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 300);
            $table->enum('type', ['oficial', 'amistoso']);
            $table->text('description')->nullable();
            $table->text('rules')->nullable();
            $table->string('prize', 300)->nullable();
            $table->enum('format', ['eliminacion_simple', 'doble_eliminacion', 'round_robin']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location', 200);
            $table->decimal('match_duration', 3, 1)->nullable();
            $table->integer('max_participants');
            $table->enum('status', ['abierto', 'en_progreso', 'finalizado'])->default('abierto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
