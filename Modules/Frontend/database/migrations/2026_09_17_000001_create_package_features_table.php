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
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')
                ->constrained('packages')
                ->cascadeOnDelete();

            $table->string('icon')->nullable()->comment('Font Awesome class, e.g. fa-solid fa-hotel');
            $table->string('title');

            $table->unsignedInteger('sort_order')->default(0);

            // User tracking
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // created_at & updated_at
            $table->timestamps();

            // deleted_at
            $table->softDeletes();

            // Ordered fetch: WHERE package_id = ? AND deleted_at IS NULL ORDER BY sort_order
            $table->index(['package_id', 'sort_order'], 'package_features_package_sort_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_features');
    }
};