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
        Schema::create('report_external_teams', function (Blueprint $table) {

            $table->char('id', 36)->primary();

            $table->unsignedBigInteger('report_id');

            $table->unsignedBigInteger('external_teams');

            $table->string('pic');

            $table->dateTime('start_time');

            $table->dateTime('end_time');

            $table->json('evidence_file_external')->nullable();

            $table->integer('duration')->nullable();

            $table->integer('total_external_duration')->nullable();

            $table->timestamps();

            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->cascadeOnDelete();

            $table->foreign('external_teams')
                ->references('id')
                ->on('mst_external_teams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_external_teams');
    }
};