<?php

use App\Models\ClientType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Existing databases may have been seeded before "Self Assessment" was added to ClientTypeSeeder.
     * The client form only lists active types from lkp_client_types — ensure this row exists and is selectable.
     */
    public function up(): void
    {
        ClientType::query()->updateOrCreate(
            ['name' => ClientType::NAME_SELF_ASSESSMENT],
            ['is_active' => true]
        );
    }

    public function down(): void
    {
        // Do not remove the type — it may already be referenced by clients.
    }
};
