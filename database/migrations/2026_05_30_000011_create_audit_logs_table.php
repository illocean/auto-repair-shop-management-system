<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('username', 50)->nullable();
            $table->string('action', 50);
            $table->string('entity_type', 50);
            $table->string('entity_id', 50)->nullable();
            $table->string('summary', 500)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id'], 'idx_audit_entity');
            $table->index('user_id', 'idx_audit_user');
            $table->index('created_at', 'idx_audit_created');
            $table->index('action', 'idx_audit_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
