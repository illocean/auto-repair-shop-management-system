<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('make', 50);
            $table->string('model', 50);
            $table->year('year')->nullable();
            $table->string('license_plate', 20)->nullable();
            $table->string('vin', 17)->nullable();
            $table->timestamps();

            $table->index('customer_id', 'idx_vehicles_customer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
