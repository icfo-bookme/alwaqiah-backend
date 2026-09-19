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
        Schema::table('flights', function (Blueprint $table) {
            // Move the airline data to the dedicated airlines table.
            $table->foreignId('airline_id')
                ->nullable()
                ->after('id')
                ->constrained('airlines')
                ->nullOnDelete();

            $table->dropColumn(['airline_name', 'airline_logo']);

            $table->index('airline_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropForeign(['airline_id']);
            $table->dropIndex(['airline_id']);
            $table->dropColumn('airline_id');

            $table->string('airline_name')->after('id');
            $table->string('airline_logo')->nullable()->after('airline_name');
        });
    }
};
