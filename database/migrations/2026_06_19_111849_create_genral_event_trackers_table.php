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
        Schema::create('genral_event_trackers', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->nullable();
            $table->string('action')->nullable();
            $table->enum('module', ['user_management', 'department_management', 'permission_management'])->nullable();
            $table->integer('user_id')->nullable();
            // $table->string('device_ip')->nullable();
            // $table->enum('severity', ['low', 'medium', 'high'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genral_event_trackers');
    }
};
