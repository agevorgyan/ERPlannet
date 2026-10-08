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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('slug', 100)->unique();
            $table->string('subdomain', 100)->unique();
            $table->string('custom_domain', 255)->nullable()->unique();
            $table->string('status', 50)->default('trialing'); // trialing, active, past_due, suspended, canceled
            $table->string('country', 2)->default('AM');
            $table->string('currency', 3)->default('AMD');
            $table->string('timezone', 100)->default('Asia/Yerevan');
            $table->string('default_locale', 10)->default('hy');
            $table->jsonb('settings')->default('{}');
            $table->timestampTz('trial_ends_at')->nullable();
            $table->timestampTz('suspended_at')->nullable();
            $table->text('suspended_reason')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
