<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_order_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('book_hours', 6, 2);
            $table->decimal('rate_per_hour', 8, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();

            $table->index('repair_order_id', 'idx_ros_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_order_services');
    }
};
