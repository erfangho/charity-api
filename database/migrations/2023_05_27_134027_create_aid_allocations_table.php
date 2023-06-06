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
        Schema::create('aid_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('status');
            $table->integer('quantity');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->unsignedBigInteger('help_seeker_id');
            $table->foreign('help_seeker_id')->references('id')->on('help_seekers')->onDelete('cascade');
            $table->unsignedBigInteger('people_aid_id');
            $table->foreign('people_aid_id')->references('id')->on('people_aids')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aid_alocations');
    }
};
