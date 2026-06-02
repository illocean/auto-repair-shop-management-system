<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('first_name', 50)->after('id');
            $table->string('last_name', 50)->after('first_name');
            $table->boolean('is_active')->default(true)->after('password');
            $table->dateTime('last_login')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->dropColumn(['first_name', 'last_name', 'is_active', 'last_login']);
        });
    }
};
