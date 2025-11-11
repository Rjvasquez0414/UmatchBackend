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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            // Relación con torneo
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');

            // Información de la ronda
            $table->string('round', 50); // Final, Semifinal, Cuartos, Octavos, etc.
            $table->integer('match_number'); // Número del partido en la ronda
            $table->integer('round_order')->default(0); // Orden de las rondas (0=Final, 1=Semi, 2=Cuartos, etc.)

            // Participantes del partido
            $table->foreignId('player1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('set null');

            // Resultado
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('player1_score')->nullable();
            $table->integer('player2_score')->nullable();

            // Programación y ubicación
            $table->dateTime('scheduled_at')->nullable();
            $table->foreignId('court_id')->nullable()->constrained()->onDelete('set null');

            // Información adicional
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'bye'])->default('pending');

            // Para doble eliminación
            $table->enum('bracket_type', ['winners', 'losers', 'grand_final'])->default('winners');

            // Estructura de árbol (para navegar entre partidos)
            $table->foreignId('next_match_id')->nullable()->constrained('matches')->onDelete('set null');
            $table->boolean('feeds_winner_to_next')->default(true); // El ganador va al siguiente partido

            // Para Round Robin
            $table->integer('group_number')->nullable(); // Si hay grupos

            $table->timestamps();

            // Índices para mejorar performance
            $table->index(['tournament_id', 'status']);
            $table->index(['tournament_id', 'round_order']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
