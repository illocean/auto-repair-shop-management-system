<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('service_advisor_name', 100);
            $table->date('order_date');
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('customer_id', 'idx_ro_customer');
            $table->index('vehicle_id', 'idx_ro_vehicle');
            $table->index('order_date', 'idx_ro_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
