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
        Schema::create('reports', function (Blueprint $table) {

            $table->id();

            $table->char('uuid', 36)->nullable();

            $table->string('incident')->nullable();

            $table->string('requestor');

            $table->string('requestor_email');

            $table->date('request_date');

            $table->time('report_time');

            $table->dateTime('response_time')->nullable();

            $table->timestamp('resolved_time')->nullable();

            $table->integer('restored_time')->nullable();

            $table->timestamp('servicerestored_time')->nullable();

            $table->integer('total_internal_duration')->nullable();

            $table->json('restoration_evidence')->nullable();

            $table->string('apps');

            $table->text('description');

            $table->string('severity');

            $table->string('assigned_to');

            $table->string('scope');

            $table->text('resolution')->nullable();

            $table->text('rca')->nullable();

            $table->string('file_downtime_evidence')->nullable();

            $table->string('type');

            $table->integer('status')->nullable();

            $table->boolean('handled_by')->nullable();

            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};