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
        Schema::create('custom_package_requests', function (Blueprint $table) {
            $table->id();

            // Selected package airline
            $table->foreignId('airline_id')
                ->nullable()
                ->constrained('airlines')
                ->nullOnDelete();

            // Customer Information
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();

            // Travel Date
            $table->date('travel_date')->nullable();

            // Preferred Hotels
            $table->string('makkah_hotel')->nullable();
            $table->string('madinah_hotel')->nullable();

            // Transport
            $table->string('preferred_transport')->nullable();

            // Passenger Information
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('male')->default(0);
            $table->unsignedInteger('female')->default(0);

            // Food Preference
            $table->string('food_preference')->nullable();

            // Additional Requirements
            $table->text('additional_note')->nullable();

            // Request Status
            $table->enum('status', [
                'pending',
                'contacted',
                'processing',
                'quoted',
                'confirmed',
                'cancelled',
            ])->default('pending');

            // Admin
            $table->decimal('quoted_price', 12, 2)->nullable();
            $table->text('admin_note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index(['status', 'travel_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_package_requests');
    }
};
