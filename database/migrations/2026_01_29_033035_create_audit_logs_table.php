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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Siapa buat
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Model apa terlibat
            $table->string('auditable_type'); // contoh: App\Models\Post
            $table->unsignedBigInteger('auditable_id'); // contoh: post_id

            // Apa tindakan
            $table->string('event'); // created, updated, deleted, login, approve

            // Data sebelum & selepas
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
