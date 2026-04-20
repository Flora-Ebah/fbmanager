<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_replies', function (Blueprint $table) {
            $table->id();
            $table->string('id_commentaire');
            $table->string('tone', 50)->default('professional');
            $table->text('reply');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('id_commentaire')->references('id_commentaire')->on('commentaires')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_replies');
    }
};
