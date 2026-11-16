<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verification_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credential_request_id')->constrained()->cascadeOnDelete();
            $table->string('outcome');
            $table->json('claims')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_results');
    }
};
