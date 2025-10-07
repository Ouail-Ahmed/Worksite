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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('task', 200);
            $table->decimal('planned', 12, 2)->default(0);
            $table->string('unit_measure', 50)->nullable();
            $table->decimal('accomplished_quantity', 12, 2)->default(0);
            $table->decimal('total_achieved', 12, 2)->default(0);
            $table->date('finished_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
