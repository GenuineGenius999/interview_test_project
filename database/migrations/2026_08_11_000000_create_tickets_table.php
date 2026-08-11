<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description');
            $table->string('priority');
            $table->string('status');
            $table->timestamps();

            $table->index(['organization_id', 'created_at', 'id']);
            $table->index(['organization_id', 'status', 'created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
