<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credential_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->string('candidate_email');
            $table->json('scopes');
            $table->string('status')->default('pending')->index();
            $table->string('share_token', 40)->unique();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credential_requests');
    }
};
