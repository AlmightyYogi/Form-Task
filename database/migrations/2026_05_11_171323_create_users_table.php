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
        Schema::create('users', function (Blueprint $table) {

            $table->char('id', 36)->primary();

            $table->unsignedBigInteger('role_id')->nullable();

            $table->string('name');

            $table->string('email')->unique();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->text('two_factor_secret')->nullable();

            $table->text('two_factor_recovery_codes')->nullable();

            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->rememberToken();

            $table->unsignedInteger('access_failed_count')->default(0);

            $table->boolean('lockout_enabled')->default(true);

            $table->timestamp('lockout_end')->nullable();

            $table->timestamps();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};