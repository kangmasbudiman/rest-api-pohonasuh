<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Audit log aktivitas user (admin/mitra/user) — dicatat otomatis oleh middleware.
class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 150)->nullable();
            $table->string('role', 20)->default('sistem');
            $table->string('method', 10);
            $table->string('endpoint', 200);
            $table->string('ip', 45)->nullable();
            $table->unsignedSmallInteger('status')->default(200);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index('role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
