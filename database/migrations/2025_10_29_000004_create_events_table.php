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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->onDelete('cascade');
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('court_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name', 300);
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('time');
            $table->decimal('duration', 3, 1);
            $table->integer('max_players');
            $table->enum('level', ['principiante', 'intermedio', 'avanzado', 'todos'])->default('todos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
