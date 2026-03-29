<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lkp_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_marital_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_action_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('category', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('lkp_tax_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_vat_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_paye_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        Schema::create('lkp_flat_rate_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->decimal('rate', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('lkp_vat_member_states', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 5)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lkp_vat_member_states');
        Schema::dropIfExists('lkp_flat_rate_categories');
        Schema::dropIfExists('lkp_paye_frequencies');
        Schema::dropIfExists('lkp_vat_frequencies');
        Schema::dropIfExists('lkp_tax_offices');
        Schema::dropIfExists('lkp_action_statuses');
        Schema::dropIfExists('lkp_languages');
        Schema::dropIfExists('lkp_nationalities');
        Schema::dropIfExists('lkp_marital_statuses');
        Schema::dropIfExists('lkp_titles');
    }
};
