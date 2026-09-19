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
        // Skipped automatically when the column already exists —
        // keeps fresh installs and older installs in sync.
        if (Schema::hasColumn('custom_package_requests', 'airline_id')) {
            return;
        }

        Schema::table('custom_package_requests', function (Blueprint $table) {
            $table->foreignId('airline_id')
                ->nullable()
                ->after('id')
                ->constrained('airlines')
                ->nullOnDelete();

            $table->index('airline_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('custom_package_requests', 'airline_id')) {
            return;
        }

        Schema::table('custom_package_requests', function (Blueprint $table) {
            $table->dropForeign(['airline_id']);
            $table->dropIndex(['airline_id']);
            $table->dropColumn('airline_id');
        });
    }
};
