<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document');
            $table->string('description')->nullable();
            $table->string('format', 10);
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('status', 20)->default('Processado');
            $table->json('filters')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_histories');
    }
};
