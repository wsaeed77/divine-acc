<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('client_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->decimal('fee', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'service_id']);
        });

        Schema::create('client_combined_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->boolean('annual_charge_enabled')->default(false);
            $table->decimal('annual_charge', 10, 2)->nullable();
            $table->boolean('monthly_charge_enabled')->default(false);
            $table->decimal('monthly_charge', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_combined_pricing');
        Schema::dropIfExists('client_services');
        Schema::dropIfExists('services');
    }
};
